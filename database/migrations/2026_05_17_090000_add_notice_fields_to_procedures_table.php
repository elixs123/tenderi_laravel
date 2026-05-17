<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            // Puni broj objave iz ProcurementNotices (npr. 707-1-1-67-3-87/26)
            // Procedure.number je bazni broj (707-1-1-67/26), ovo je broj sa tipom i seq ID-jem
            $table->string('notice_number', 100)->nullable()->after('number');

            // Rokovi iz ProcurementNotices
            $table->timestamp('application_deadline_date_time')->nullable()->after('notice_number');
            $table->timestamp('bid_opening_date_time')->nullable()->after('application_deadline_date_time');

            // Kontakt podaci iz ProcurementNotices
            $table->string('contact_email', 255)->nullable()->after('contact_person_name');
            $table->string('contact_phone', 100)->nullable()->after('contact_email');
            $table->string('contact_website', 255)->nullable()->after('contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->dropColumn([
                'notice_number',
                'application_deadline_date_time',
                'bid_opening_date_time',
                'contact_email',
                'contact_phone',
                'contact_website',
            ]);
        });
    }
};
