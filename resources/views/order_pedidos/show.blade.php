@extends('layouts.app')
@section('title', 'Orden ' . $order->order_number)

@section('css')
<style>
.remito-header{background:linear-gradient(135deg,#1a237e,#283593);color:#fff;padding:25px 30px;border-radius:10px 10px 0 0}
.remito-header h2{margin:0;font-size:1.8em;font-weight:700}
.remito-body{background:#fff;border:1px solid #e0e0e0;border-top:none;border-radius:0 0 10px 10px}
.remito-section{padding:20px 30px;border-bottom:1px solid #f0f0f0}
.remito-section:last-child{border-bottom:none}
.remito-section-title{font-size:.85em;text-transform:uppercase;letter-spacing:1px;color:#666;font-weight:700;margin-bottom:12px}
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px}
.info-item label{display:block;font-size:.78em;text-transform:uppercase;letter-spacing:.5px;color:#999;margin-bottom:2px}
.info-item span{font-size:1em;font-weight:600;color:#333}
.items-table{width:100%;border-collapse:collapse}
.items-table thead th{background:#f8f9fa;padding:10px 12px;font-size:.82em;text-transform:uppercase;letter-spacing:.5px;color:#666;border-bottom:2px solid #dee2e6}
.items-table tbody td{padding:12px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
.items-table tbody tr:hover{background:#f8f9ff}
.items-table .product-name{font-weight:600;color:#1a237e}
.items-table .product-desc{font-size:.85em;color:#999;margin-top:2px}
.totals-box{background:#f8f9fa;border-radius:8px;padding:15px 20px}
.totals-row{display:flex;justify-content:space-between;padding:6px 0}
.totals-row.grand-total{border-top:2px solid #1a237e;padding-top:12px;margin-top:8px;font-size:1.3em}
.totals-row.grand-total .amount{color:#1a237e;font-weight:800}
.status-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:20px;font-size:.85em;font-weight:600}
.status-pill.draft{background:#f5f5f5;color:#757575}
.status-pill.pending{background:#fff3e0;color:#e65100}
.status-pill.approved{background:#e8f5e9;color:#2e7d32}
.status-pill.in_progress{background:#e3f2fd;color:#1565c0}
.status-pill.partial{background:#fce4ec;color:#c62828}
.status-pill.completed{background:#e8f5e9;color:#1b5e20}
.status-pill.cancelled{background:#fafafa;color:#9e9e9e;text-decoration:line-through}
.priority-pill{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:12px;font-size:.8em;font-weight:600}
.priority-pill.low{background:#e8f5e9;color:#2e7d32}
.priority-pill.medium{background:#fff8e1;color:#f57f17}
.priority-pill.high{background:#fff3e0;color:#e65100}
.priority-pill.urgent{background:#ffebee;color:#c62828}
.progress-bar-custom{height:8px;background:#e0e0e0;border-radius:4px;overflow:hidden}
.progress-bar-custom .fill{height:100%;border-radius:4px;transition:width .3s}
.progress-bar-custom .fill.green{background:linear-gradient(90deg,#43a047,#66bb6a)}
.progress-bar-custom .fill.blue{background:linear-gradient(90deg,#1e88e5,#42a5f5)}
.progress-bar-custom .fill.orange{background:linear-gradient(90deg,#fb8c00,#ffa726)}
.action-card{background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-bottom:15px}
.action-card h4{font-size:.9em;text-transform:uppercase;letter-spacing:1px;color:#666;margin:0 0 15px}
.tabs-modern .nav-tabs{border-bottom:2px solid #e0e0e0}
.tabs-modern .nav-tabs>li>a{border:none;border-bottom:3px solid transparent;color:#666;font-weight:600;padding:12px 20px;margin-bottom:-2px}
.tabs-modern .nav-tabs>li.active>a,.tabs-modern .nav-tabs>li.active>a:focus,.tabs-modern .nav-tabs>li.active>a:hover{border-bottom-color:#1a237e;color:#1a237e;background:transparent}
.tabs-modern .nav-tabs>li>a:hover{background:#f8f9fa;border-bottom-color:#ccc}
.tabs-modern .tab-content{padding:20px 0}
.task-card{border:1px solid #e5e7eb;border-radius:10px;padding:15px;margin-bottom:12px;transition:all .2s;background:#fff}
.task-card:hover{box-shadow:0 4px 15px rgba(0,0,0,.08);transform:translateY(-1px)}
.task-card.completed{opacity:.7;background:#fafafa}
.task-card.overdue{border-left:3px solid #c62828}
.checklist-item{display:flex;align-items:center;gap:8px;padding:4px 0;font-size:.9em}
.checklist-item.completed label{text-decoration:line-through;color:#999}
.comment-item{padding:12px 0;border-bottom:1px solid #f0f0f0}
.comment-item:last-child{border-bottom:none}
.comment-avatar{width:36px;height:36px;border-radius:50%;background:#1a237e;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85em}
</style>
@endsection

@section('content')
<section class="content" style="padding:15px">
    <div class="row">
        <div class="col-md-9">
            <div class="remito-header">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                    <div>
                        <div style="font-size:.85em;opacity:.8;margin-bottom:4px">REMITO / ORDEN DE PEDIDO</div>
                        <h2>{{ $order->order_number }}</h2>
                    </div>
                    <div style="text-align:right">
                        <span class="status-pill {{ $order->status }}">
                            @php
                                $statusIcons = ['draft'=>'pencil-alt','pending'=>'clock','approved'=>'thumbs-up','in_progress'=>'cogs','partial'=>'tasks','completed'=>'check-double','cancelled'=>'times'];
                            @endphp
                            <i class="fas fa-{{ $statusIcons[$order->status] ?? 'info-circle' }}"></i>
                            {{ $order->status_label['text'] }}
                        </span>
                        <div style="font-size:.85em;opacity:.8;margin-top:6px">
                            Creado: {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="remito-body">
                <div class="remito-section">
                    <div class="remito-section-title"><i class="fas fa-info-circle"></i> Informacion General</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Cliente</label>
                            <span>{{ $order->contact->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Fecha Orden</label>
                            <span>{{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label>Entrega Esperada</label>
                            <span style="{{ $order->expected_delivery_date && $order->expected_delivery_date->isPast() && $order->status != 'completed' ? 'color:#c62828;font-weight:700' : '' }}">
                                {{ $order->expected_delivery_date ? $order->expected_delivery_date->format('d/m/Y') : 'No definida' }}
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Prioridad</label>
                            <span class="priority-pill {{ $order->priority }}">
                                @if($order->priority == 'urgent') 🔴
                                @elseif($order->priority == 'high') 🟠
                                @elseif($order->priority == 'medium') 🟡
                                @else 🟢
                                @endif
                                {{ $order->priority_label['text'] }}
                            </span>
                        </div>
                        @if($order->approved_at)
                        <div class="info-item">
                            <label>Aprobado</label>
                            <span>{{ $order->approved_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($order->actual_delivery_date)
                        <div class="info-item">
                            <label>Entrega Real</label>
                            <span>{{ $order->actual_delivery_date->format('d/m/Y') }}</span>
                        </div>
                        @endif
                    </div>
                    @if($order->notes)
                    <div style="margin-top:15px;padding:12px;background:#f8f9fa;border-radius:6px;border-left:3px solid #1a237e">
                        <strong style="font-size:.82em;text-transform:uppercase;color:#666"><i class="fas fa-sticky-note"></i> Notas:</strong>
                        <p style="margin:6px 0 0;color:#555">{{ $order->notes }}</p>
                    </div>
                    @endif
                </div>
                <div class="remito-section">
                    <div class="remito-section-title"><i class="fas fa-boxes"></i> Items del Pedido</div>
                    <div style="overflow-x:auto">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Producto</th>
                                    <th style="text-align:center;width:100px">Cantidad</th>
                                    <th style="text-align:right;width:120px">Precio Unit.</th>
                                    <th style="text-align:center;width:120px">Progreso</th>
                                    <th style="text-align:right;width:120px">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->items as $i => $item)
                                <tr>
                                    <td style="color:#999">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="product-name">{{ $item->product->name ?? $item->product_name ?? 'Producto' }}</div>
                                        @if($item->variation)
                                        <div class="product-desc">{{ $item->variation->name ?? '' }}</div>
                                        @endif
                                        @if($item->notes)
                                        <div class="product-desc"><i class="fas fa-comment-dots"></i> {{ $item->notes }}</div>
                                        @endif
                                    </td>
                                    <td style="text-align:center;font-weight:600">
                                        {{ number_format($item->quantity, 2) }}
                                        @if($item->delivered_quantity > 0)
                                        <div style="font-size:.8em;color:#43a047">✓ {{ number_format($item->delivered_quantity, 2) }} entregado</div>
                                        @endif
                                    </td>
                                    <td style="text-align:right">$ {{ number_format($item->unit_price, 2) }}</td>
                                    <td style="text-align:center">
                                        @php
                                            $progress = $item->quantity > 0 ? round(($item->delivered_quantity / $item->quantity) * 100) : 0;
                                            $progressClass = $progress >= 100 ? 'green' : ($progress >= 50 ? 'blue' : 'orange');
                                        @endphp
                                        <div class="progress-bar-custom">
                                            <div class="fill {{ $progressClass }}" style="width:{{ min($progress, 100) }}%"></div>
                                        </div>
                                        <small style="color:#999">{{ $progress }}%</small>
                                    </td>
                                    <td style="text-align:right;font-weight:600">$ {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:30px;color:#999">
                                        <i class="fas fa-box-open" style="font-size:2em;display:block;margin-bottom:8px"></i>
                                        No hay items en este pedido
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($order->items->count() > 0)
                    <div style="display:flex;justify-content:flex-end;margin-top:15px">
                        <div class="totals-box" style="min-width:280px">
                            @php
                                $subtotal = $order->items->sum(function($item) { return $item->quantity * $item->unit_price; });
                                $tax = $order->tax_amount ?? 0;
                                $discount = $order->discount_amount ?? 0;
                                $total = $subtotal + $tax - $discount;
                            @endphp
                            <div class="totals-row">
                                <span>Subtotal:</span>
                                <span class="amount">$ {{ number_format($subtotal, 2) }}</span>
                            </div>
                            @if($tax > 0)
                            <div class="totals-row">
                                <span>Impuestos:</span>
                                <span class="amount">$ {{ number_format($tax, 2) }}</span>
                            </div>
                            @endif
                            @if($discount > 0)
                            <div class="totals-row">
                                <span>Descuento:</span>
                                <span class="amount" style="color:#c62828">-$ {{ number_format($discount, 2) }}</span>
                            </div>
                            @endif
                            <div class="totals-row grand-total">
                                <span>TOTAL:</span>
                                <span class="amount">$ {{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="remito-section">
                    <div class="tabs-modern">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#tab-tasks" role="tab" data-toggle="tab">
                                    <i class="fas fa-tasks"></i> Tareas
                                    <span class="badge" style="background:#1a237e;color:#fff;margin-left:4px">{{ $order->tasks->count() }}</span>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#tab-comments" role="tab" data-toggle="tab">
                                    <i class="fas fa-comments"></i> Comentarios
                                    <span class="badge" style="background:#666;color:#fff;margin-left:4px">{{ $order->comments->count() }}</span>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="tab-tasks">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px">
                                    <h4 style="margin:0;color:#333"><i class="fas fa-tasks"></i> Tareas del Pedido</h4>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addTaskModal" style="border-radius:20px">
                                        <i class="fas fa-plus"></i> Nueva Tarea
                                    </button>
                                </div>
                                <div id="tasks-container">
                                    @forelse($order->tasks->sortBy('sort_order') as $task)
                                        @include('order_pedidos.partials.task_card', ['task' => $task])
                                    @empty
                                        <div style="text-align:center;padding:40px;color:#999">
                                            <i class="fas fa-clipboard-check" style="font-size:3em;display:block;margin-bottom:10px;opacity:.3"></i>
                                            <p>No hay tareas creadas</p>
                                            <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#addTaskModal" style="border-radius:20px">
                                                <i class="fas fa-plus"></i> Crear primera tarea
                                            </button>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab-comments">
                                <div style="margin-bottom:20px">
                                    <div style="display:flex;gap:10px">
                                        <div class="comment-avatar">{{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}</div>
                                        <div style="flex:1">
                                            <textarea id="new-comment" class="form-control" rows="2" placeholder="Escribir un comentario..." style="border-radius:8px"></textarea>
                                            <button class="btn btn-sm btn-primary" id="btn-add-comment" style="margin-top:8px;border-radius:20px">
                                                <i class="fas fa-paper-plane"></i> Comentar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div id="comments-container">
                                    @forelse($order->comments->sortByDesc('created_at') as $comment)
                                    <div class="comment-item">
                                        <div style="display:flex;gap:10px">
                                            <div class="comment-avatar">{{ strtoupper(substr($comment->user->first_name ?? 'U', 0, 1)) }}</div>
                                            <div style="flex:1">
                                                <div style="display:flex;justify-content:space-between;align-items:center">
                                                    <strong style="font-size:.9em">{{ $comment->user->first_name ?? '' }} {{ $comment->user->last_name ?? '' }}</strong>
                                                    <small style="color:#999">{{ $comment->created_at->diffForHumans() }}</small>
                                                </div>
                                                <p style="margin:4px 0 0;color:#555">{{ $comment->comment }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div style="text-align:center;padding:30px;color:#999">
                                        <i class="fas fa-comment-slash" style="font-size:2em;opacity:.3;display:block;margin-bottom:8px"></i>
                                        No hay comentarios
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="action-card">
                <h4><i class="fas fa-chart-pie"></i> Progreso</h4>
                @php
                    $taskProgress = $order->task_progress;
                @endphp
                <div style="text-align:center;margin-bottom:15px">
                    <div style="font-size:2.5em;font-weight:800;color:#1a237e">{{ $taskProgress['percent'] }}%</div>
                    <small style="color:#999">{{ $taskProgress['completed'] }} de {{ $taskProgress['total'] }} tareas</small>
                </div>
                <div class="progress-bar-custom" style="height:10px">
                    <div class="fill {{ $taskProgress['percent'] >= 100 ? 'green' : ($taskProgress['percent'] >= 50 ? 'blue' : 'orange') }}" style="width:{{ $taskProgress['percent'] }}%"></div>
                </div>
            </div>
            <div class="action-card">
                <h4><i class="fas fa-info-circle"></i> Detalles</h4>
                <div style="font-size:.9em">
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0">
                        <span style="color:#999">Ubicacion:</span>
                        <span style="font-weight:600">{{ $order->location->name ?? 'N/A' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0">
                        <span style="color:#999">Creado por:</span>
                        <span style="font-weight:600">{{ $order->createdBy->first_name ?? '' }} {{ $order->createdBy->last_name ?? '' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0">
                        <span style="color:#999">Items:</span>
                        <span style="font-weight:600">{{ $order->items->count() }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:8px 0">
                        <span style="color:#999">Tareas:</span>
                        <span style="font-weight:600">{{ $order->tasks->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="action-card">
                <h4><i class="fas fa-cog"></i> Acciones</h4>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <a href="{{ url('/order-pedidos/' . $order->id . '/edit') }}" class="btn btn-info btn-block" style="border-radius:8px">
                        <i class="fas fa-edit"></i> Editar Orden
                    </a>
                    @if($order->status == 'pending')
                    <button class="btn btn-success btn-block status-change-btn" data-status="approved" style="border-radius:8px">
                        <i class="fas fa-thumbs-up"></i> Aprobar
                    </button>
                    @endif
                    @if(in_array($order->status, ['approved', 'in_progress']))
                    <button class="btn btn-primary btn-block status-change-btn" data-status="in_progress" style="border-radius:8px">
                        <i class="fas fa-cogs"></i> En Proceso
                    </button>
                    <button class="btn btn-success btn-block status-change-btn" data-status="completed" style="border-radius:8px">
                        <i class="fas fa-check-double"></i> Completar
                    </button>
                    @endif
                    @if($order->status != 'cancelled')
                    <button class="btn btn-danger btn-block status-change-btn" data-status="cancelled" style="border-radius:8px">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    @endif
                    <a href="{{ url('/order-pedidos') }}" class="btn btn-default btn-block" style="border-radius:8px">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:10px">
            <div class="modal-header" style="background:#1a237e;color:#fff;border-radius:10px 10px 0 0">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fas fa-plus-circle"></i> Nueva Tarea</h4>
            </div>
            <div class="modal-body">
                <form id="addTaskForm">
                    <div class="form-group">
                        <label>Titulo *</label>
                        <input type="text" name="title" class="form-control" required style="border-radius:6px">
                    </div>
                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea name="description" class="form-control" rows="3" style="border-radius:6px"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prioridad</label>
                                <select name="priority" class="form-control" style="border-radius:6px">
                                    <option value="low">Baja</option>
                                    <option value="medium" selected>Media</option>
                                    <option value="high">Alta</option>
                                    <option value="urgent">Urgente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Asignar a</label>
                                <select name="assigned_to" class="form-control" style="border-radius:6px">
                                    <option value="">Sin asignar</option>
                                    @foreach(\App\User::where('business_id', $order->business_id)->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Inicio</label>
                                <input type="date" name="start_date" class="form-control" style="border-radius:6px">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Limite</label>
                                <input type="date" name="due_date" class="form-control" style="border-radius:6px">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius:6px">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-task" style="border-radius:6px"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:10px">
            <div class="modal-header" style="background:#1a237e;color:#fff;border-radius:10px 10px 0 0">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fas fa-edit"></i> Editar Tarea</h4>
            </div>
            <div class="modal-body">
                <form id="editTaskForm">
                    <input type="hidden" name="task_id" id="edit_task_id">
                    <div class="form-group">
                        <label>Titulo *</label>
                        <input type="text" name="title" id="edit_task_title" class="form-control" required style="border-radius:6px">
                    </div>
                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea name="description" id="edit_task_description" class="form-control" rows="3" style="border-radius:6px"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="status" id="edit_task_status" class="form-control" style="border-radius:6px">
                                    <option value="pending">Pendiente</option>
                                    <option value="in_progress">En Progreso</option>
                                    <option value="completed">Completada</option>
                                    <option value="on_hold">En Espera</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prioridad</label>
                                <select name="priority" id="edit_task_priority" class="form-control" style="border-radius:6px">
                                    <option value="low">Baja</option>
                                    <option value="medium">Media</option>
                                    <option value="high">Alta</option>
                                    <option value="urgent">Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Asignar a</label>
                                <select name="assigned_to" id="edit_task_assigned" class="form-control" style="border-radius:6px">
                                    <option value="">Sin asignar</option>
                                    @foreach(\App\User::where('business_id', $order->business_id)->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Limite</label>
                                <input type="date" name="due_date" id="edit_task_due_date" class="form-control" style="border-radius:6px">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius:6px">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-update-task" style="border-radius:6px"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function(){
    var orderId = {{ $order->id }};
    var baseUrl = '{{ url("/") }}';

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
    });

    // Save new task
    $('#btn-save-task').click(function(){
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        $.ajax({
            url: baseUrl + '/order-tasks',
            method: 'POST',
            data: $('#addTaskForm').serialize() + '&order_pedido_id=' + orderId,
            success: function(res){
                if(res.success){
                    toastr.success(res.msg || 'Tarea creada');
                    location.reload();
                }
            },
            error: function(xhr){ toastr.error('Error al crear tarea'); },
            complete: function(){ btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar'); }
        });
    });

    // Edit task button
    $(document).on('click', '.edit-task-btn', function(e){
        e.preventDefault();
        var task = $(this).data('task');
        $('#edit_task_id').val(task.id);
        $('#edit_task_title').val(task.title);
        $('#edit_task_description').val(task.description);
        $('#edit_task_status').val(task.status);
        $('#edit_task_priority').val(task.priority);
        $('#edit_task_assigned').val(task.assigned_to);
        $('#edit_task_due_date').val(task.due_date);
        $('#editTaskModal').modal('show');
    });

    // Update task
    $('#btn-update-task').click(function(){
        var btn = $(this);
        var taskId = $('#edit_task_id').val();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        $.ajax({
            url: baseUrl + '/order-tasks/' + taskId,
            method: 'PUT',
            data: $('#editTaskForm').serialize(),
            success: function(res){
                if(res.success){
                    toastr.success(res.msg || 'Tarea actualizada');
                    location.reload();
                }
            },
            error: function(xhr){ toastr.error('Error al actualizar'); },
            complete: function(){ btn.prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar'); }
        });
    });

    // Task status change
    $(document).on('click', '.task-status-btn', function(e){
        e.preventDefault();
        var taskId = $(this).data('task-id');
        var status = $(this).data('status');
        $.ajax({
            url: baseUrl + '/order-tasks/' + taskId + '/status',
            method: 'POST',
            data: {status: status},
            success: function(res){
                if(res.success){ location.reload(); }
            },
            error: function(){ toastr.error('Error al cambiar estado'); }
        });
    });

    // Delete task
    $(document).on('click', '.delete-task-btn', function(e){
        e.preventDefault();
        var taskId = $(this).data('task-id');
        if(!confirm('Eliminar esta tarea?')) return;
        $.ajax({
            url: baseUrl + '/order-tasks/' + taskId,
            method: 'DELETE',
            success: function(res){
                if(res.success){
                    toastr.success('Tarea eliminada');
                    $('#task_' + taskId).fadeOut(300, function(){ $(this).remove(); });
                }
            },
            error: function(){ toastr.error('Error al eliminar'); }
        });
    });

    // Add comment
    $('#btn-add-comment').click(function(){
        var comment = $('#new-comment').val().trim();
        if(!comment){ toastr.warning('Escribe un comentario'); return; }
        var btn = $(this);
        btn.prop('disabled', true);
        $.ajax({
            url: baseUrl + '/order-pedidos/' + orderId + '/comments',
            method: 'POST',
            data: {comment: comment},
            success: function(res){
                if(res.success){
                    toastr.success('Comentario agregado');
                    location.reload();
                }
            },
            error: function(){ toastr.error('Error al comentar'); },
            complete: function(){ btn.prop('disabled', false); }
        });
    });

    // Checklist toggle
    $(document).on('change', '.checklist-toggle', function(){
        var id = $(this).data('id');
        var checked = $(this).is(':checked');
        $.ajax({
            url: baseUrl + '/order-tasks/checklist/' + id + '/toggle',
            method: 'POST',
            data: {is_completed: checked ? 1 : 0},
            success: function(res){
                if(res.success){ location.reload(); }
            }
        });
    });

    // Order status change
    $('.status-change-btn').click(function(){
        var status = $(this).data('status');
        if(!confirm('Cambiar estado a ' + status + '?')) return;
        $.ajax({
            url: baseUrl + '/order-pedidos/' + orderId + '/status',
            method: 'POST',
            data: {status: status},
            success: function(res){
                if(res.success){ location.reload(); }
            },
            error: function(){ toastr.error('Error al cambiar estado'); }
        });
    });
});
</script>
@endsection