<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Transaction;
use App\TransactionSellLine;
use App\TransactionPayment;
use App\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionApiController extends Controller
{
    /**
     * GET /api/v1/sells
     * List sell transactions
     */
    public function indexSells(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $query = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->with(['contact', 'sell_lines.product', 'payment_lines', 'location']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status); // final, draft, quotation
        }
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status); // paid, due, partial
        }
        if ($request->has('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->has('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->has('invoice_no')) {
            $query->where('invoice_no', 'like', '%' . $request->invoice_no . '%');
        }

        $query->orderBy('transaction_date', 'desc');
        $perPage = min($request->get('per_page', 25), 100);
        $transactions = $query->paginate($perPage);

        $transactions->getCollection()->transform(function ($t) {
            return $this->transformTransaction($t);
        });

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/sells/{id}
     */
    public function showSell(Request $request, $id)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $transaction = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->with(['contact', 'sell_lines.product', 'sell_lines.variations', 'payment_lines', 'location'])
            ->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'error' => 'Venta no encontrada.',
                'code' => 'NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformTransaction($transaction, true),
        ]);
    }

    /**
     * GET /api/v1/purchases
     * List purchase transactions
     */
    public function indexPurchases(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $query = Transaction::where('business_id', $businessId)
            ->where('type', 'purchase')
            ->with(['contact', 'purchase_lines.product', 'payment_lines', 'location']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }
        if ($request->has('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to . ' 23:59:59');
        }

        $query->orderBy('transaction_date', 'desc');
        $perPage = min($request->get('per_page', 25), 100);
        $transactions = $query->paginate($perPage);

        $transactions->getCollection()->transform(function ($t) {
            return $this->transformTransaction($t, false, 'purchase');
        });

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/transactions/summary
     * Get sales/purchases summary for a date range
     */
    public function summary(Request $request)
    {
        $apiKey = $request->attributes->get('api_key');
        $businessId = $apiKey->business_id;

        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        // Sales summary
        $sells = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(final_total) as total_amount'),
                DB::raw('SUM(total_before_tax) as subtotal_amount')
            )
            ->first();

        // Purchases summary
        $purchases = Transaction::where('business_id', $businessId)
            ->where('type', 'purchase')
            ->where('status', 'received')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(final_total) as total_amount')
            )
            ->first();

        // Expenses summary
        $expenses = Transaction::where('business_id', $businessId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(final_total) as total_amount')
            )
            ->first();

        // Payments received
        $paymentsReceived = TransactionPayment::join('transactions', 'transactions.id', '=', 'transaction_payments.transaction_id')
            ->where('transactions.business_id', $businessId)
            ->where('transactions.type', 'sell')
            ->whereBetween('transaction_payments.paid_on', [$dateFrom, $dateTo . ' 23:59:59'])
            ->sum('transaction_payments.amount');

        return response()->json([
            'success' => true,
            'data' => [
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
                'sells' => [
                    'count' => (int)($sells->total_count ?? 0),
                    'total' => (float)($sells->total_amount ?? 0),
                    'subtotal' => (float)($sells->subtotal_amount ?? 0),
                ],
                'purchases' => [
                    'count' => (int)($purchases->total_count ?? 0),
                    'total' => (float)($purchases->total_amount ?? 0),
                ],
                'expenses' => [
                    'count' => (int)($expenses->total_count ?? 0),
                    'total' => (float)($expenses->total_amount ?? 0),
                ],
                'payments_received' => (float)$paymentsReceived,
                'profit_estimate' => (float)(($sells->total_amount ?? 0) - ($purchases->total_amount ?? 0) - ($expenses->total_amount ?? 0)),
            ],
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────

    private function transformTransaction($transaction, $detailed = false, $type = 'sell')
    {
        $data = [
            'id' => $transaction->id,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'sub_status' => $transaction->sub_status,
            'payment_status' => $transaction->payment_status,
            'invoice_no' => $transaction->invoice_no,
            'ref_no' => $transaction->ref_no,
            'transaction_date' => $transaction->transaction_date,
            'contact' => $transaction->contact ? [
                'id' => $transaction->contact->id,
                'name' => $transaction->contact->name,
                'tax_number' => $transaction->contact->tax_number,
            ] : null,
            'location' => $transaction->location ? [
                'id' => $transaction->location->id,
                'name' => $transaction->location->name,
            ] : null,
            'subtotal' => (float)($transaction->total_before_tax ?? 0),
            'tax' => (float)($transaction->tax_amount ?? 0),
            'discount' => (float)($transaction->discount_amount ?? 0),
            'discount_type' => $transaction->discount_type,
            'total' => (float)($transaction->final_total ?? 0),
            'paid' => (float)TransactionPayment::where('transaction_id', $transaction->id)->sum('amount'),
            'shipping_charges' => (float)($transaction->shipping_charges ?? 0),
            'additional_notes' => $transaction->additional_notes,
            'staff_note' => $transaction->staff_note,
            'created_at' => $transaction->created_at ? $transaction->created_at->toIso8601String() : null,
        ];

        if ($detailed) {
            // Line items
            if ($type === 'sell') {
                $data['items'] = $transaction->sell_lines->map(function ($line) {
                    return [
                        'id' => $line->id,
                        'product_id' => $line->product_id,
                        'product_name' => $line->product ? $line->product->name : null,
                        'variation_id' => $line->variation_id,
                        'quantity' => (float)$line->quantity,
                        'unit_price' => (float)$line->unit_price,
                        'unit_price_inc_tax' => (float)$line->unit_price_inc_tax,
                        'discount' => (float)($line->line_discount_amount ?? 0),
                        'discount_type' => $line->line_discount_type,
                        'total' => (float)($line->quantity * $line->unit_price_inc_tax),
                    ];
                });
            } else {
                $data['items'] = $transaction->purchase_lines->map(function ($line) {
                    return [
                        'id' => $line->id,
                        'product_id' => $line->product_id,
                        'product_name' => $line->product ? $line->product->name : null,
                        'variation_id' => $line->variation_id,
                        'quantity' => (float)$line->quantity,
                        'purchase_price' => (float)$line->purchase_price,
                        'purchase_price_inc_tax' => (float)$line->purchase_price_inc_tax,
                    ];
                });
            }

            // Payments
            $data['payments'] = $transaction->payment_lines->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float)$payment->amount,
                    'method' => $payment->method,
                    'paid_on' => $payment->paid_on,
                    'note' => $payment->note,
                ];
            });
        }

        return $data;
    }
}
