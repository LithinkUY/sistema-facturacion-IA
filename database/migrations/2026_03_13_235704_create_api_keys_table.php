<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->integer('created_by')->unsigned();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('name'); // Nombre descriptivo: "Web stockba.es", "App Movil", etc.
            $table->string('api_key', 64)->unique(); // Token único
            $table->string('api_secret', 64); // Secret para firmar requests (opcional)
            $table->text('permissions')->nullable(); // JSON con permisos: ["products.read","contacts.write",...]
            $table->text('allowed_ips')->nullable(); // JSON con IPs permitidas (null = todas)
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Tabla para log de uso de API
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_key_id')->nullable();
            $table->foreign('api_key_id')->references('id')->on('api_keys')->onDelete('set null');
            $table->string('method', 10); // GET, POST, PUT, DELETE
            $table->string('endpoint'); // /api/v1/products
            $table->string('ip_address', 45)->nullable();
            $table->integer('response_code')->default(200);
            $table->text('request_body')->nullable();
            $table->integer('response_time_ms')->nullable(); // Tiempo de respuesta en ms
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_logs');
        Schema::dropIfExists('api_keys');
    }
};
