<?php

namespace App\Http\Controllers;

use App\Services\Cfe\CFEService;
use App\Services\Cfe\CFEXmlGenerator;
use App\Services\Cfe\RutService;
use App\Transaction;
use App\TransactionSellLine;
use App\TransactionPayment;
use App\VariationLocationDetails;
use App\Business;
use App\Contact;
use App\CfeSubmission;
use App\Utils\TransactionUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controlador para Comprobantes Fiscales Electrónicos (CFE)
 * Cumple con normativa DGI y BPS Uruguay
 */
class CFEController extends Controller
{
    protected CFEService $cfeService;
    protected CFEXmlGenerator $xmlGenerator;
    protected RutService $rutService;
    protected TransactionUtil $transactionUtil;

    // Tipos de CFE según DGI Uruguay
    public const CFE_TYPES = [
        101 => 'e-Ticket',
        102 => 'Nota de Crédito de e-Ticket',
        103 => 'Nota de Débito de e-Ticket',
        111 => 'e-Factura',
        112 => 'Nota de Crédito de e-Factura',
        113 => 'Nota de Débito de e-Factura',
        121 => 'e-Ticket Contingencia',
        122 => 'NC e-Ticket Contingencia',
        123 => 'ND e-Ticket Contingencia',
        131 => 'e-Factura Contingencia',
        132 => 'NC e-Factura Contingencia',
        133 => 'ND e-Factura Contingencia',
        141 => 'e-Ticket Venta por Cuenta Ajena',
        142 => 'NC e-Ticket Venta por Cuenta Ajena',
        143 => 'ND e-Ticket Venta por Cuenta Ajena',
        151 => 'e-Boleta de Entrada',
        152 => 'NC e-Boleta de Entrada',
        153 => 'ND e-Boleta de Entrada',
        181 => 'e-Remito',
        182 => 'e-Remito de Exportación',
        201 => 'e-Resguardo',
    ];

    // Formas de pago DGI
    public const PAYMENT_METHODS = [
        1 => 'Contado',
        2 => 'Crédito',
        3 => 'Contra Entrega',
        4 => 'Cheque',
        5 => 'Transferencia Bancaria',
        6 => 'Tarjeta de Débito',
        7 => 'Tarjeta de Crédito',
        8 => 'Mercado Pago',
        9 => 'Otro',
    ];

    // Tasas de IVA Uruguay
    public const IVA_RATES = [
        0 => 'Exento',
        10 => 'Tasa Mínima (10%)',
        22 => 'Tasa Básica (22%)',
    ];

    // Departamentos Uruguay
    public const DEPARTMENTS = [
        'Artigas', 'Canelones', 'Cerro Largo', 'Colonia', 'Durazno',
        'Flores', 'Florida', 'Lavalleja', 'Maldonado', 'Montevideo',
        'Paysandú', 'Río Negro', 'Rivera', 'Rocha', 'Salto',
        'San José', 'Soriano', 'Tacuarembó', 'Treinta y Tres',
    ];

