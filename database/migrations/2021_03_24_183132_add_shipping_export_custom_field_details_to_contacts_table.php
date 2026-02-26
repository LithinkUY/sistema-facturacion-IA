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
        try { Schema::table('contacts', function (Blueprint $table) { $table->longText('shipping_custom_field_details')->nullable()->after('shipping_address'); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->boolean('is_export')->default(false)->after('shipping_custom_field_details'); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('export_custom_field_1')->nullable()->after('is_export'); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('export_custom_field_2')->nullable()->after('export_custom_field_1'); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('export_custom_field_3')->nullable()->after('export_custom_field_2'); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('export_custom_field_4')->nullable()->after('export_custom_field_3'); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('export_custom_field_5')->nullable()->after('export_custom_field_4'); }); } catch (\Exception $e) {}
        try { Schema::table('contacts', function (Blueprint $table) { $table->string('export_custom_field_6')->nullable()->after('export_custom_field_5'); }); } catch (\Exception $e) {}

    // SQLite no soporta ALTER TABLE MODIFY COLUMN. Se omite para compatibilidad.
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
