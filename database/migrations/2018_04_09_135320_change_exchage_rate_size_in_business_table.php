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
    // SQLite does not support ALTER TABLE MODIFY COLUMN. Skipping for SQLite compatibility.
    // If you need to change column types or defaults, create a new migration with the desired schema.

        //Update 0 to 1
        DB::table('transactions')
            ->where('exchange_rate', 0)
            ->update(['exchange_rate' => 1]);
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
