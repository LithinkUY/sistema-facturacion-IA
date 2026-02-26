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
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('prefix')->after('name')->nullable(); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('first_name')->after('prefix')->nullable(); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('middle_name')->after('first_name')->nullable(); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('last_name')->after('middle_name')->nullable(); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->text('address_line_2')->after('landmark')->nullable(); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('zip_code')->after('address_line_2')->nullable(); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->date('dob')->after('zip_code')->nullable(); }); } catch (\Exception $e) {}

    // SQLite no soporta ALTER TABLE CHANGE. Se omite para compatibilidad.

        DB::statement('UPDATE contacts SET first_name=name;');
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
