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

    // SQLite no soporta MODIFY COLUMN ni ENUM, así que solo agregamos los campos nuevos

        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('expense_category_id')->nullable()->after('final_total');
            $table->integer('expense_for')->nullable()->after('expense_category_id');
            $table->index('expense_category_id');
            // Las foreign keys no se agregan aquí porque SQLite no soporta alter table add foreign key fácilmente
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
