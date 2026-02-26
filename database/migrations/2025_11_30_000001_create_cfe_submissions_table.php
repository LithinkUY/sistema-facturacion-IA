<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para tabla de Comprobantes Fiscales Electrónicos (CFE)
 * Almacena todos los CFE emitidos para DGI Uruguay
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cfe_submissions', function (Blueprint $table) {
            $table->id();
            
            // Referencias (usar tipos compatibles con las tablas existentes)
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('transaction_id')->nullable();
            $table->unsignedInteger('contact_id')->nullable();
            $table->unsignedInteger('user_id');
            
            // Identificación del CFE
            $table->integer('cfe_type'); // 101, 111, etc.
            $table->string('series', 2)->default('A');
            $table->bigInteger('number');
            
            // Fechas
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            
            // Información de pago
            $table->integer('payment_method')->default(1);
            $table->string('currency', 3)->default('UYU');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            
            // Montos
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            // Detalle de items (JSON)
            $table->json('items')->nullable();
            
            // Estado del CFE
            $table->string('status', 20)->default('pending');
            // pending, submitted, accepted, rejected, error
            
            // Datos del Emisor
            $table->string('emitter_rut', 20)->nullable();
            $table->string('emitter_name')->nullable();
            $table->string('emitter_address')->nullable();
            $table->string('emitter_city')->nullable();
            $table->string('emitter_department')->nullable();
            
            // Datos del Receptor
            $table->string('receiver_doc_type', 10)->default('CI'); // CI, RUT, Pasaporte
            $table->string('receiver_document', 20)->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_address')->nullable();
            $table->string('receiver_city')->nullable();
            $table->string('receiver_department')->nullable();
            
            // XML generado
            $table->longText('xml_content')->nullable();
            $table->longText('signed_xml')->nullable();
            
            // Respuesta DGI
            $table->string('cae', 50)->nullable(); // Código de Autorización de Emisión
            $table->string('track_id', 100)->nullable();
            $table->json('dgi_response')->nullable();
            $table->timestamp('submitted_at')->nullable();
            
            // Notas
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('business_id');
            $table->index('transaction_id');
            $table->index('contact_id');
            $table->index('cfe_type');
            $table->index('status');
            $table->index('issue_date');
            $table->index(['series', 'number']);
            $table->index('cae');
            
            // Unique constraint para evitar duplicados
            $table->unique(['business_id', 'series', 'number'], 'cfe_unique_number');
            
            // Foreign keys
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('set null');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfe_submissions');
    }
};
