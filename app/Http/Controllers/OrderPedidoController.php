<?php

namespace App\Http\Controllers;

use App\OrderPedido;
use App\OrderPedidoLine;
use App\OrderComment;
use App\Contact;
use App\BusinessLocation;
use App\Product;
use App\Variation;
use App\User;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class OrderPedidoController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Listado de Ã³rdenes de pedido
     */
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $orders = OrderPedido::forBusiness($business_id)
                ->with(['contact', 'createdBy', 'location'])
                ->select('order_pedidos.*');

            // Filtros
            if ($request->filled('status')) {
                $orders->where('status', $request->status);
            }
            if ($request->filled('priority')) {
                $orders->where('priority', $request->priority);
            }
            if ($request->filled('contact_id')) {
                $orders->where('contact_id', $request->contact_id);
            }
            if ($request->filled('date_range')) {
                $dates = explode(' - ', $request->date_range);
                if (count($dates) == 2) {
                    $orders->whereBetween('order_date', [
                        Carbon::parse($dates[0])->startOfDay(),
                        Carbon::parse($dates[1])->endOfDay(),
                    ]);
                }
            }

            return DataTables::of($orders)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    $html .= '<button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>';
                    $html .= '<ul class="dropdown-menu dropdown-menu-right">';
                    $html .= '<li><a href="' . route('order-pedidos.show', $row->id) . '"><i class="fas fa-eye"></i> Ver Detalle</a></li>';
                    $html .= '<li><a href="' . route('order-pedidos.edit', $row->id) . '"><i class="fas fa-edit"></i> Editar</a></li>';
                    if ($row->status === 'draft' || $row->status === 'pending') {
                        $html .= '<li><a href="#" class="delete-order" data-href="' . route('order-pedidos.destroy', $row->id) . '"><i class="fas fa-trash text-danger"></i> Eliminar</a></li>';
                    }
                    $html .= '</ul></div>';
                    return $html;
                })
                ->editColumn('order_number', function ($row) {
                    return '<a href="' . route('order-pedidos.show', $row->id) . '" class="tw-font-bold tw-text-blue-600">' . $row->order_number . '</a>';
                })
                ->editColumn('contact_id', function ($row) {
                    return $row->contact ? $row->contact->name : '-';
                })
                ->editColumn('order_date', function ($row) {
                    return $row->order_date->format('d/m/Y');
                })
                ->editColumn('expected_delivery_date', function ($row) {
                    if (!$row->expected_delivery_date) return '-';
                    $isOverdue = $row->expected_delivery_date->isPast() && !in_array($row->status, ['completed', 'cancelled']);
                    $class = $isOverdue ? 'tw-text-red-600 tw-font-bold' : '';
                    return '<span class="' . $class . '">' . $row->expected_delivery_date->format('d/m/Y') . ($isOverdue ? ' âš ï¸' : '') . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $label = $row->status_label;
                    return '<span class="badge ' . $label['class'] . '">' . $label['text'] . '</span>';
                })
                ->editColumn('priority', function ($row) {
                    $label = $row->priority_label;
                    return '<span class="badge ' . $label['class'] . '">' . $label['text'] . '</span>';
                })
                ->editColumn('total', function ($row) {
                    return '<span class="tw-font-semibold">$' . number_format($row->total, 2) . '</span>';
                })
                ->addColumn('progress', function ($row) {
                    $percent = $row->progress_percent;
                    $color = $percent >= 100 ? 'bg-success' : ($percent > 50 ? 'bg-info' : ($percent > 0 ? 'bg-warning' : 'bg-secondary'));
                    return '<div class="progress" style="height:8px;margin-bottom:0"><div class="progress-bar ' . $color . '" style="width:' . $percent . '%"></div></div><small class="text-muted">' . $percent . '%</small>';
                })
                ->addColumn('tasks_count', function ($row) {
                    $total = $row->tasks()->count();
                    $completed = $row->tasks()->where('status', 'completed')->count();
                    if ($total === 0) return '<span class="text-muted">-</span>';
                    return '<span class="badge bg-primary">' . $completed . '/' . $total . '</span>';
                })
                ->rawColumns(['action', 'order_number', 'status', 'priority', 'total', 'progress', 'tasks_count', 'expected_delivery_date'])
                ->make(true);
        }

        // EstadÃ­sticas para los cards
        $stats = [
            'total' => OrderPedido::forBusiness($business_id)->count(),
            'pending' => OrderPedido::forBusiness($business_id)->byStatus('pending')->count(),
            'in_progress' => OrderPedido::forBusiness($business_id)->byStatus('in_progress')->count(),
            'completed' => OrderPedido::forBusiness($business_id)->byStatus('completed')->count(),
            'overdue' => OrderPedido::forBusiness($business_id)->overdue()->count(),
            'total_value' => OrderPedido::forBusiness($business_id)->whereNotIn('status', ['cancelled'])->sum('total'),
        ];

        return view('order_pedidos.index', compact('stats'));
    }

    /**
     * Formulario de nueva orden
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        $contacts = Contact::where('business_id', $business_id)
            ->pluck('name', 'id');

        $locations = BusinessLocation::where('business_id', $business_id)
            ->pluck('name', 'id');

        $users = User::where('business_id', $business_id)
            ->select(DB::raw("CONCAT(first_name, ' ', COALESCE(last_name, '')) as name"), 'id')
            ->pluck('name', 'id');

        $order_number = OrderPedido::generateOrderNumber($business_id);

        $products = Product::where('business_id', $business_id)
            ->where('is_inactive', 0)
            ->select('id', 'name', 'sku')
            ->get();

        return view('order_pedidos.create', compact(
            'contacts', 'locations', 'users', 'order_number', 'products'
        ));
    }

    /**
     * Guardar nueva orden
     */
    public function store(Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            DB::beginTransaction();

            $order = OrderPedido::create([
                'business_id' => $business_id,
                'contact_id' => $request->contact_id,
                'location_id' => $request->location_id,
                'created_by' => auth()->id(),
                'order_number' => OrderPedido::generateOrderNumber($business_id),
                'type' => $request->type ?? 'purchase',
                'status' => $request->status ?? 'draft',
                'priority' => $request->priority ?? 'medium',
                'reference' => $request->reference,
                'order_date' => $request->order_date ?? now()->toDateString(),
                'expected_delivery_date' => $request->expected_delivery_date,
                'shipping_address' => $request->shipping_address,
                'shipping_method' => $request->shipping_method,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms ?? $request->terms_conditions,
                'discount_type' => $request->discount_type,
                'discount_amount' => $request->discount_amount ?? $request->order_discount ?? 0,
            ]);

            // Guardar lÃ­neas
            if ($request->has('lines')) {
                foreach ($request->lines as $lineData) {
                    if (empty($lineData['product_name'])) continue;

                    $line = new OrderPedidoLine([
                        'product_id' => $lineData['product_id'] ?? null,
                        'variation_id' => $lineData['variation_id'] ?? null,
                        'product_name' => $lineData['product_name'],
                        'sku' => $lineData['sku'] ?? null,
                        'quantity' => $lineData['quantity'] ?? 1,
                        'unit' => $lineData['unit'] ?? null,
                        'unit_price' => $lineData['unit_price'] ?? 0,
                        'tax_percent' => $lineData['tax_percent'] ?? 0,
                        'discount_percent' => $lineData['discount_percent'] ?? 0,
                        'sort_order' => $lineData['sort_order'] ?? 0,
                        'description' => $lineData['description'] ?? null,
                    ]);
                    $line->calculateLineTotal();
                    $order->lines()->save($line);
                }
            }

            $order->recalculateTotals();

            // Registrar actividad
            $order->addSystemComment('Orden de pedido creada');

            DB::commit();

            $output = [
                'success' => true,
                'msg' => 'Orden de pedido creada exitosamente',
                'redirect' => route('order-pedidos.show', $order->id),
            ];
        } catch (\Exception $e) {
            DB::rollback();
            $output = [
                'success' => false,
                'msg' => 'Error al crear la orden: ' . $e->getMessage(),
            ];
        }

        if ($request->ajax()) {
            return response()->json($output);
        }

        return redirect()->route('order-pedidos.show', $order->id ?? 0)
                         ->with('status', $output);
    }

    /**
     * Detalle de la orden (vista tipo Perfex)
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $order = OrderPedido::forBusiness($business_id)
            ->with([
                'contact', 'location', 'createdBy', 'approvedBy',
                'lines.product', 'lines.variation',
                'tasks.assignedTo', 'tasks.checklists',
                'comments.user', 'attachments',
            ])
            ->findOrFail($id);

        $users = User::where('business_id', $business_id)
            ->select(DB::raw("CONCAT(first_name, ' ', COALESCE(last_name, '')) as name"), 'id')
            ->pluck('name', 'id');

        $statuses = [
            'draft' => 'Borrador',
            'pending' => 'Pendiente',
            'approved' => 'Aprobada',
            'in_progress' => 'En Proceso',
            'partial' => 'Parcial',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
        ];

        return view('order_pedidos.show', compact('order', 'users', 'statuses'));
    }

    /**
     * Formulario de ediciÃ³n
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $order = OrderPedido::forBusiness($business_id)
            ->with(['lines'])
            ->findOrFail($id);

        $contacts = Contact::where('business_id', $business_id)
            ->pluck('name', 'id');

        $locations = BusinessLocation::where('business_id', $business_id)
            ->pluck('name', 'id');

        $users = User::where('business_id', $business_id)
            ->select(DB::raw("CONCAT(first_name, ' ', COALESCE(last_name, '')) as name"), 'id')
            ->pluck('name', 'id');

        $products = Product::where('business_id', $business_id)
            ->where('is_inactive', 0)
            ->select('id', 'name', 'sku')
            ->get();

        $statuses = [
            'draft' => 'Borrador',
            'pending' => 'Pendiente',
            'approved' => 'Aprobada',
            'in_progress' => 'En Proceso',
            'partial' => 'Parcial',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
        ];

        return view('order_pedidos.edit', compact(
            'order', 'contacts', 'locations', 'users', 'products', 'statuses'
        ));
    }

    /**
     * Actualizar orden
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            DB::beginTransaction();

            $order = OrderPedido::forBusiness($business_id)->findOrFail($id);
            $oldStatus = $order->status;

            $order->update([
                'contact_id' => $request->contact_id,
                'location_id' => $request->location_id,
                'type' => $request->type ?? $order->type,
                'status' => $request->status ?? $order->status,
                'priority' => $request->priority ?? $order->priority,
                'order_date' => $request->order_date ?? $order->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'actual_delivery_date' => $request->actual_delivery_date,
                'shipping_method' => $request->shipping_method,
                'shipping_address' => $request->shipping_address,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms ?? $request->terms_conditions,
                'discount_type' => $request->discount_type,
                'discount_amount' => $request->discount_amount ?? $request->order_discount ?? 0,
            ]);

            // Actualizar lÃ­neas
            if ($request->has('lines')) {
                // Eliminar lÃ­neas existentes y recrear
                $order->lines()->delete();

                foreach ($request->lines as $lineData) {
                    if (empty($lineData['product_name'])) continue;

                    $line = new OrderPedidoLine([
                        'product_id' => $lineData['product_id'] ?? null,
                        'variation_id' => $lineData['variation_id'] ?? null,
                        'product_name' => $lineData['product_name'],
                        'sku' => $lineData['sku'] ?? null,
                        'quantity' => $lineData['quantity'] ?? 1,
                        'quantity_received' => $lineData['quantity_received'] ?? 0,
                        'unit' => $lineData['unit'] ?? null,
                        'unit_price' => $lineData['unit_price'] ?? 0,
                        'tax_percent' => $lineData['tax_percent'] ?? 0,
                        'discount_percent' => $lineData['discount_percent'] ?? 0,
                        'sort_order' => $lineData['sort_order'] ?? 0,
                        'description' => $lineData['description'] ?? null,
                    ]);
                    $line->calculateLineTotal();
                    $order->lines()->save($line);
                }
            }

            $order->recalculateTotals();

            // Si cambiÃ³ el estado, registrar
            if ($oldStatus !== $order->status) {
                $statusLabels = $order->status_label;
                $order->addSystemComment("Estado cambiado a: {$statusLabels['text']}");
                
                if ($order->status === 'approved') {
                    $order->approved_by = auth()->id();
                    $order->approved_at = now();
                    $order->save();
                }
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => 'Orden actualizada exitosamente',
                'redirect' => url('/order-pedidos/' . $order->id),
            ];
        } catch (\Exception $e) {
            DB::rollback();
            $output = [
                'success' => false,
                'msg' => 'Error al actualizar: ' . $e->getMessage(),
            ];
        }

        if ($request->ajax()) {
            return response()->json($output);
        }

        return redirect()->route('order-pedidos.show', $id)
                         ->with('status', $output);
    }

    /**
     * Eliminar orden
     */
    public function destroy($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $order = OrderPedido::forBusiness($business_id)->findOrFail($id);
            $order->delete();

            $output = [
                'success' => true,
                'msg' => 'Orden eliminada exitosamente',
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => 'Error al eliminar: ' . $e->getMessage(),
            ];
        }

        return response()->json($output);
    }

    /**
     * Cambiar estado de orden (AJAX)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $order = OrderPedido::forBusiness($business_id)->findOrFail($id);
            $oldStatus = $order->status;

            $order->status = $request->status;

            if ($request->status === 'approved') {
                $order->approved_by = auth()->id();
                $order->approved_at = now();
            }
            if ($request->status === 'completed') {
                $order->actual_delivery_date = now()->toDateString();
            }

            $order->save();

            $statusLabel = $order->status_label;
            $order->addSystemComment("Estado cambiado de \"{$this->getStatusText($oldStatus)}\" a \"{$statusLabel['text']}\"");

            return response()->json([
                'success' => true,
                'msg' => 'Estado actualizado',
                'new_status' => $statusLabel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Agregar comentario (AJAX)
     */
    public function addComment(Request $request, $id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $order = OrderPedido::forBusiness($business_id)->findOrFail($id);

            $comment = $order->comments()->create([
                'user_id' => auth()->id(),
                'comment' => $request->comment,
                'type' => 'comment',
            ]);

            $comment->load('user');

            return response()->json([
                'success' => true,
                'msg' => 'Comentario agregado',
                'comment' => [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'user_name' => $comment->user->first_name . ' ' . ($comment->user->last_name ?? ''),
                    'created_at' => $comment->created_at->diffForHumans(),
                    'type' => $comment->type,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Buscar productos para agregar a la orden (AJAX)
     */
    public function searchProducts(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $term = $request->input('term', '');

        $products = Product::where('business_id', $business_id)
            ->where('is_inactive', 0)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%");
            })
            ->with(['variations' => function ($q) {
                $q->with(['product_variation']);
            }])
            ->limit(20)
            ->get();

        $results = [];
        foreach ($products as $product) {
            foreach ($product->variations as $variation) {
                $variationName = $variation->product_variation ? $variation->product_variation->name : '';
                $name = $product->name;
                if ($variationName && $variationName !== 'DUMMY') {
                    $name .= ' - ' . $variationName;
                }
                if ($variation->name && $variation->name !== 'DUMMY') {
                    $name .= ' (' . $variation->name . ')';
                }

                $results[] = [
                    'id' => $product->id,
                    'variation_id' => $variation->id,
                    'text' => $name,
                    'sku' => $variation->sub_sku ?? $product->sku,
                    'name' => $name,
                    'default_purchase_price' => $variation->default_purchase_price ?? 0,
                    'unit' => $product->unit ? $product->unit->short_name : '',
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    private function getStatusText($status)
    {
        $texts = [
            'draft' => 'Borrador', 'pending' => 'Pendiente', 'approved' => 'Aprobada',
            'in_progress' => 'En Proceso', 'partial' => 'Parcial',
            'completed' => 'Completada', 'cancelled' => 'Cancelada',
        ];
        return $texts[$status] ?? $status;
    }
}
