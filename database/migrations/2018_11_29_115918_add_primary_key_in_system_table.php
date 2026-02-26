<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        // SQLite no permite agregar una columna PRIMARY KEY a una tabla existente.
        // Si necesitas la columna 'id', deberás recrear la tabla manualmente.
        // Se omite este cambio para compatibilidad con SQLite.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('system');
    }
};
