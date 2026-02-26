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
        // SQLite no soporta ALTER TABLE CHANGE ni ENUM, se omite este cambio

        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('transfer_parent_id')->nullable()->after('total_amount_recovered');
            $table->integer('opening_stock_product_id')->nullable()->after('transfer_parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
