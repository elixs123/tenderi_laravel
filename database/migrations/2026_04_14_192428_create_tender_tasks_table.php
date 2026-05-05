<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tender_tasks', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('tender_workflow_id')->constrained('tender_workflows')->cascadeOnDelete();            
            // Osnovni podaci o dokumentu/zadatku
            $table->string('naziv'); // npr. "Garancija za ponudu"
            $table->string('kategorija')->nullable(); // npr. "Banka", "Pravno"
            
            // Praćenje statusa
            $table->string('status')->default('na_cekanju'); // na_cekanju, pribavljeno, kasni
            $table->text('razlog_kasnjenja')->nullable(); // Upisuje radnik ako zapne
            $table->timestamp('acquired_at')->nullable(); // Tačno vrijeme kada je dokument pribavljen
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_tasks');
    }
};
