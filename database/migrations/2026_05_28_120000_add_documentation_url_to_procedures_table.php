<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            // Direktan URL ka PDF-u tenderske dokumentacije na docs.ejn.gov.ba
            // (npr. https://docs.ejn.gov.ba/Procurement/{UUID}.pdf)
            // Spašava se prvi put kad se uspješno skine, kasnije se koristi za brzo preuzimanje.
            $table->string('documentation_url', 500)->nullable()->after('contact_website');
        });
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->dropColumn('documentation_url');
        });
    }
};
