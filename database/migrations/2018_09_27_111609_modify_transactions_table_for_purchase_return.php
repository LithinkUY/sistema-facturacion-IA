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
        // SQLite does not support ALTER TABLE MODIFY COLUMN or ENUM types. Skipping for SQLite compatibility.
        // If you need to enforce allowed values, use application-level validation.

        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('return_parent_id')->nullable()->after('transfer_parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('transactions', function (Blueprint $table) {
        //     //
        // });
    }
};
