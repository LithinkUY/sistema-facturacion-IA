<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agregar campo cae_due_date (Fecha de Vencimiento del CAE)
 * Requerido por normativa DGI Uruguay para todos los CFE
 * El CAE tiene una fecha de vencimiento que debe figurar en el comprobante impreso
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cfe_submissions', function (Blueprint $table) {
            $table->date('cae_due_date')->nullable()->after('cae')
                  ->comment('Fecha de vencimiento del CAE - Requerido por DGI');
        });
    }

    public function down(): void
    {
        Schema::table('cfe_submissions', function (Blueprint $table) {
            $table->dropColumn('cae_due_date');
        });
    }
};
