<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->string('currency_code', 10); // USD, EUR, etc.
            $table->decimal('buy_rate', 22, 4)->default(0);
            $table->decimal('sell_rate', 22, 4)->default(0);
            $table->string('source', 100)->default('manual'); // brou, bcu, manual
            $table->date('rate_date');
            $table->timestamps();

            $table->index(['business_id', 'currency_code', 'rate_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('exchange_rates');
    }
};
