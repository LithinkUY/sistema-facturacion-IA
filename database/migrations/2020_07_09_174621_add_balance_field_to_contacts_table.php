<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregar columnas solo si no existen (SQLite):
        try { Schema::table('contacts', function (Blueprint $table) { $table->decimal('balance', 22, 4)->default(0)->after('created_by'); }); } catch (\Exception $e) {}
        try { Schema::table('transaction_payments', function (Blueprint $table) { $table->boolean('is_advance')->default(0)->after('created_by'); }); } catch (\Exception $e) {}
        // SQLite no soporta ALTER TABLE MODIFY COLUMN. Se omite para compatibilidad.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
