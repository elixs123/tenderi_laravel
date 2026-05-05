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
       Schema::table('lots', function (Blueprint $table) {
            $table->text('additional_information')->nullable()->change();
            $table->text('location')->nullable()->change();
            $table->text('short_description')->nullable()->change();
            $table->text('contract_duration')->nullable()->change();
            $table->text('extended_duration_reason')->nullable()->change();
        });

        Schema::table('procedures', function (Blueprint $table) {
            $table->text('name')->nullable()->change();
            $table->text('contracting_authority_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('text', function (Blueprint $table) {
            //
        });
    }
};
