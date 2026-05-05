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
        Schema::create('tenders', function (Blueprint $table) {
            $table->id(); // SERIAL PRIMARY KEY
            
            // ID sa e-Nabavki (external_id)
            $table->string('external_id', 100)->unique();
            
            $table->text('title');
            $table->text('contracting_authority');
            
            $table->string('type', 50)->nullable();
            $table->string('procedure_type', 100)->nullable();
            $table->string('cpv_code', 20)->nullable();
            
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->string('currency', 10)->default('BAM');
            
            // Statusi: 'new', 'accepted', 'rejected'
            $table->string('status', 20)->default('new');
            $table->text('rejection_reason')->nullable();
            
            // JSONB je odličan za čuvanje cijelog odgovora sa API-ja za svaki slučaj
            $table->jsonb('raw_data')->nullable(); 
            
            // Standardni Laravel timestamps (created_at i updated_at)
            $table->timestamps();

            // Index za bržu pretragu po nazivu (idx_tenders_title)
            $table->index('title', 'idx_tenders_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};