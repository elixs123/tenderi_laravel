<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedures', function (Blueprint $table) {
            // Primarni ključ sa EJN API-ja
            $table->unsignedBigInteger('id')->primary();
            
            // Osnovni podaci o objavi
            $table->timestamp('announced')->nullable();
            $table->string('award_criterion', 100)->nullable();
            $table->string('award_type', 100)->nullable();
            
            // Podaci o ugovornom organu (Penny Plus konkurenti/partneri)
            $table->unsignedBigInteger('contracting_authority_id')->nullable()->index('idx_procedures_authority_id');
            $table->text('contracting_authority_name')->nullable();
            $table->string('contracting_authority_tax_number', 50)->nullable();
            $table->string('contracting_authority_city_name', 255)->nullable();
            $table->string('contracting_authority_type', 100)->nullable();
            $table->string('contracting_authority_activity_type_name', 255)->nullable();
            $table->string('contracting_authority_administrative_unit_type', 100)->nullable();
            $table->string('contracting_authority_administrative_unit_name', 255)->nullable();
            
            // Karakteristike tendera (Booleans)
            $table->string('contract_type', 100)->nullable();
            $table->boolean('has_complaint')->default(false);
            $table->boolean('has_lots')->default(false);
            $table->boolean('is_auction_online')->default(false);
            $table->boolean('is_electronic_offer')->default(false);
            $table->boolean('is_joint_procurement')->default(false);
            $table->boolean('is_master_agreement')->default(false);
            $table->boolean('is_on_behalf_procurement')->default(false);
            
            // Detalji postupka
            $table->text('name')->index('idx_procedures_name'); // Naslov tendera
            $table->string('number', 100)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('type', 100)->nullable();
            
            // Dodatne informacije
            $table->string('bidder_count', 100)->nullable();
            $table->string('bidding_invitation_type', 100)->nullable();
            $table->string('contact_person_name', 255)->nullable();
            
            // Kategorizacija
            $table->integer('contract_category_id')->nullable();
            $table->text('contract_category_name')->nullable();
            $table->integer('contract_subcategory_id')->nullable();
            $table->text('contract_subcategory_name')->nullable();
            
            // Pravne i tehničke postavke
            $table->boolean('is_alternative_offer_allowed')->default(false);
            $table->boolean('is_centralized_procurement')->default(false);
            $table->boolean('is_contract_renewable')->default(false);
            $table->boolean('is_defence_and_security')->default(false);
            $table->boolean('is_documentation_online')->default(false);
            $table->boolean('is_gpa')->default(false);
            $table->boolean('is_international_announcement')->default(false);
            
            $table->string('lot_offer_type', 100)->nullable();
            $table->string('master_agreement_status', 100)->nullable();
            $table->string('master_agreement_sub_type', 100)->nullable();
            $table->string('negotiated_procedure_announcement_option', 100)->nullable();
            $table->integer('negotiated_suppliers_count')->nullable();
            $table->text('no_divison_into_lots_explanation')->nullable();
            $table->text('offers_submission_explanation')->nullable();
            $table->integer('phase_number')->nullable();
            
            // Povezana obavještenja
            $table->unsignedBigInteger('pi_notice_id')->nullable();
            $table->text('pi_notice_name')->nullable();
            $table->unsignedBigInteger('previous_procedure_id')->nullable();
            $table->text('previous_procedure_name')->nullable();
            $table->unsignedBigInteger('qs_notice_id')->nullable();
            $table->text('qs_notice_name')->nullable();
            
            $table->text('reasons_for_negotiated_procedure')->nullable();
            $table->unsignedBigInteger('regulation_quote_id')->nullable();
            $table->text('regulation_quote_name')->nullable();
            
            // Vanjski ključ prema cpvcodes (već smo napravili tabelu)
            $table->unsignedBigInteger('cpvcodeid')->index('idx_procedures_cpvcodeid');
            
            // Timestamps
            $table->timestamp('last_updated')->nullable();
            $table->timestamps(); // created_at i updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};