    public function __construct(
        CFEService $cfeService,
        CFEXmlGenerator $xmlGenerator,
        RutService $rutService,
        TransactionUtil $transactionUtil
    ) {
        $this->cfeService = $cfeService;
        $this->xmlGenerator = $xmlGenerator;
        $this->rutService = $rutService;
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Lista de CFE emitidos
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $cfe_submissions = CfeSubmission::where('business_id', $business_id)
                ->with(['transaction', 'contact'])
                ->orderBy('created_at', 'desc');

            return DataTables::of($cfe_submissions)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    $html .= '<button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown">';
                    $html .= __('messages.actions') . ' <span class="caret"></span>';
                    $html .= '</button>';
                    $html .= '<ul class="dropdown-menu dropdown-menu-right">';
                    
                    $html .= '<li><a href="' . route('cfe.show', $row->id) . '"><i class="fas fa-eye"></i> ' . __('messages.view') . '</a></li>';
                    $html .= '<li><a href="' . route('cfe.edit', $row->id) . '"><i class="fas fa-edit"></i> Editar</a></li>';
                    $html .= '<li><a href="' . route('cfe.print', $row->id) . '" target="_blank"><i class="fas fa-print"></i> Imprimir Ticket</a></li>';
                    $html .= '<li><a href="' . route('cfe.download-xml', $row->id) . '"><i class="fas fa-file-code"></i> Descargar XML</a></li>';
                    
                    if ($row->status !== 'accepted') {
                        $html .= '<li><a href="#" class="resend-cfe" data-id="' . $row->id . '"><i class="fas fa-paper-plane"></i> Reenviar a DGI</a></li>';
                    }
                    
                    $html .= '</ul></div>';
                    return $html;
                })
                ->addColumn('cfe_type_name', function ($row) {
                    return self::CFE_TYPES[$row->cfe_type] ?? $row->cfe_type;
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'pending' => '<span class="label label-warning">Pendiente</span>',
                        'submitted' => '<span class="label label-info">Enviado</span>',
                        'accepted' => '<span class="label label-success">Aceptado DGI</span>',
                        'rejected' => '<span class="label label-danger">Rechazado</span>',
                        'error' => '<span class="label label-danger">Error</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="label label-default">' . $row->status . '</span>';
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 2);
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d/m/Y H:i');
                })
                ->addColumn('cae_due_date_formatted', function ($row) {
                    if ($row->cae_due_date) {
                        try {
                            return Carbon::parse($row->cae_due_date)->format('d/m/Y');
                        } catch (\Exception $e) {
                            return $row->cae_due_date;
                        }
                    }
                    return null;
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('cfe.index', [
            'cfe_types' => self::CFE_TYPES,
        ]);
    }

    /**
     * Formulario para crear nueva factura/ticket CFE
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business = Business::find($business_id);

        // Clientes - obtener todos los clientes activos e inactivos
        $customers = Contact::where('business_id', $business_id)
            ->where('type', 'customer')
            ->whereNull('deleted_at')
            ->select('id', 'name', 'supplier_business_name', 'mobile', 'tax_number')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($customer) {
                $displayName = $customer->supplier_business_name 
                    ? $customer->supplier_business_name . ' - ' . $customer->name 
                    : $customer->name;
                if ($customer->tax_number) {
                    $displayName .= ' (' . $customer->tax_number . ')';
                }
                return [$customer->id => $displayName];
            });

        // Sucursales
        $business_locations = $business->locations()->pluck('name', 'id');

        // Productos
        $products = \App\Product::where('business_id', $business_id)
            ->active()
            ->with(['variations'])
            ->get();

        // Próximo número de CFE
        $last_cfe = CfeSubmission::where('business_id', $business_id)
            ->where('series', config('cfe.default_series', 'A'))
            ->orderBy('number', 'desc')
            ->first();
        
        $next_number = $last_cfe ? ($last_cfe->number + 1) : 1;

        // Tipos de contacto para el modal de agregar cliente
        $types = [
            'customer' => __('report.customer'),
        ];

        // Customer groups para el modal
        $customer_groups = \App\CustomerGroup::forDropdown($business_id);

        return view('cfe.create', [
            'business' => $business,
            'customers' => $customers,
            'business_locations' => $business_locations,
            'products' => $products,
            'cfe_types' => self::CFE_TYPES,
            'payment_methods' => self::PAYMENT_METHODS,
            'iva_rates' => self::IVA_RATES,
            'departments' => self::DEPARTMENTS,
            'next_number' => $next_number,
            'default_series' => config('cfe.default_series', 'A'),
            'default_cfe_type' => config('cfe.default_cfe_type', 111),
            'types' => $types,
            'customer_groups' => $customer_groups,
        ]);
    }

    /**
     * Guardar y procesar nuevo CFE
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Validar según si el cliente es del sistema o manual
        $isManualCustomer = $request->input('customer_manual') == '1';

        $rules = [
            'cfe_type' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];

        if ($isManualCustomer) {
            $rules['customer_name_manual'] = 'required|string|max:255';
        } else {
            $rules['customer_id'] = 'required|exists:contacts,id';
        }

        $request->validate($rules);

        $business_id = request()->session()->get('user.business_id');
        $user_id = Auth::id();

        try {
            DB::beginTransaction();

            $business = Business::find($business_id);
            $location = \App\BusinessLocation::find($request->location_id);

            // Datos del receptor: cliente del sistema o manual
            $customer = null;
            $receiverDocType = 'CI';
            $receiverDocument = '';
            $receiverName = '';
            $receiverAddress = '';
            $receiverCity = 'Montevideo';
            $receiverDepartment = 'Montevideo';
            $contactId = null;

            if ($isManualCustomer) {
                $receiverName = $request->input('customer_name_manual');
                $receiverDocument = preg_replace('/[^0-9]/', '', $request->input('customer_rut_manual', ''));
                $receiverDocType = strlen($receiverDocument) === 12 ? 'RUT' : 'CI';
                $receiverAddress = $request->input('customer_address_manual', '');
                $receiverCity = $request->input('customer_city_manual', 'Montevideo');
                $receiverDepartment = 'Montevideo';
            } else {
                $customer = Contact::findOrFail($request->customer_id);
                $contactId = $customer->id;
                $receiverDocType = $this->getDocumentType($customer);
                $receiverDocument = $customer->tax_number ?? $customer->mobile ?? '';
                $receiverName = $customer->name ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
                $receiverAddress = $customer->address_line_1 ?? '';
                $receiverCity = $customer->city ?? 'Montevideo';
                $receiverDepartment = $customer->state ?? 'Montevideo';
            }

            // Calcular totales
            $subtotal = 0;
            $tax_amount = 0;
            $items = [];

            foreach ($request->items as $item) {
                $product = !empty($item['product_id']) ? \App\Product::find($item['product_id']) : null;
                $quantity = (float) $item['quantity'];
                $unit_price = (float) $item['unit_price'];
                $iva_rate = (float) ($item['iva_rate'] ?? 22);
                
                // Nombre del item: producto del sistema o descripción manual
                $item_name = '';
                if ($product) {
                    $item_name = $product->name;
                } elseif (!empty($item['name'])) {
                    $item_name = $item['name'];
                } else {
                    $item_name = 'Producto/Servicio';
                }
                
                $line_total = $quantity * $unit_price;
                $line_tax = $line_total * ($iva_rate / 100);
                
                $subtotal += $line_total;
                $tax_amount += $line_tax;

                $items[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item_name,
                    'description' => $item['description'] ?? ($product ? $product->sku : ''),
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'unit' => $item['unit'] ?? 'unidad',
                    'iva_rate' => $iva_rate,
                    'line_total' => $line_total,
                    'line_tax' => $line_tax,
                ];
            }

            $total = $subtotal + $tax_amount;

            // Obtener próximo número
            $series = $request->series ?? config('cfe.default_series', 'A');
            $last_cfe = CfeSubmission::where('business_id', $business_id)
                ->where('series', $series)
                ->orderBy('number', 'desc')
                ->first();
            $number = $last_cfe ? ($last_cfe->number + 1) : 1;

            // Crear transacción si no existe
            $transaction = null;
            if ($request->transaction_id) {
                $transaction = Transaction::find($request->transaction_id);
            }

            // Crear registro CFE
            $cfe = new CfeSubmission();
            $cfe->business_id = $business_id;
            $cfe->location_id = $request->location_id;
            $cfe->transaction_id = $transaction ? $transaction->id : null;
            $cfe->contact_id = $contactId;
            $cfe->user_id = $user_id;
            $cfe->cfe_type = $request->cfe_type;
            $cfe->series = $series;
            $cfe->number = $number;
            $cfe->issue_date = Carbon::now();
            $cfe->due_date = $request->due_date ? Carbon::parse($request->due_date) : Carbon::now();
            $cfe->payment_method = $request->payment_method ?? 1;
            $cfe->currency = $request->currency ?? 'UYU';
            $cfe->exchange_rate = $request->exchange_rate ?? 1;
            $cfe->subtotal = $subtotal;
            $cfe->tax_amount = $tax_amount;
            $cfe->total = $total;
            $cfe->items = $items;
            $cfe->status = 'pending';
            
            // Datos emisor
            $cfe->emitter_rut = config('cfe.emitter_rut') ?? $business->tax_number_1;
            $cfe->emitter_name = $business->name;
            $cfe->emitter_address = $location ? $location->landmark : '';
            $cfe->emitter_city = $location ? $location->city : 'Montevideo';
            $cfe->emitter_department = $location ? $location->state : 'Montevideo';
            
            // Datos receptor
            $cfe->receiver_doc_type = $receiverDocType;
            $cfe->receiver_document = $receiverDocument;
            $cfe->receiver_name = $receiverName;
            $cfe->receiver_address = $receiverAddress;
            $cfe->receiver_city = $receiverCity;
            $cfe->receiver_department = $receiverDepartment;

            $cfe->save();

            // Generar XML CFE
            $cfeData = $this->prepareCFEData($cfe);
            $xmlContent = $this->xmlGenerator->generateCFE($cfeData);
            
            $cfe->xml_content = $xmlContent;
            $cfe->save();

            // ── Crear Transaction de venta vinculada al CFE ──────────────
            // Solo para tipos de venta (no notas de crédito/débito/remitos)
            $cfe_sale_types = [101, 111, 121, 131, 141];
            if (in_array((int)$request->cfe_type, $cfe_sale_types)) {
                $transaction = $this->createTransactionFromCFE($cfe, $business, $items, $subtotal, $tax_amount, $total, $user_id, $business_id);
                if ($transaction) {
                    $cfe->transaction_id = $transaction->id;
                    $cfe->save();
                }
            }
            // ─────────────────────────────────────────────────────────────

            // Si está configurado auto_submit, enviar a DGI
            if (config('cfe.auto_submit', false)) {
                $result = $this->submitToDGI($cfe);
                if (!$result['success']) {
                    Log::warning('CFE guardado pero no enviado a DGI', [
                        'cfe_id' => $cfe->id,
                        'errors' => $result['errors'] ?? [],
                    ]);
                }
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => 'CFE creado exitosamente. Número: ' . $series . '-' . $number,
                'cfe_id' => $cfe->id,
                'redirect' => route('cfe.show', $cfe->id),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando CFE', ['message' => $e->getMessage()]);
            
            $output = [
                'success' => false,
                'msg' => 'Error al crear CFE: ' . $e->getMessage(),
            ];
        }

        return response()->json($output);
    }

    /**
     * Crea una Transaction de venta vinculada a un CFE.
     * Esto permite que el CFE aparezca en el dashboard, informes de ventas
     * y descuente el stock de los productos correctamente.
     *
     * @param  CfeSubmission  $cfe
     * @param  Business       $business
     * @param  array          $items        ítems ya calculados del CFE
     * @param  float          $subtotal
     * @param  float          $tax_amount
     * @param  float          $total
     * @param  int            $user_id
     * @param  int            $business_id
     * @return Transaction|null
     */
    private function createTransactionFromCFE(
        CfeSubmission $cfe,
        Business $business,
        array $items,
        float $subtotal,
        float $tax_amount,
        float $total,
        int $user_id,
        int $business_id
    ): ?Transaction {
        try {
            // Obtener número de factura del esquema configurado
            $invoice_no = $this->transactionUtil->getInvoiceNumber(
                $business_id,
                'final',
                $cfe->location_id
            );

            // Mapeo de forma de pago CFE → método de pago del sistema
            $payment_method_map = [
                1 => 'cash',        // Contado
                2 => 'credit',      // Crédito
                3 => 'cash',        // Contra Entrega
                4 => 'cheque',      // Cheque
                5 => 'bank_transfer', // Transferencia
                6 => 'card',        // Tarjeta débito
                7 => 'card',        // Tarjeta crédito
                8 => 'custom_pay_1', // Mercado Pago
                9 => 'other',       // Otro
            ];
            $pay_method = $payment_method_map[$cfe->payment_method] ?? 'cash';

            // Determinar contact_id: usar el del CFE si existe, sino buscar "Consumidor Final"
            $contact_id = $cfe->contact_id;
            if (empty($contact_id)) {
                $walk_in = \App\Contact::where('business_id', $business_id)
                    ->where('type', 'customer')
                    ->where('is_default', 1)
                    ->first();
                $contact_id = $walk_in ? $walk_in->id : \App\Contact::where('business_id', $business_id)
                    ->where('type', 'customer')
                    ->value('id');
            }

            // Crear la transacción
            $transaction = Transaction::create([
                'business_id'         => $business_id,
                'location_id'         => $cfe->location_id,
                'type'                => 'sell',
                'status'              => 'final',
                'payment_status'      => 'paid',
                'contact_id'          => $contact_id,
                'invoice_no'          => $invoice_no,
                'transaction_date'    => $cfe->issue_date ?? Carbon::now(),
                'total_before_tax'    => $subtotal,
                'tax_amount'          => $tax_amount,
                'final_total'         => $total,
                'discount_type'       => 'fixed',
                'discount_amount'     => 0,
                'created_by'          => $user_id,
                'shipping_charges'    => 0,
                'additional_notes'    => 'CFE #' . $cfe->series . '-' . $cfe->number . ' (' . ($cfe->receiver_name ?: 'Consumidor Final') . ')',
            ]);

            // ── Crear líneas de venta y descontar stock ──────────────────
            foreach ($items as $item) {
                $product_id   = !empty($item['product_id']) ? (int)$item['product_id'] : null;
                $variation_id = null;

                if ($product_id) {
                    // Buscar la variación "DUMMY" (variante única / producto simple)
                    $variation = \App\Variation::where('product_id', $product_id)->first();
                    $variation_id = $variation ? $variation->id : null;
                }

                $qty        = (float) $item['quantity'];
                $unit_price = (float) $item['unit_price'];
                $iva_rate   = (float) ($item['iva_rate'] ?? 22);
                $item_tax   = $unit_price * ($iva_rate / 100);
                $unit_price_inc_tax = $unit_price + $item_tax;

                // Buscar tax_id que coincida con la tasa de IVA configurada
                $tax_id = null;
                if ($iva_rate > 0) {
                    $tax = \App\TaxRate::where('business_id', $business_id)
                        ->where('amount', $iva_rate)
                        ->where('is_tax_group', 0)
                        ->first();
                    $tax_id = $tax ? $tax->id : null;
                }

                $sell_line = TransactionSellLine::create([
                    'transaction_id'          => $transaction->id,
                    'product_id'              => $product_id,
                    'variation_id'            => $variation_id,
                    'quantity'                => $qty,
                    'unit_price_before_discount' => $unit_price,
                    'unit_price'              => $unit_price,
                    'line_discount_type'      => 'fixed',
                    'line_discount_amount'    => 0,
                    'item_tax'                => $item_tax,
                    'tax_id'                  => $tax_id,
                    'unit_price_inc_tax'      => $unit_price_inc_tax,
                    'sell_line_note'          => $item['name'] ?? '',
                    'quantity_returned'       => 0,
                    'sub_unit_id'             => null,
                ]);

                // Descontar stock si el producto tiene control de inventario
                if ($product_id && $variation_id) {
                    $product = \App\Product::find($product_id);
                    if ($product && $product->enable_stock == 1) {
                        VariationLocationDetails::where('variation_id', $variation_id)
                            ->where('product_id', $product_id)
                            ->where('location_id', $cfe->location_id)
                            ->decrement('qty_available', $qty);
                    }
                }
            }

            // ── Registrar el pago ─────────────────────────────────────────
            // Solo si la forma de pago es contado (no crédito)
            if ($cfe->payment_method != 2) {
                TransactionPayment::create([
                    'transaction_id'  => $transaction->id,
                    'amount'          => $total,
                    'method'          => $pay_method,
                    'is_return'       => 0,
                    'paid_on'         => $cfe->issue_date ?? Carbon::now(),
                    'created_by'      => $user_id,
                    'business_id'     => $business_id,
                    'payment_for'     => $contact_id,
                    'payment_ref_no'  => 'CFE-' . $cfe->series . $cfe->number,
                ]);
            }

            Log::info('Transaction creada desde CFE', [
                'cfe_id'         => $cfe->id,
                'transaction_id' => $transaction->id,
                'total'          => $total,
            ]);

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Error al crear Transaction desde CFE', [
                'cfe_id'  => $cfe->id,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            // No lanzamos excepción para no cancelar la creación del CFE
            return null;
        }
    }

    /**
     * Mostrar detalle de un CFE
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $cfe = CfeSubmission::where('business_id', $business_id)
            ->with(['transaction', 'contact', 'location'])
            ->findOrFail($id);

        return view('cfe.show', [
            'cfe' => $cfe,
            'cfe_types' => self::CFE_TYPES,
            'payment_methods' => self::PAYMENT_METHODS,
        ]);
    }

    /**
     * Formulario de edición de CFE
     */
    public function edit($id)
    {
        if (!auth()->user()->can('sell.update') && !auth()->user()->hasRole('Admin#' . session('business.id'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $cfe = CfeSubmission::where('business_id', $business_id)
            ->with(['transaction', 'contact', 'location'])
            ->findOrFail($id);

        return view('cfe.edit', [
            'cfe' => $cfe,
            'cfe_types' => self::CFE_TYPES,
            'payment_methods' => self::PAYMENT_METHODS,
            'iva_rates' => self::IVA_RATES,
        ]);
    }

    /**
     * Actualizar datos de un CFE
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('sell.update') && !auth()->user()->hasRole('Admin#' . session('business.id'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $cfe = CfeSubmission::where('business_id', $business_id)->findOrFail($id);

        $request->validate([
            'notes'        => 'nullable|string|max:1000',
            'cae'          => 'nullable|string|max:50',
            'cae_due_date' => 'nullable|date',
            'status'       => 'required|in:pending,submitted,accepted,rejected,error',
            'payment_method' => 'required|integer',
        ]);

        $cfe->notes          = $request->input('notes');
        $cfe->payment_method = $request->input('payment_method');
        $cfe->status         = $request->input('status');

        if ($request->filled('cae')) {
            $cfe->cae = $request->input('cae');
        }
        if ($request->filled('cae_due_date')) {
            $cfe->cae_due_date = $request->input('cae_due_date');
        }

        // Actualizar ítems si se enviaron
        if ($request->has('items') && is_array($request->input('items'))) {
            $items = [];
            foreach ($request->input('items') as $item) {
                if (!empty($item['name']) || !empty($item['description'])) {
                    $qty        = (float) ($item['quantity'] ?? 1);
                    $unitPrice  = (float) ($item['unit_price'] ?? 0);
                    $ivaRate    = (int)   ($item['iva_rate'] ?? 22);
                    $subtotal   = $qty * $unitPrice;
                    $ivaAmt     = $subtotal * $ivaRate / 100;
                    $items[] = [
                        'name'        => $item['name'] ?? $item['description'],
                        'description' => $item['description'] ?? $item['name'],
                        'quantity'    => $qty,
                        'unit'        => $item['unit'] ?? 'unidad',
                        'unit_price'  => $unitPrice,
                        'iva_rate'    => $ivaRate,
                        'subtotal'    => $subtotal,
                        'iva_amount'  => $ivaAmt,
                    ];
                }
            }
            if (!empty($items)) {
                $subtotal   = array_sum(array_column($items, 'subtotal'));
                $tax_amount = array_sum(array_column($items, 'iva_amount'));
                $cfe->items      = $items;
                $cfe->subtotal   = $subtotal;
                $cfe->tax_amount = $tax_amount;
                $cfe->total      = $subtotal + $tax_amount;
            }
        }

        $cfe->save();

        return redirect()->route('cfe.show', $id)
            ->with('status', ['success' => 1, 'msg' => 'CFE actualizado correctamente.']);
    }

    /**
     * Imprimir ticket/factura CFE
     */
    public function print($id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $cfe = CfeSubmission::where('business_id', $business_id)
            ->with(['transaction', 'contact', 'location'])
            ->findOrFail($id);

        $business = Business::find($business_id);
        
        // Obtener configuración CFE (donde está el RUT configurado)
        $cfe_settings_key = 'cfe_settings_' . $business_id;
        $cfe_settings_record = \App\System::where('key', $cfe_settings_key)->first();
        $cfe_settings = $cfe_settings_record ? json_decode($cfe_settings_record->value, true) : [];
        
        // Obtener ubicación del negocio (contiene teléfono, email, etc.)
        $location = $cfe->location;
        if (!$location && $cfe->location_id) {
            $location = \App\BusinessLocation::find($cfe->location_id);
        }
        if (!$location) {
            // Usar primera ubicación del negocio como fallback
            $location = \App\BusinessLocation::where('business_id', $business_id)->first();
        }
        
        // Obtener cliente
        $customer = $cfe->contact;
        if (!$customer && $cfe->contact_id) {
            $customer = Contact::find($cfe->contact_id);
        }

        // Determinar formato de impresión: ticket (80mm) o factura (A4)
        $format = request()->get('format', 'ticket');

        // Generar código QR para verificación DGI
        $qr_data = $this->generateQRData($cfe);

        // Seleccionar vista según formato
        $view = $format === 'a4' ? 'cfe.print_a4' : 'cfe.print';

        return view($view, [
            'cfe' => $cfe,
            'business' => $business,
            'location' => $location,
            'customer' => $customer,
            'cfe_settings' => $cfe_settings,
            'cfe_types' => self::CFE_TYPES,
            'qr_data' => $qr_data,
            'format' => $format,
        ]);
    }

    /**
     * Descargar XML del CFE
     */
    public function downloadXml($id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $cfe = CfeSubmission::where('business_id', $business_id)->findOrFail($id);

        $filename = 'CFE_' . $cfe->series . '_' . $cfe->number . '.xml';

        return response($cfe->xml_content, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generar CFE desde una venta existente
     */
    public function createFromTransaction($transaction_id)
    {
        if (!auth()->user()->can('sell.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        $transaction = Transaction::where('business_id', $business_id)
            ->with(['contact', 'sell_lines.product', 'location', 'business'])
            ->findOrFail($transaction_id);

        // Verificar si ya tiene CFE
        $existing_cfe = CfeSubmission::where('transaction_id', $transaction_id)->first();
        if ($existing_cfe) {
            return redirect()->route('cfe.show', $existing_cfe->id)
                ->with('status', ['success' => false, 'msg' => 'Esta venta ya tiene un CFE asociado']);
        }

        $business = $transaction->business;

        return view('cfe.create_from_transaction', [
            'transaction' => $transaction,
            'business' => $business,
            'cfe_types' => self::CFE_TYPES,
            'payment_methods' => self::PAYMENT_METHODS,
            'iva_rates' => self::IVA_RATES,
            'default_cfe_type' => $this->suggestCFEType($transaction),
        ]);
    }

    /**
     * Guardar CFE desde transacción existente
     */
    public function storeFromTransaction(Request $request, $transaction_id)
    {
        if (!auth()->user()->can('sell.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        $transaction = Transaction::where('business_id', $business_id)
            ->with(['contact', 'sell_lines.product', 'location', 'business'])
            ->findOrFail($transaction_id);

        try {
            DB::beginTransaction();

            $result = $this->cfeService->processTransaction($transaction, [
                'cfe_type' => $request->cfe_type ?? config('cfe.default_cfe_type', 111),
                'environment' => config('cfe.environment', 'testing'),
                'auto_submit' => config('cfe.auto_submit', false),
            ]);

            if ($result['success']) {
                // Crear registro en cfe_submissions
                $cfe = $this->createCfeSubmissionFromTransaction($transaction, $result);
                
                DB::commit();

                return redirect()->route('cfe.show', $cfe->id)
                    ->with('status', ['success' => true, 'msg' => 'CFE generado exitosamente']);
            } else {
                DB::rollBack();
                return back()->with('status', [
                    'success' => false, 
                    'msg' => 'Error al generar CFE: ' . implode(', ', $result['errors'] ?? [])
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generando CFE desde transacción', [
                'transaction_id' => $transaction_id,
                'message' => $e->getMessage(),
            ]);
            
            return back()->with('status', [
                'success' => false, 
                'msg' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reenviar CFE a DGI
     */
    public function resend($id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $cfe = CfeSubmission::where('business_id', $business_id)->findOrFail($id);

        $result = $this->submitToDGI($cfe);

        return response()->json($result);
    }

    /**
     * Actualizar CAE y fecha de vencimiento manualmente (para registros existentes)
     */
    public function updateCae(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        $cfe = CfeSubmission::where('business_id', $business_id)->findOrFail($id);

        $request->validate([
            'cae' => 'nullable|string|max:50',
            'cae_due_date' => 'nullable|date',
        ]);

        if ($request->filled('cae')) {
            $cfe->cae = $request->cae;
        }
        if ($request->filled('cae_due_date')) {
            $cfe->cae_due_date = Carbon::parse($request->cae_due_date);
        }
        $cfe->save();

        return response()->json([
            'success' => true,
            'msg' => 'CAE actualizado correctamente.',
            'cae_due_date' => $cfe->cae_due_date ? $cfe->cae_due_date->format('d/m/Y') : null,
        ]);
    }

    /**
     * API: Consultar estado de CFE en DGI
     */
    public function checkStatus($id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $cfe = CfeSubmission::where('business_id', $business_id)->findOrFail($id);

        // TODO: Implementar consulta real a DGI
        return response()->json([
            'success' => true,
            'status' => $cfe->status,
            'cae' => $cfe->cae,
            'cae_due_date' => $cfe->cae_due_date ? $cfe->cae_due_date->format('d/m/Y') : null,
            'track_id' => $cfe->track_id,
        ]);
    }

    /**
     * Configuración CFE del negocio
     */
    public function settings()
    {
        if (!auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business = Business::find($business_id);

        // Cargar configuración CFE desde tabla system
        $key = 'cfe_settings_' . $business_id;
        $cfe_settings_row = \App\System::where('key', $key)->first();
        $cfe_settings = $cfe_settings_row ? json_decode($cfe_settings_row->value, true) : [];

        return view('cfe.settings', [
            'business' => $business,
            'cfe_types' => self::CFE_TYPES,
            'departments' => self::DEPARTMENTS,
            'cfe_settings' => $cfe_settings,
        ]);
    }

    /**
     * Guardar configuración CFE
     */
    public function saveSettings(Request $request)
    {
        if (!auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business = Business::find($business_id);

        // Si el RUT emisor viene vacío, usar el tax_number_1 del negocio
        $emitter_rut = $request->cfe_emitter_rut;
        if (empty($emitter_rut) && !empty($business->tax_number_1)) {
            $emitter_rut = $business->tax_number_1;
        }

        // Si el RUT usuario DGI viene vacío, usar el RUT emisor
        $dgi_user_rut = $request->cfe_dgi_user_rut;
        if (empty($dgi_user_rut)) {
            $dgi_user_rut = $emitter_rut;
        }

        // Mantener contraseña existente si no se envía una nueva
        $cert_password = $request->cfe_cert_password;
        if (empty($cert_password)) {
            $key_existing = 'cfe_settings_' . $business_id;
            $existing = \App\System::where('key', $key_existing)->first();
            if ($existing) {
                $existing_settings = json_decode($existing->value, true);
                $cert_password = $existing_settings['cfe_cert_password'] ?? null;
            }
        }

        // Guardar configuración CFE en tabla system
        $cfe_settings = [
            'cfe_enabled' => $request->cfe_enabled ? true : false,
            'cfe_auto_submit' => $request->cfe_auto_submit ? true : false,
            'cfe_environment' => $request->cfe_environment ?? 'testing',
            'cfe_default_type' => $request->cfe_default_type ?? 111,
            'cfe_default_series' => $request->cfe_default_series ?? 'A',
            'cfe_emitter_rut' => $emitter_rut,
            'cfe_dgi_user_rut' => $dgi_user_rut,
            'cfe_cert_path' => $request->cfe_cert_path,
            'cfe_cert_password' => $cert_password,
        ];

        // Usar tabla system para guardar configuración por negocio
        $key = 'cfe_settings_' . $business_id;
        
        \App\System::updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($cfe_settings)]
        );

        return redirect()->back()->with('status', [
            'success' => true,
            'msg' => 'Configuración CFE guardada exitosamente'
        ]);
    }

    // ============ MÉTODOS PRIVADOS ============

    /**
     * Preparar datos para generar XML CFE
     */
    private function prepareCFEData(CfeSubmission $cfe): array
    {
        return [
            'tipo' => $cfe->cfe_type,
            'serie' => $cfe->series,
            'numero' => $cfe->number,
            'fecha' => $cfe->issue_date->format('Y-m-d'),
            'fecha_vencimiento' => $cfe->due_date->format('Y-m-d'),
            'forma_pago' => $cfe->payment_method,
            'emisor' => [
                'rut' => $cfe->emitter_rut,
                'razonSocial' => $cfe->emitter_name,
                'nombreComercial' => $cfe->emitter_name,
                'direccion' => $cfe->emitter_address,
                'ciudad' => $cfe->emitter_city,
                'departamento' => $cfe->emitter_department,
            ],
            'receptor' => [
                'tipoDoc' => $cfe->receiver_doc_type,
                'documento' => $cfe->receiver_document,
                'nombre' => $cfe->receiver_name,
                'direccion' => $cfe->receiver_address,
                'ciudad' => $cfe->receiver_city,
                'departamento' => $cfe->receiver_department,
            ],
            'items' => $cfe->items,
            'totales' => [
                'moneda' => $cfe->currency,
                'tipo_cambio' => $cfe->exchange_rate,
                'no_gravado' => 0,
                'iva_tasa_min' => 0,
                'iva_tasa_basica' => $cfe->tax_amount,
                'total' => $cfe->total,
            ],
        ];
    }

    /**
     * Enviar CFE a DGI
     */
    private function submitToDGI(CfeSubmission $cfe): array
    {
        try {
            $certificatePath = config('cfe.certificate_path');
            $certificatePassword = config('cfe.certificate_password');
            $isProduction = config('cfe.environment') === 'production';

            if (empty($certificatePath) || empty($certificatePassword)) {
                return [
                    'success' => false,
                    'errors' => ['Certificado digital no configurado'],
                ];
            }

            // Llamar al servicio DGI
            $dgiService = app(\App\Services\Cfe\DgiApiService::class);
            $signatureService = app(\App\Services\Cfe\DigitalSignatureService::class);

            // Firmar XML
            $signedResult = $signatureService->signCFE($cfe->xml_content, [
                'certificate' => file_get_contents($certificatePath),
                'password' => $certificatePassword,
            ]);

            $cfe->signed_xml = $signedResult['signedXML'];
            $cfe->save();

            // Obtener token y enviar
            $token = $dgiService->getDGIToken($certificatePath, $certificatePassword, $isProduction);
            $response = $dgiService->submitCFEToDGI($signedResult['signedXML'], $token, $isProduction);

            // Actualizar estado
            $cfe->status = $response['success'] ? 'accepted' : 'rejected';
            $cfe->cae = $response['cae'] ?? null;
            $cfe->cae_due_date = $response['cae_due_date'] ?? ($response['caeDueDate'] ?? ($response['fecha_vencimiento_cae'] ?? null));
            $cfe->track_id = $response['trackingCode'] ?? null;
            $cfe->dgi_response = $response;
            $cfe->submitted_at = Carbon::now();
            $cfe->save();

            return $response;

        } catch (\Exception $e) {
            $cfe->status = 'error';
            $cfe->dgi_response = ['error' => $e->getMessage()];
            $cfe->save();

            return [
                'success' => false,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Crear CFE submission desde transacción
     */
    private function createCfeSubmissionFromTransaction(Transaction $transaction, array $result): CfeSubmission
    {
        $business = $transaction->business;
        $contact = $transaction->contact;
        $location = $transaction->location;

        // Obtener próximo número
        $series = config('cfe.default_series', 'A');
        $last_cfe = CfeSubmission::where('business_id', $business->id)
            ->where('series', $series)
            ->orderBy('number', 'desc')
            ->first();
        $number = $last_cfe ? ($last_cfe->number + 1) : 1;

        $items = $transaction->sell_lines->map(function ($line) {
            return [
                'product_id' => $line->product_id,
                'name' => optional($line->product)->name ?? 'Producto',
                'description' => $line->sell_line_note ?? '',
                'quantity' => (float) $line->quantity,
                'unit_price' => (float) $line->unit_price_inc_tax,
                'unit' => optional($line->sub_unit)->short_name ?? 'unidad',
                'iva_rate' => 22,
                'line_total' => (float) $line->quantity * (float) $line->unit_price_inc_tax,
            ];
        })->toArray();

        $cfe = new CfeSubmission();
        $cfe->business_id = $business->id;
        $cfe->location_id = $transaction->location_id;
        $cfe->transaction_id = $transaction->id;
        $cfe->contact_id = $contact ? $contact->id : null;
        $cfe->user_id = Auth::id();
        $cfe->cfe_type = config('cfe.default_cfe_type', 111);
        $cfe->series = $series;
        $cfe->number = $number;
        $cfe->issue_date = Carbon::parse($transaction->transaction_date);
        $cfe->due_date = Carbon::parse($transaction->transaction_date);
        $cfe->payment_method = $transaction->payment_status === 'paid' ? 1 : 2;
        $cfe->currency = optional($business->currency)->code ?? 'UYU';
        $cfe->exchange_rate = $transaction->exchange_rate ?? 1;
        $cfe->subtotal = $transaction->total_before_tax ?? $transaction->final_total;
        $cfe->tax_amount = $transaction->tax_amount ?? 0;
        $cfe->total = $transaction->final_total;
        $cfe->items = $items;
        $cfe->status = $result['success'] ? 'submitted' : 'error';
        
        // Datos emisor
        $cfe->emitter_rut = config('cfe.emitter_rut') ?? $business->tax_number_1;
        $cfe->emitter_name = $business->name;
        $cfe->emitter_address = $location ? $location->landmark : '';
        $cfe->emitter_city = $location ? $location->city : 'Montevideo';
        $cfe->emitter_department = $location ? $location->state : 'Montevideo';
        
        // Datos receptor
        $cfe->receiver_doc_type = $this->getDocumentType($contact);
        $cfe->receiver_document = $contact ? ($contact->tax_number ?? $contact->mobile ?? '') : '';
        $cfe->receiver_name = $contact ? ($contact->name ?? $contact->first_name . ' ' . $contact->last_name) : 'Consumidor Final';
        $cfe->receiver_address = $contact ? ($contact->address_line_1 ?? '') : '';
        $cfe->receiver_city = $contact ? ($contact->city ?? 'Montevideo') : 'Montevideo';
        $cfe->receiver_department = $contact ? ($contact->state ?? 'Montevideo') : 'Montevideo';

        $cfe->xml_content = $result['xmlContent'] ?? '';
        $cfe->signed_xml = $result['signedXml'] ?? '';
        $cfe->cae = $result['cae'] ?? null;
        $cfe->cae_due_date = $result['cae_due_date'] ?? ($result['caeDueDate'] ?? ($result['fecha_vencimiento_cae'] ?? null));
        $cfe->track_id = $result['trackingCode'] ?? null;
        $cfe->dgi_response = $result;

        $cfe->save();

        // Actualizar transacción
        $transaction->cfe_status = $cfe->status;
        $transaction->cfe_track_id = $cfe->track_id;
        $transaction->save();

        return $cfe;
    }

    /**
     * Determinar tipo de documento del cliente
     */
    private function getDocumentType(?Contact $contact): string
    {
        if (!$contact || empty($contact->tax_number)) {
            return 'CI';
        }

        $doc = preg_replace('/[^0-9]/', '', $contact->tax_number);
        
        // RUT uruguayo tiene 12 dígitos
        if (strlen($doc) === 12) {
            return 'RUT';
        }

        // CI uruguaya tiene 7-8 dígitos
        if (strlen($doc) >= 7 && strlen($doc) <= 8) {
            return 'CI';
        }

        return 'Otro';
    }

    /**
     * Sugerir tipo de CFE según la transacción
     */
    private function suggestCFEType(Transaction $transaction): int
    {
        $contact = $transaction->contact;

        // Si el cliente tiene RUT válido, sugerir e-Factura
        if ($contact && !empty($contact->tax_number)) {
            $doc = preg_replace('/[^0-9]/', '', $contact->tax_number);
            if (strlen($doc) === 12) {
                return 111; // e-Factura
            }
        }

        // Por defecto, e-Ticket para consumidor final
        return 101; // e-Ticket
    }

    /**
     * Generar datos para código QR de verificación
     */
    private function generateQRData(CfeSubmission $cfe): string
    {
        // Formato DGI para verificación
        // RUT|TipoCFE|Serie|Numero|Total|CAE|Fecha
        $data = implode('|', [
            $cfe->emitter_rut,
            $cfe->cfe_type,
            $cfe->series,
            $cfe->number,
            number_format($cfe->total, 2, '.', ''),
            $cfe->cae ?? '0',
            $cfe->issue_date->format('Ymd'),
        ]);

        // URL de verificación DGI
        $verifyUrl = config('cfe.environment') === 'production'
            ? 'https://www.efactura.dgi.gub.uy/consultaQR/cfe?'
            : 'https://efactura.testing.dgi.gub.uy/consultaQR/cfe?';

        return $verifyUrl . base64_encode($data);
    }
}
