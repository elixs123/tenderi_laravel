<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sync_job_logs', function (Blueprint $table) {
            $table->json('inserted_ids')->nullable()->after('updated_count');
        });
    }

    public function down(): void
    {
        Schema::table('sync_job_logs', function (Blueprint $table) {
            $table->dropColumn('inserted_ids');
        });
    }
};
