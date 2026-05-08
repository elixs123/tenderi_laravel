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
        Schema::create('lots', function (Blueprint $table) {
            // Primarni ključ sa e-Nabavki (bez auto-incrementa)
            $table->unsignedBigInteger('id')->primary();
            
            // Strani ključ prema procedurama
            // Pretpostavljamo da se tabela zove 'procedures'
            $table->foreignId('procedure_id')
                  ->constrained('procedures')
                  ->onDelete('cascade');

            $table->text('name')->nullable();
            $table->string('no', 50)->nullable();
            $table->string('master_agreement_status', 100)->nullable();
            $table->string('status', 50)->nullable()->index(); // Odmah dodajemo indeks na status
            
            $table->text('additional_information')->nullable();
            $table->string('contract_duration', 255)->nullable();
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->text('extended_duration_reason')->nullable();
            
            $table->boolean('has_complaint')->default(false);
            $table->text('location')->nullable();
            
            $table->integer('master_agreement_duration')->nullable();
            $table->string('master_agreement_duration_interval_type', 50)->nullable();
            
            $table->text('quantity')->nullable();
            $table->text('short_description')->nullable();
            $table->integer('phase_number')->nullable();

            // Svi rokovi i datumi sa API-ja
            $table->timestamp('application_deadline_date_time')->nullable();
            $table->timestamp('bid_opening_date_time')->nullable();
            $table->timestamp('documentation_take_over_deadline_date')->nullable();
            $table->timestamp('intermediate_phase_documentation_download_deadline')->nullable();
            $table->timestamp('intermediate_phase_offer_submission_deadline')->nullable();
            $table->timestamp('procurement_phase_documentation_download_deadline')->nullable();
            $table->timestamp('procurement_phase_offer_submission_deadline')->nullable();
            $table->timestamp('recommendation_resend_deadline')->nullable();
            
            $table->timestamp('last_updated')->nullable();
            
            // Standardni Laravel timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};