<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('wa_message_id')->nullable()->index(); // ID del mensaje en WhatsApp
            $table->string('phone_number', 20)->index(); // Número del cliente (+59899123456)
            $table->string('contact_name')->nullable(); // Nombre del contacto en WhatsApp
            $table->unsignedBigInteger('contact_id')->nullable(); // FK a contacts del sistema
            $table->enum('direction', ['incoming', 'outgoing'])->default('incoming');
            $table->enum('message_type', ['text', 'image', 'audio', 'document', 'location', 'template', 'interactive', 'reaction'])->default('text');
            $table->text('message')->nullable(); // Contenido del texto
            $table->text('media_url')->nullable(); // URL de media si aplica
            $table->string('media_mime_type')->nullable();
            $table->string('media_id')->nullable(); // Media ID de WhatsApp
            $table->enum('status', ['sent', 'delivered', 'read', 'failed', 'received'])->default('received');
            $table->boolean('is_ai_response')->default(false); // Si fue respondido por IA
            $table->string('session_id', 64)->nullable()->index(); // Para historial de conversación IA
            $table->text('ai_context')->nullable(); // Contexto usado para la respuesta IA
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'phone_number']);
            $table->index(['phone_number', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
