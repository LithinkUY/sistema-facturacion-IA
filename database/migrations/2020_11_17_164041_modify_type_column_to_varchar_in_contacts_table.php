<?php

use App\Contact;
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

    // SQLite no soporta ALTER TABLE MODIFY COLUMN. Se omite para compatibilidad.

        Contact::where('type', '=', '')
                 ->orWhereNull('type')
                ->update(['type' => 'lead']);
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
