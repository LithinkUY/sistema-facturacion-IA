<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('payment_receipts')) {
            return;
        }

        if (! Schema::hasColumn('payment_receipts', 'payment_method')) {
            Schema::table('payment_receipts', function (Blueprint $table) {
                $table->string('payment_method', 50)->nullable()->after('currency_code');
            });
        }

        if (! Schema::hasColumn('payment_receipts', 'bank_name')) {
            Schema::table('payment_receipts', function (Blueprint $table) {
                $table->string('bank_name')->nullable()->after('payment_method');
            });
        }

        if (! Schema::hasColumn('payment_receipts', 'bank_reference')) {
            Schema::table('payment_receipts', function (Blueprint $table) {
                $table->string('bank_reference')->nullable()->after('bank_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('payment_receipts')) {
            return;
        }

        $columns = collect(['bank_reference', 'bank_name', 'payment_method'])
            ->filter(function ($column) {
                return Schema::hasColumn('payment_receipts', $column);
            })
            ->values();

        if ($columns->isNotEmpty()) {
            Schema::table('payment_receipts', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns->all());
            });
        }
    }
};
