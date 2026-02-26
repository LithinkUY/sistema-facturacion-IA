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
        // SQLite does not support ALTER TABLE MODIFY COLUMN or ENUM types. Skipping for SQLite compatibility.
        // If you need to enforce allowed values, use application-level validation.
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
