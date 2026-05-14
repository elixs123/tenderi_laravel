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
        Schema::table('tender_workflows', function (Blueprint $table) {
            $table->string('winner_supplier')->nullable()->after('reason');
            $table->decimal('final_price', 15, 2)->nullable()->after('winner_supplier');
            $table->timestamp('won_at')->nullable()->after('final_price');
            $table->timestamp('lost_at')->nullable()->after('won_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tender_workflows', function (Blueprint $table) {
            $table->dropColumn(['winner_supplier', 'final_price', 'won_at', 'lost_at']);
        });
    }
};
