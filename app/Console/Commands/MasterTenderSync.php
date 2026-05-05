<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\MarketIntelligence;
use Carbon\Carbon;

class MasterTenderSync extends Command
{
    protected $signature = 'tenders:sync-all';

    public function handle()
    {
        $this->info("🚀 Pokrećem Master Intelligence Sync (6 izvora)...");
        
        // Granica: 2026. godina da izbjegnemo stare podatke
        $cutoffDate = "2026-01-01T00:00:00Z";

        // 1. Planovi nabavki
        $this->syncAnnouncements($cutoffDate);
        
        // 2. Realizovani ugovori (LotContracts)
        $this->syncContracts($cutoffDate);
        
        // 3. Odluke o dodjeli (Awards)
        $this->syncAwards($cutoffDate);
        
        // 4. Pregovarački postupci (Negotiated) - HITNO
        $this->syncNegotiated($cutoffDate);
        
        // 5. Godišnje prethodne najave (Annual Notices) - STRATEŠKI
        $this->syncAnnualNotices($cutoffDate);
        
        // 6. Usluge iz Aneksa II (NonPublished)
        $this->syncNonPublished($cutoffDate);

        $this->info("✅ Sveobuhvatni Master Sync završen!");
    }

    // --- 1. NAJAVE / PLANOVI ---
    private function syncAnnouncements($cutoffDate)
    {
        $this->info("📅 Povlačim planove nabavki...");
        $items = Http::get("https://open.ejn.gov.ba/AnnouncementProcurementPlans", [
            '$top' => 100, '$orderby' => "Announced desc", '$filter' => "Announced ge $cutoffDate"
        ])->json('value') ?? [];
        
        foreach ($items as $i) {
            $this->saveToDb('ANNUNCIEMENT', $i, 'Announced', '0');
        }
    }

    // --- 2. UGOVORI ---
    private function syncContracts($cutoffDate)
    {
        $this->info("💰 Povlačim ugovore (LotContracts)...");
        $items = Http::get("https://open.ejn.gov.ba/LotContracts", [
            '$top' => 60, '$orderby' => 'ContractDate desc', '$filter' => "ContractDate ge $cutoffDate"
        ])->json('value') ?? [];

        foreach ($items as $i) {
            $this->saveToDb('CONTRACT', $i, 'ContractDate', 'Value');
        }
    }

    // --- 3. DODJELE ---
    private function syncAwards($cutoffDate)
    {
        $this->info("🏆 Povlačim odluke o dodjeli (Awards)...");
        $items = Http::get("https://open.ejn.gov.ba/Awards", [
            '$top' => 60, '$orderby' => "ContractDate desc", '$filter' => "ContractDate ge $cutoffDate"
        ])->json('value') ?? [];

        foreach ($items as $i) {
            $this->saveToDb('AWARD', $i, 'ContractDate', 'Value');
        }
    }

    // --- 4. PREGOVARAČKI (HITNO) ---
    private function syncNegotiated($cutoffDate) {
        $this->info("⚡ Povlačim pregovaračke postupke...");
        $items = Http::get("https://open.ejn.gov.ba/AnnouncementNegotiatedProcedureInformations", [
            '$top' => 50, '$orderby' => "Announced desc", '$filter' => "Announced ge $cutoffDate"
        ])->json('value') ?? [];

        foreach ($items as $i) {
            $this->saveToDb('NEGOTIATED', $i, 'Announced', 'EstimatedValue');
        }
    }

    // --- 5. GODIŠNJE NAJAVE ---
    private function syncAnnualNotices($cutoffDate) {
        $this->info("📅 Povlačim godišnje najave...");
        $items = Http::get("https://open.ejn.gov.ba/AnnouncementAnnualNotices", [
            '$top' => 50, '$orderby' => "Announced desc", '$filter' => "Announced ge $cutoffDate"
        ])->json('value') ?? [];

        foreach ($items as $i) {
            $this->saveToDb('ANNUAL_NOTICE', $i, 'Announced');
        }
    }

