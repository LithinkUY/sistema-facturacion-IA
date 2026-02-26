<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Get all columns with type decimal(20, 2)
        $db_name = env('DB_DATABASE');

        // Esta migración solo aplica a MySQL. SQLite no tiene information_schema ni ALTER TABLE MODIFY COLUMN para decimales.
        // Se omite para compatibilidad.


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
