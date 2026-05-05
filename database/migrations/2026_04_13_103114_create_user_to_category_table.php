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
        Schema::create('user_to_category', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('user_id')->nullable()->index('idx_user_id');
            
            $table->integer('category_id')->index('idx_category_id');
            $table->integer('category_root_id');
            
            $table->string('is_main', 255);
            
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'category_id'], 'unique_user_cpv');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_to_category');
    }
};