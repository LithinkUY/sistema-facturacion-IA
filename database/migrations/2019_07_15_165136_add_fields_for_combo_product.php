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
            Schema::table('variations', function (Blueprint $table) {
                $table->text('combo_variations')->nullable()->comment('Contains the combo variation details');
            });
        } catch (\Exception $e) {
            // Si ya existe, ignorar el error
        }

    // SQLite no soporta ALTER TABLE CHANGE ni ENUM. Se omite para compatibilidad.

        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            $table->string('children_type')
                ->default('')
                ->after('parent_sell_line_id')
                ->comment('Type of children for the parent, like modifier or combo');

            $table->index(['children_type']);
            $table->index(['parent_sell_line_id']);
        });

        DB::statement("UPDATE transaction_sell_lines SET children_type='modifier' WHERE parent_sell_line_id IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variations', function (Blueprint $table) {
            $table->dropColumn(['combo_variations']);
        });
    }
};
