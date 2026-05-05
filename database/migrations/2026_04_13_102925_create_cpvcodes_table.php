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
        Schema::create('cpvcodes', function (Blueprint $table) {
            // Pošto API daje svoj ID, koristimo unsignedBigInteger kao primarni ključ bez auto-incrementa
            $table->unsignedBigInteger('id')->primary();
            
            // Code sa indeksom za bržu pretragu (idx_category_code)
            $table->string('code', 50)->index();
            
            $table->text('description')->nullable();
            $table->unsignedBigInteger('root_id')->nullable();
            $table->string('root_code', 50)->nullable();
            $table->text('root_description')->nullable();
            
            // Timestampovi
            $table->timestamp('last_updated')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Ako želiš Laravelov standardni updated_at, dodaj:
            // $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpvcodes');
    }
};