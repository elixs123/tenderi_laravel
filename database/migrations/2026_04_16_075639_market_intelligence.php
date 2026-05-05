<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabela za Market Intelligence: Najave, Realizovani ugovori i Dodjele.
     */
    public function up(): void
    {
       Schema::create('market_intelligence', function (Blueprint $table) {
            $table->id(); 
            $table->string('type'); // ANNUNCIEMENT, CONTRACT, AWARD, NEGOTIATED, ANNUAL_NOTICE, NON_PUBLISHED
            $table->integer('external_id');
            $table->string('authority_name');
            $table->string('title');
            $table->string('cpv_code')->index()->nullable();
            $table->decimal('value', 15, 2)->default(0);
            $table->string('supplier_name')->nullable();
            $table->timestamp('event_date');
            $table->timestamp('expiry_date')->nullable(); // Precizan podatak iz LOT-a
            $table->integer('duration_months')->nullable();
            $table->integer('duration_days')->nullable();
            $table->string('city')->nullable();
            $table->string('procedure_type')->nullable();
            $table->integer('offers_count')->default(0);
            $table->boolean('is_master_agreement')->default(false);
            $table->timestamps();

            $table->unique(['type', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_intelligence');
    }
};