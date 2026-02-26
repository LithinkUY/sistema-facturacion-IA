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
        Schema::table('order_pedidos', function (Blueprint $table) {
            $table->string('reference')->nullable()->after('priority');
            $table->string('shipping_method')->nullable()->after('shipping_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_pedidos', function (Blueprint $table) {
            $table->dropColumn(['reference', 'shipping_method']);
        });
    }
};
