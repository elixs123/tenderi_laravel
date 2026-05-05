<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Procedure;
use App\Models\Lot;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('app:sync-lots {--limit=5000 : Maksimalan broj procedura za skip}')]
#[Description('Totalni sync procedura i lotova od juče do danas sa ispravkom za Postgres')]
class SyncLots extends Command
{
    public function handle()
    {
        $fromDate = Carbon::today()->startOfDay();
        // $fromDate = Carbon::yesterday()->startOfDay();
        $this->info("🚀 Pokrećem TOTALNI sync (Procedure + Lotovi, od: {$fromDate->format('d.m.Y H:i')})...");

        $skip = 0;
        $hasMore = true;
        $syncedProcedures = 0;

        $procededures = Procedure::where('last_updated', '>=', $fromDate)->get();

        foreach($procededures as $proc) {
            $this->syncLotsForProcedure($proc->id);
            $syncedProcedures++;

            usleep(500000);
        }

        // while ($hasMore) {
        //     $this->comment("Provjeravam paket procedura: skip={$skip}...");
            
        //     $url = "https://open.ejn.gov.ba/Procedures";
        //     $response = Http::timeout(60)->get($url, [
        //         '$filter' => "LastUpdated ge {$fromDate->format('Y-m-d\TH:i:s\Z')}",
        //         '$orderby' => "LastUpdated desc",
        //         '$skip' => $skip,
        //         '$top' => 50
        //     ]);

        //     if (!$response->successful()) {
        //         $this->error("❌ Greška pri komunikaciji sa API-jem na skip: {$skip}");
        //         break;
        //     }

        //     $procedures = $response->json('value');

        //     if (empty($procedures)) {
        //         $hasMore = false;
        //         break;
        //     }

        //     foreach ($procedures as $proc) {
        //         // --- UPSERT PROCEDURE ---
        //         Procedure::updateOrCreate(
        //             ['id' => $proc['Id']],
        //             [
        //                 'id' => $proc['Id'],
        //                 'announced' => $proc['Announced'] ? Carbon::parse($proc['Announced']) : null,
        //                 'award_criterion' => $proc['AwardCriterion'] ?? null,
        //                 'award_type' => $proc['AwardType'] ?? null,
        //                 'contracting_authority_id' => $proc['ContractingAuthorityId'] ?? null,
        //                 'contracting_authority_name' => $proc['ContractingAuthorityName'] ?? null,
        //                 'contracting_authority_tax_number' => $proc['ContractingAuthorityTaxNumber'] ?? null,
        //                 'contracting_authority_city_name' => $proc['ContractingAuthorityCityName'] ?? null,
        //                 'contracting_authority_type' => $proc['ContractingAuthorityType'] ?? null,
        //                 'contracting_authority_activity_type_name' => $proc['ContractingAuthorityActivityTypeName'] ?? null,
        //                 'contracting_authority_administrative_unit_type' => $proc['ContractingAuthorityAdministrativeUnitType'] ?? null,
        //                 'contracting_authority_administrative_unit_name' => $proc['ContractingAuthorityAdministrativeUnitName'] ?? null,
        //                 'contract_type' => $proc['ContractType'] ?? null,
        //                 'has_complaint' => $proc['HasComplaint'] ?? false,
        //                 'has_lots' => $proc['HasLots'] ?? false,
        //                 'is_auction_online' => $proc['IsAuctionOnline'] ?? false,
        //                 'is_electronic_offer' => $proc['IsElectronicOffer'] ?? false,
        //                 'is_joint_procurement' => $proc['IsJointProcurement'] ?? false,
        //                 'is_master_agreement' => $proc['IsMasterAgreement'] ?? false,
        //                 'is_on_behalf_procurement' => $proc['IsOnBehalfProcurement'] ?? false,
        //                 'name' => $proc['Name'] ?? null,
        //                 'number' => $proc['Number'] ?? null,
        //                 'status' => $proc['Status'] ?? null,
        //                 'type' => $proc['Type'] ?? null,
        //                 'bidder_count' => $proc['BidderCount'] ?? 0,
        //                 'contact_person_name' => $proc['ContactPersonName'] ?? null,
        //                 'contract_category_id' => $proc['ContractCategoryId'] ?? null,
        //                 'contract_category_name' => $proc['ContractCategoryName'] ?? null,
                        
        //                 'negotiated_suppliers_count' => is_numeric($proc['NegotiatedSuppliersCount'] ?? null) ? (int)$proc['NegotiatedSuppliersCount'] : null,
        //                 'phase_number' => is_numeric($proc['PhaseNumber'] ?? null) ? (int)$proc['PhaseNumber'] : null,
                        
        //                 'last_updated' => Carbon::parse($proc['LastUpdated']),
        //             ]
        //         );

        //         $this->syncLotsForProcedure($proc['Id']);
                
        //         $syncedProcedures++;
        //         usleep(500000); // 0.5 sekundi
        //     }

        //     $skip += 50;
        //     if ($skip > $this->option('limit')) break; 
            
        //     $this->info("⏳ Odmaram 1s prije novog paketa procedura...");
        //     sleep(1);
        // }

        $this->info("✅ Završeno. Sinhronizovano {$syncedProcedures} procedura sa pripadajućim lotovima.");
    }

