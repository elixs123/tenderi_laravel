<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('article_mappings', function (Blueprint $table) {
        $table->id();
        $table->string('tender_description')->index(); // Originalni naziv iz PDF-a sa e-nabavke
        $table->string('acIdent'); 
        $table->string('acName'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_mappings');
    }
};
