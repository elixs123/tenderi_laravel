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
        Schema::create('tender_workflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procedure_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('status'); //  'accepted', 'rejected', 'pending'
            $table->text('reason')->nullable();
            $table->string('document_path')->nullable(); 
            $table->timestamp('completed_at')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tender_workflows');
    }
};
