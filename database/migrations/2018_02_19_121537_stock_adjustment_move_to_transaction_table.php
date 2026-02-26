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

    // SQLite no soporta SET FOREIGN_KEY_CHECKS

        DB::statement('DROP TABLE IF EXISTS stock_adjustment_lines');

        Schema::create('stock_adjustment_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->unsigned();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->integer('variation_id')->unsigned();
            $table->foreign('variation_id')->references('id')->on('variations')
            ->onDelete('cascade');
            $table->decimal('quantity', 22, 4);
            $table->decimal('unit_price', 22, 4)->comment('Last purchase unit price')->nullable();
            $table->timestamps();

            //Indexing
            $table->index('transaction_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('adjustment_type', ['normal', 'abnormal'])->nullable()->after('payment_status');
            $table->decimal('total_amount_recovered', 22, 4)->comment('Used for stock adjustment.')->nullable()->after('exchange_rate');
        });

        //Create & Rename stock_adjustment table.
        DB::statement('CREATE TABLE IF NOT EXISTS `stock_adjustments` (`id` int(11) DEFAULT NULL) ');
        Schema::rename('stock_adjustments', 'stock_adjustments_temp');

    // SQLite no soporta SET FOREIGN_KEY_CHECKS
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
