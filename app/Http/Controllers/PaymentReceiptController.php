<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\PaymentReceipt;
use App\Utils\NumberToWords;
use App\Utils\Util;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PaymentReceiptController extends Controller
{
    /**
     * @var \App\Utils\Util
     */
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! auth()->user()->can('sell.view') && ! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $paymentTypes = $this->commonUtil->payment_types(null, false, $business_id);

        if (request()->ajax()) {
            $receipts = PaymentReceipt::with(['contact', 'location'])
                ->where('business_id', $business_id)
                ->select([
                    'id',
                    'business_id',
                    'location_id',
                    'contact_id',
                    'receipt_no',
                    'amount',
                    'currency_code',
                    'payment_method',
                    'bank_name',
                    'bank_reference',
                    'payment_date',
                    'reference',
                    'concept',
                    'created_at',
                ]);

            return DataTables::of($receipts)
                ->editColumn('payment_date', function ($row) {
                    return $this->commonUtil->format_date($row->payment_date);
                })
                ->editColumn('amount', function ($row) {
                    $currency = session('currency');
                    $symbol = $currency['symbol'] ?? '';

                    return trim($symbol.' '.$this->commonUtil->num_f($row->amount));
                })
                ->editColumn('payment_method', function ($row) use ($paymentTypes) {
                    return $paymentTypes[$row->payment_method] ?? __('messages.na');
                })
                ->editColumn('bank_name', function ($row) {
                    return $row->bank_name ?: __('lang_v1.not_applicable');
                })
                ->editColumn('bank_reference', function ($row) {
                    return $row->bank_reference ?: __('lang_v1.not_applicable');
                })
                ->addColumn('contact_name', function ($row) {
                    return optional($row->contact)->name;
                })
                ->addColumn('location_name', function ($row) {
                    return optional($row->location)->name;
                })
                ->addColumn('action', function ($row) {
                    $buttons = [];
                    $buttons[] = '<a href="'.action([self::class, 'show'], [$row->id]).'" class="btn btn-xs btn-primary">'.__('messages.view').'</a>';
                    $buttons[] = '<a href="'.action([self::class, 'print'], [$row->id]).'" target="_blank" class="btn btn-xs btn-secondary">'.__('lang_v1.print').'</a>';

                    return implode(' ', $buttons);
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('payment_receipts.index');
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $customers = Contact::customersDropdown($business_id, false, true);
        $locations = BusinessLocation::forDropdown($business_id, false, false, true);
        $paymentTypes = $this->commonUtil->payment_types(null, false, $business_id);

        return view('payment_receipts.create', compact('customers', 'locations', 'paymentTypes'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $paymentTypes = $this->commonUtil->payment_types(null, false, $business_id);

        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'location_id' => 'nullable|exists:business_locations,id',
            'amount' => 'required',
            'payment_date' => 'required',
            'concept' => 'required|string|max:500',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => ['nullable', Rule::in(array_keys($paymentTypes))],
            'bank_name' => 'nullable|string|max:191',
            'bank_reference' => 'nullable|string|max:191',
        ]);

        try {
            $user_id = request()->session()->get('user.id');

            $ref_count = $this->commonUtil->setAndGetReferenceCount('payment_receipt', $business_id);
            $receipt_no = $this->commonUtil->generateReferenceNumber('payment_receipt', $ref_count, $business_id, 'REC-');

            $currency = session('currency');

            $data = $request->only([
                'contact_id',
                'location_id',
                'reference',
                'concept',
                'notes',
                'payment_method',
                'bank_name',
                'bank_reference',
            ]);
            $data['amount'] = $this->parseAmountInput($request->input('amount'));
            $paymentDateInput = $request->input('payment_date');
            $data['payment_date'] = $this->normalizePaymentDate($paymentDateInput);
            $data['business_id'] = $business_id;
            $data['created_by'] = $user_id;
            $data['receipt_no'] = $receipt_no;
            $data['currency_code'] = $currency['code'] ?? null;

            PaymentReceipt::create($data);

            $output = ['success' => true, 'msg' => __('lang_v1.added_success')];
        } catch (\Exception $e) {
            \Log::error($e);
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        if ($request->wantsJson()) {
            return $output;
        }

        return redirect()->action([self::class, 'index'])->with('status', $output);
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $receipt = PaymentReceipt::with(['contact', 'location', 'createdBy'])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        if (! auth()->user()->can('sell.view') && $receipt->created_by != auth()->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $paymentTypes = $this->commonUtil->payment_types(null, false, $business_id);
        $paymentMethodLabel = $paymentTypes[$receipt->payment_method] ?? __('messages.na');
        $amountInWords = NumberToWords::toSpanishCurrency((float) $receipt->amount);
        $business = $this->getBusinessDetails($business_id);
        $customLabels = json_decode($business['custom_labels'] ?? '{}', true);

        return view('payment_receipts.show', compact('receipt', 'paymentMethodLabel', 'amountInWords', 'customLabels', 'business'));
    }

    public function print($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $receipt = PaymentReceipt::with(['contact', 'location', 'createdBy'])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        if (! auth()->user()->can('sell.view') && $receipt->created_by != auth()->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $paymentTypes = $this->commonUtil->payment_types(null, false, $business_id);
        $paymentMethodLabel = $paymentTypes[$receipt->payment_method] ?? __('messages.na');
        $amountInWords = NumberToWords::toSpanishCurrency((float) $receipt->amount);
        $business = $this->getBusinessDetails($business_id);
        $customLabels = json_decode($business['custom_labels'] ?? '{}', true);

        return view('payment_receipts.print', compact('receipt', 'paymentMethodLabel', 'amountInWords', 'customLabels', 'business'));
    }

    protected function normalizePaymentDate($paymentDateInput)
    {
        if (empty($paymentDateInput)) {
            return null;
        }

        $paymentDateInput = trim($paymentDateInput);

        try {
            // First, try to parse it with the business format, which is the most likely.
            $businessFormat = session('business.date_format');
            if ($businessFormat) {
                return Carbon::createFromFormat($businessFormat, $paymentDateInput)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Ignore and try other formats.
        }

        try {
            // If business format fails, try a generic parse.
            return Carbon::parse($paymentDateInput)->format('Y-m-d');
        } catch (\Exception $e) {
            // As a last resort, return today's date.
            return Carbon::now()->format('Y-m-d');
        }
    }

    protected function getBusinessDetails($business_id)
    {
        $businessDetails = Business::find($business_id);

        if ($businessDetails) {
            $business = $businessDetails->toArray();
        } else {
            $business = session('business') ?: [];
        }

        if (empty($business['tax_number_1'])) {
            $business['tax_number_1'] = session('business.tax_number_1');
        }

        return $business;
    }

    protected function parseAmountInput($amountInput)
    {
        if (is_null($amountInput)) {
            return 0;
        }

        $value = trim((string) $amountInput);

        if ($value === '') {
            return 0;
        }

        $normalized = str_replace(["\u{00A0}", ' '], '', $value);
        $lastComma = strrpos($normalized, ',');
        $lastDot = strrpos($normalized, '.');
        $decimalSeparator = null;

        if ($lastComma !== false && $lastDot !== false) {
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
        } elseif ($lastComma !== false) {
            $decimalDigits = strlen($normalized) - $lastComma - 1;
            if ($decimalDigits > 0 && $decimalDigits <= 2) {
                $decimalSeparator = ',';
            }
        } elseif ($lastDot !== false) {
            $decimalDigits = strlen($normalized) - $lastDot - 1;
            if ($decimalDigits > 0 && $decimalDigits <= 2) {
                $decimalSeparator = '.';
            }
        }

        if ($decimalSeparator === ',') {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif ($decimalSeparator === '.') {
            $normalized = str_replace(',', '', $normalized);
        } else {
            $normalized = str_replace(['.', ','], '', $normalized);
        }

        $normalized = preg_replace('/[^0-9\-\.]/', '', $normalized);

        if ($normalized === '' || $normalized === '.' || $normalized === '-' || $normalized === '-.') {
            return 0;
        }

        return (float) $normalized;
    }
}