    private function syncLotsForProcedure($procedureId)
    {
        $response = Http::get("https://open.ejn.gov.ba/LotsBase", [
            '$filter' => "ProcedureId eq {$procedureId}"
        ]);

        if ($response->successful()) {
            $lots = $response->json('value');
            
            foreach ($lots as $lot) {
                $linkRes = Http::get("https://open.ejn.gov.ba/LotCpvCodeLinks", [
                    '$filter' => "LotId eq {$lot['Id']}"
                ]);
                
                $cpvCodeId = $linkRes->json('value.0.CpvCodeId');

                $this->info("   🔄 Lot ID: {$lot['Id']} (Proc: {$procedureId}) | CPV: " . ($cpvCodeId ?? 'N/A'));

                // --- UPSERT LOT ---
                Lot::updateOrCreate(
                    ['id' => $lot['Id']],
                    [
                        'id' => $lot['Id'],
                        'procedure_id' => $lot['ProcedureId'],
                        'name' => $lot['Name'] ?? null,
                        'no' => $lot['No'] ?? null,
                        'status' => $lot['Status'] ?? null,
                        'additional_information' => $lot['AdditionalInformation'] ?? null,
                        'contract_duration' => $lot['ContractDuration'] ?? null,
                        'estimated_value' => $lot['EstimatedValue'] ?? 0,
                        'extended_duration_reason' => $lot['ExtendedDurationReason'] ?? null,
                        'has_complaint' => $lot['HasComplaint'] ?? false,
                        'location' => $lot['Location'] ?? null,
                        'master_agreement_duration' => $lot['MasterAgreementDuration'] ?? null,
                        'master_agreement_duration_interval_type' => $lot['MasterAgreementDurationIntervalType'] ?? null,
                        'quantity' => $lot['Quantity'] ?? null,
                        'short_description' => $lot['ShortDescription'] ?? null,
                        'application_deadline_date_time' => $lot['ApplicationDeadlineDateTime'] ? Carbon::parse($lot['ApplicationDeadlineDateTime']) : null,
                        'bid_opening_date_time' => $lot['BidOpeningDateTime'] ? Carbon::parse($lot['BidOpeningDateTime']) : null,
                        'documentation_take_over_deadline_date' => $lot['DocumentationTakeOverDeadlineDate'] ? Carbon::parse($lot['DocumentationTakeOverDeadlineDate']) : null,
                        'last_updated' => Carbon::parse($lot['LastUpdated']),
                    ]
                );

                if ($cpvCodeId) {
                    Procedure::where('id', $procedureId)->update(['cpvcodeid' => $cpvCodeId]);
                }
            }
        }
    }
}