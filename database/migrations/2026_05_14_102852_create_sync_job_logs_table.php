<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_job_logs', function (Blueprint $table) {
            $table->id();
            $table->string('job'); // tenders / lots
            $table->string('status')->default('running'); // running / completed / failed
            $table->timestamp('synced_from');
            $table->timestamp('synced_to');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('inserted_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_job_logs');
    }
};
