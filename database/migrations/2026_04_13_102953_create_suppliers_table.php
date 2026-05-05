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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id(); // SERIAL PRIMARY KEY (id)
            
            // External ID sa e-Nabavki
            $table->integer('supplier_id')->unique();
            
            $table->text('name');
            $table->text('activity_type')->nullable();
            $table->boolean('is_main')->default(true);
            
            // Kolona za datum sa API-ja
            $table->timestamp('last_updated_api')->nullable();
            
            // Standardni Laravel timestamps (created_at i updated_at)
            // Laravel će automatski postaviti CURRENT_TIMESTAMP
            $table->timestamps();

            // Eksplicitno kreiranje indeksa sa tvojim nazivima
            $table->index('supplier_id', 'idx_supplier_external_id');
            $table->index('name', 'idx_supplier_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};