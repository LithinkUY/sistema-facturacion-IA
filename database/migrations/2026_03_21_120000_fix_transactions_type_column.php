<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixTransactionsTypeColumn extends Migration
{
    /**
     * Run the migrations.
     * Fix: transactions.type was ENUM('purchase','sell') but the app uses many more types
     * like opening_stock, sell_return, purchase_return, stock_adjustment, etc.
     * This caused opening_stock inserts to silently fail (empty string stored).
     */
    public function up()
    {
        // Change type column from ENUM to VARCHAR(50) to support all transaction types
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT ''");

        // Fix any broken records that have empty type but are opening_stock
        DB::statement("UPDATE transactions SET type = 'opening_stock' WHERE LENGTH(type) = 0 AND opening_stock_product_id IS NOT NULL");

        // Make contact_id nullable (opening_stock transactions don't always have a contact)
        DB::statement("ALTER TABLE transactions MODIFY COLUMN contact_id INT(10) UNSIGNED NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Revert contact_id to NOT NULL
        DB::statement("ALTER TABLE transactions MODIFY COLUMN contact_id INT(10) UNSIGNED NOT NULL");

        // Revert type to ENUM (will lose non-enum values)
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('purchase','sell') NOT NULL");
    }
}