    // --- 6. ANEKS II (NON-PUBLISHED) ---
    private function syncNonPublished($cutoffDate) {
        $this->info("📦 Povlačim Aneks II...");
        $items = Http::get("https://open.ejn.gov.ba/NonPublicationS2Notices", [
            '$top' => 50, '$orderby' => "Announced desc", '$filter' => "Announced ge $cutoffDate"
        ])->json('value') ?? [];

        foreach ($items as $i) {
            $eventDate = Carbon::parse($i['Announced']);
            $expiryDate = null;
            
            // Konkretan podatak iz NPS rute
            if (isset($i['NpsProcurementContractDuration'])) {
                $duration = $i['NpsProcurementContractDuration'];
                $unit = strtolower($i['NpsProcurementContractDurationIntervalType'] ?? 'month');
                $expiryDate = match($unit) {
                    'day' => $eventDate->copy()->addDays($duration),
                    'year' => $eventDate->copy()->addYears($duration),
                    default => $eventDate->copy()->addMonths($duration),
                };
            }

            MarketIntelligence::updateOrCreate(
                ['type' => 'NON_PUBLISHED', 'external_id' => $i['Id']],
                [
                    'authority_name' => $i['ContractingAuthorityName'] ?? 'Nepoznat',
                    'title' => $i['NpsProcurementName'] ?? 'Aneks II Nabavka',
                    'event_date' => $eventDate,
                    'expiry_date' => $expiryDate,
                    'value' => $i['NpsProcurementEstimatedValue'] ?? 0,
                    'city' => $i['ContractingAuthorityCityName'] ?? 'BiH',
                    'procedure_type' => 'Aneks II dio B'
                ]
            );
        }
    }

    // --- UNIVERZALNI HELPERI ---

    private function saveToDb($type, $data, $dateKey, $valueKey = 'Value') {
        $details = $this->getLotDetails($data['ProcedureId'] ?? null);
        $eventDate = Carbon::parse($data[$dateKey]);

        MarketIntelligence::updateOrCreate(
            ['type' => $type, 'external_id' => $data['Id']],
            [
                'authority_name' => $data['ContractingAuthorityName'] ?? 'Nepoznat organ',
                'title' => $data['ProcedureName'] ?? ($data['Title'] ?? 'Nabavka'),
                'cpv_code' => $data['MainCpvCode'] ?? $details['cpv'],
                'value' => $data[$valueKey] ?? ($data['ContractValueVatExcluded'] ?? 0),
                'supplier_name' => $data['SupplierName'] ?? ($type === 'NEGOTIATED' ? 'U pregovorima' : null),
                'event_date' => $eventDate,
                'expiry_date' => $this->calculateActualExpiry($eventDate, $details),
                'duration_months' => $details['months'],
                'duration_days' => $details['days'],
                'city' => $data['ContractingAuthorityCityName'] ?? 'BiH',
                'procedure_type' => $data['ProcedureType'] ?? null,
                'is_master_agreement' => $data['IsMasterAgreement'] ?? false,
                'offers_count' => $data['NumberOfReceivedOffers'] ?? 0,
            ]
        );
        usleep(100000); // 0.1s pauza da ne opteretimo API
    }

    private function getLotDetails($procedureId) {
        $res = ['cpv' => '00000000-0', 'months' => null, 'days' => null];
        if (!$procedureId) return $res;

        $response = Http::timeout(10)->get("https://open.ejn.gov.ba/Lots", [
            '$filter' => "ProcedureId eq $procedureId", '$top' => 1
        ]);
        
        if ($response->successful()) {
            $lot = $response->json('value')[0] ?? null;
            if ($lot) {
                $res['cpv'] = $lot['MainCpvCode'] ?? ($lot['CpvCode'] ?? '00000000-0');
                $res['months'] = $lot['DurationInMonths'] ?? null;
                $res['days'] = $lot['DurationInDays'] ?? null;
            }
        }
        return $res;
    }

    private function calculateActualExpiry($startDate, $details) {
        if (!$details['months'] && !$details['days']) return null;
        
        $date = $startDate->copy();
        if ($details['months']) return $date->addMonths($details['months']);
        if ($details['days']) return $date->addDays($details['days']);
        return null;
    }
}