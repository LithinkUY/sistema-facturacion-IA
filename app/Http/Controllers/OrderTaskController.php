<?php

namespace App\Http\Controllers;

use App\OrderTask;
use App\OrderTaskChecklist;
use App\OrderPedido;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderTaskController extends Controller
{
    /**
     * Guardar nueva tarea (AJAX)
     */
    public function store(Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            // Verificar que la orden pertenece al negocio
            $order = OrderPedido::forBusiness($business_id)->findOrFail($request->order_pedido_id);

            $task = OrderTask::create([
                'order_pedido_id' => $order->id,
                'business_id' => $business_id,
                'created_by' => auth()->id(),
                'assigned_to' => $request->assigned_to,
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status ?? 'pending',
                'priority' => $request->priority ?? 'medium',
                'due_date' => $request->due_date,
                'start_date' => $request->start_date ?? now()->toDateString(),
                'is_milestone' => $request->has('is_milestone'),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            // Agregar checklist items si se proporcionan
            if ($request->has('checklist') && is_array($request->checklist)) {
                foreach ($request->checklist as $index => $item) {
                    if (!empty(trim($item))) {
                        $task->checklists()->create([
                            'description' => trim($item),
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            $order->addSystemComment("Nueva tarea creada: \"{$task->title}\"");

            $task->load(['assignedTo', 'checklists']);

            return response()->json([
                'success' => true,
                'msg' => 'Tarea creada exitosamente',
                'task' => $this->formatTaskForResponse($task),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Actualizar tarea (AJAX)
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $task = OrderTask::forBusiness($business_id)->findOrFail($id);
            $oldStatus = $task->status;

            $task->update([
                'assigned_to' => $request->assigned_to,
                'title' => $request->title ?? $task->title,
                'description' => $request->description,
                'status' => $request->status ?? $task->status,
                'priority' => $request->priority ?? $task->priority,
                'due_date' => $request->due_date,
                'start_date' => $request->start_date,
                'is_milestone' => $request->has('is_milestone'),
                'progress' => $request->progress ?? $task->progress,
            ]);

            // Si se completÃ³
            if ($task->status === 'completed' && $oldStatus !== 'completed') {
                $task->markAsCompleted();
            }

            // Si se proporcionÃ³ status_change log
            if ($oldStatus !== $task->status) {
                $statusLabel = $task->status_label;
                $task->orderPedido->addSystemComment(
                    "Tarea \"{$task->title}\": estado cambiado a {$statusLabel['text']}"
                );
            }

            $task->load(['assignedTo', 'checklists']);

            return response()->json([
                'success' => true,
                'msg' => 'Tarea actualizada',
                'task' => $this->formatTaskForResponse($task),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Cambiar estado de tarea (AJAX rÃ¡pido)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $task = OrderTask::forBusiness($business_id)->findOrFail($id);

            if ($request->status === 'completed') {
                $task->markAsCompleted();
            } else {
                $oldStatus = $task->status;
                $task->status = $request->status;
                if ($request->status === 'in_progress' && !$task->start_date) {
                    $task->start_date = now()->toDateString();
                }
                $task->save();

                if ($oldStatus !== $task->status) {
                    $statusLabel = $task->status_label;
                    $task->orderPedido->addSystemComment(
                        "Tarea \"{$task->title}\": {$statusLabel['text']}"
                    );
                }
            }

            return response()->json([
                'success' => true,
                'msg' => 'Estado actualizado',
                'status_label' => $task->status_label,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar tarea (AJAX)
     */
    public function destroy($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $task = OrderTask::forBusiness($business_id)->findOrFail($id);

            $taskTitle = $task->title;
            $order = $task->orderPedido;

            $task->delete();

            $order->addSystemComment("Tarea eliminada: \"{$taskTitle}\"");

            return response()->json([
                'success' => true,
                'msg' => 'Tarea eliminada',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Agregar item al checklist (AJAX)
     */
    public function addChecklist(Request $request, $task_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $task = OrderTask::forBusiness($business_id)->findOrFail($task_id);

            $maxOrder = $task->checklists()->max('sort_order') ?? 0;

            $item = $task->checklists()->create([
                'description' => $request->description,
                'sort_order' => $maxOrder + 1,
            ]);

            return response()->json([
                'success' => true,
                'msg' => 'Item agregado',
                'item' => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'is_completed' => false,
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
     * Toggle checklist item (AJAX)
     */
    public function toggleChecklist($id)
    {
        try {
            $item = OrderTaskChecklist::findOrFail($id);
            $item->toggleComplete();

            return response()->json([
                'success' => true,
                'is_completed' => $item->is_completed,
                'task_progress' => $item->task->checklistProgress,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar item del checklist (AJAX)
     */
    public function deleteChecklist($id)
    {
        try {
            $item = OrderTaskChecklist::findOrFail($id);
            $task = $item->task;
            $item->delete();
            $task->updateProgress();

            return response()->json([
                'success' => true,
                'msg' => 'Item eliminado',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Agregar comentario a tarea (AJAX)
     */
    public function addComment(Request $request, $task_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $task = OrderTask::forBusiness($business_id)->findOrFail($task_id);

            $comment = $task->comments()->create([
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
     * Dashboard de tareas del usuario actual
     */
    public function myTasks(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = auth()->id();

        $tasks = OrderTask::forBusiness($business_id)
            ->where(function ($q) use ($user_id) {
                $q->where('assigned_to', $user_id)
                  ->orWhere('created_by', $user_id);
            })
            ->with(['orderPedido', 'assignedTo', 'checklists'])
            ->whereNotIn('status', ['cancelled'])
            ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'on_hold', 'completed')")
            ->orderBy('due_date')
            ->get();

        $overdue = $tasks->filter(fn($t) => $t->is_overdue);
        $today = $tasks->filter(fn($t) => $t->due_date && $t->due_date->isToday());
        $upcoming = $tasks->filter(fn($t) => $t->due_date && $t->due_date->isFuture() && $t->due_date->diffInDays(now()) <= 7);

        return view('order_pedidos.my_tasks', compact('tasks', 'overdue', 'today', 'upcoming'));
    }

    private function formatTaskForResponse($task)
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'status_label' => $task->status_label,
            'priority' => $task->priority,
            'priority_label' => $task->priority_label,
            'due_date' => $task->due_date ? $task->due_date->format('d/m/Y') : null,
            'start_date' => $task->start_date ? $task->start_date->format('d/m/Y') : null,
            'progress' => $task->progress,
            'is_overdue' => $task->is_overdue,
            'assigned_to' => $task->assignedTo ? [
                'id' => $task->assignedTo->id,
                'name' => $task->assignedTo->first_name . ' ' . ($task->assignedTo->last_name ?? ''),
            ] : null,
            'checklists' => $task->checklists->map(fn($c) => [
                'id' => $c->id,
                'description' => $c->description,
                'is_completed' => $c->is_completed,
            ]),
            'checklist_progress' => $task->checklistProgress,
            'is_milestone' => $task->is_milestone,
        ];
    }
}
