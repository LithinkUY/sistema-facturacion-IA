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
        // Solo agregar la columna si no existe (SQLite):
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('allow_login')->default(1)->after('business_id');
            });
        } catch (\Exception $e) {
            // Si ya existe, ignorar el error
        }
        // SQLite no soporta ALTER TABLE CHANGE. Se omite para compatibilidad.
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
