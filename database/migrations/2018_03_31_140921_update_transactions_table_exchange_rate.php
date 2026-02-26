<?php

use Illuminate\Database\Migrations\Migration;

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
