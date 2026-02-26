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
        // On MySQL we need to rename purchase_line_id -> removed_purchase_line
        // The original project skipped this for SQLite. When running on MySQL perform the rename.
        if (DB::getDriverName() === 'mysql') {
            if (Schema::hasColumn('stock_adjustment_lines', 'purchase_line_id') && ! Schema::hasColumn('stock_adjustment_lines', 'removed_purchase_line')) {
                // Use raw ALTER to rename the column. Provide a nullable INT definition.
                DB::statement('ALTER TABLE `stock_adjustment_lines` CHANGE COLUMN `purchase_line_id` `removed_purchase_line` INT NULL');
            }
        }
        // For other drivers (e.g., sqlite) this migration remains a no-op.
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
