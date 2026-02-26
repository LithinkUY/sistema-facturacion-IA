<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ===== 1. Órdenes de Pedido =====
        Schema::create('order_pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('contact_id')->nullable()->comment('Proveedor o cliente');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('created_by');

            $table->string('order_number', 50)->unique();
            $table->enum('type', ['purchase', 'sale'])->default('purchase')->comment('Tipo de orden');
            $table->enum('status', [
                'draft',        // Borrador
                'pending',      // Pendiente de aprobación
                'approved',     // Aprobada
                'in_progress',  // En proceso
                'partial',      // Parcialmente completada
                'completed',    // Completada
                'cancelled',    // Cancelada
            ])->default('draft');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            $table->decimal('subtotal', 22, 4)->default(0);
            $table->decimal('tax_amount', 22, 4)->default(0);
            $table->decimal('discount_amount', 22, 4)->default(0);
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('total', 22, 4)->default(0);

            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
            $table->index('contact_id');
            $table->index('status');
            $table->index('order_date');
        });

        // ===== 2. Líneas de la Orden =====
        Schema::create('order_pedido_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_pedido_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();

            $table->string('product_name', 255)->comment('Nombre del producto (puede ser manual)');
            $table->string('sku', 100)->nullable();
            $table->decimal('quantity', 22, 4)->default(1);
            $table->decimal('quantity_received', 22, 4)->default(0);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 22, 4)->default(0);
            $table->decimal('tax_percent', 8, 4)->default(0);
            $table->decimal('tax_amount', 22, 4)->default(0);
            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->decimal('discount_amount', 22, 4)->default(0);
            $table->decimal('line_total', 22, 4)->default(0);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('order_pedido_id')
                  ->references('id')->on('order_pedidos')
                  ->onDelete('cascade');

            $table->index('product_id');
        });

        // ===== 3. Tareas vinculadas a Órdenes =====
        Schema::create('order_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_pedido_id');
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('assigned_to')->nullable();

            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('status', [
                'pending',      // Pendiente
                'in_progress',  // En progreso
                'completed',    // Completada
                'cancelled',    // Cancelada
                'on_hold',      // En espera
            ])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();

            $table->integer('progress')->default(0)->comment('Porcentaje 0-100');
            $table->boolean('is_milestone')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_pedido_id')
                  ->references('id')->on('order_pedidos')
                  ->onDelete('cascade');

            $table->index('business_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('due_date');
        });

        // ===== 4. Checklist items de las tareas =====
        Schema::create('order_task_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_task_id');
            $table->string('description', 500);
            $table->boolean('is_completed')->default(false);
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('order_task_id')
                  ->references('id')->on('order_tasks')
                  ->onDelete('cascade');
        });

        // ===== 5. Comentarios / Actividad =====
        Schema::create('order_comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable'); // order_pedido or order_task
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->enum('type', ['comment', 'status_change', 'system'])->default('comment');
            $table->timestamps();

            $table->index('user_id');
        });

        // ===== 6. Archivos adjuntos =====
        Schema::create('order_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // order_pedido or order_task
            $table->unsignedBigInteger('uploaded_by');
            $table->string('filename', 255);
            $table->string('original_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('path', 500);
            $table->timestamps();

            $table->index('uploaded_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_attachments');
        Schema::dropIfExists('order_comments');
        Schema::dropIfExists('order_task_checklists');
        Schema::dropIfExists('order_tasks');
        Schema::dropIfExists('order_pedido_lines');
        Schema::dropIfExists('order_pedidos');
    }
};
