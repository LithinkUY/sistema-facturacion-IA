<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('cfe_status')->nullable()->after('export_custom_fields_info');
            $table->string('cfe_track_id')->nullable()->after('cfe_status');
            $table->json('cfe_last_response')->nullable()->after('cfe_track_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['cfe_status', 'cfe_track_id', 'cfe_last_response']);
        });
    }
};
