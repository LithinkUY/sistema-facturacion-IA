<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id');
            $table->string('session_id', 64)->index();
            $table->enum('role', ['user', 'model', 'system'])->default('user');
            $table->text('message');
            $table->text('context_data')->nullable(); // JSON con datos del sistema usados
            $table->string('action_type')->nullable(); // consulta_ventas, crear_producto, etc.
            $table->text('action_result')->nullable(); // resultado de la acción ejecutada
            $table->integer('tokens_used')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'user_id', 'session_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_conversations');
    }
};
