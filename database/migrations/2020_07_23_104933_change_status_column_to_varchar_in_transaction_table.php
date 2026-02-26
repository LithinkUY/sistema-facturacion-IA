<?php

use App\Transaction;
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

    // SQLite no soporta ALTER TABLE MODIFY COLUMN. Se omite para compatibilidad.

        Transaction::where('type', 'sell_transfer')
                ->update(['status' => 'final']);

        Transaction::where('type', 'purchase_transfer')
                ->update(['status' => 'received']);
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
