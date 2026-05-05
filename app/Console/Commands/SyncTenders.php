<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Procedure;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('app:sync-tenders')]
#[Description('Potpuna sinhronizacija svih kolona sa e-Nabavke API-ja (juče i danas)')]
class SyncTenders extends Command
{
    public function handle()
    {
        $pageSize = 100;
        $skip = 0;
        
        $fromDate = Carbon::yesterday()->startOfDay()->format('Y-m-d\TH:i:s\Z');

        // $fromDate = Carbon::today()->startOfDay();
        
        $this->info("🚀 Započinjem duboku sinhronizaciju...");
        $this->warn("📅 Period: Sve od {$fromDate} do sada.");

        while (true) {
            $this->info("📡 Request na Skip: {$skip}...");
            
            $response = Http::timeout(60)->get("https://open.ejn.gov.ba/Procedures", [
                '$top' => $pageSize,
                '$skip' => $skip,
                '$orderby' => 'Announced desc',
                '$filter' => "Announced ge {$fromDate}"
            ]);

            $this->info("request" . $response->effectiveUri() . " | Status: " . $response->status());

            if ($response->failed()) {
                $this->error("❌ API Greška! Status: " . $response->status());
                break;
            }

            $items = $response->json('value');

            if (empty($items)) {
                $this->info("🏁 Nema više podataka za ovaj period. Završavam.");
                break;
            }

            $firstDate = Carbon::parse($items[0]['Announced'])->format('d.m. H:i');
            $lastDate = Carbon::parse(end($items)['Announced'])->format('d.m. H:i');
            $this->line("📦 Paket: " . count($items) . " zapisa ($firstDate - $lastDate)");

            foreach ($items as $item) {
                Procedure::updateOrCreate(
                    ['id' => $item['Id']],
                    [
                        'announced' => $item['Announced'] ? Carbon::parse($item['Announced']) : null,
                        'award_criterion' => $item['AwardCriterion'] ?? null,
                        'award_type' => $item['AwardType'] ?? null,
                        
                        // Ugovorni organ (Svi detalji)
                        'contracting_authority_id' => $item['ContractingAuthorityId'] ?? null,
                        'contracting_authority_name' => $item['ContractingAuthorityName'] ?? null,
                        'contracting_authority_tax_number' => $item['ContractingAuthorityTaxNumber'] ?? null,
                        'contracting_authority_city_name' => $item['ContractingAuthorityCityName'] ?? null,
                        'contracting_authority_type' => $item['ContractingAuthorityType'] ?? null,
                        'contracting_authority_activity_type_name' => $item['ContractingAuthorityActivityTypeName'] ?? null,
                        'contracting_authority_administrative_unit_type' => $item['ContractingAuthorityAdministrativeUnitType'] ?? null,
                        'contracting_authority_administrative_unit_name' => $item['ContractingAuthorityAdministrativeUnitName'] ?? null,
                        
                        // Osnovne informacije
                        'name' => $item['Name'] ?? 'Bez naziva',
                        'number' => $item['Number'] ?? null,
                        'status' => $item['Status'] ?? null,
                        'type' => $item['Type'] ?? null,
                        'contract_type' => $item['ContractType'] ?? null,
                        
                        // Logička polja (Flags)
                        'has_complaint' => $item['HasComplaint'] ?? false,
                        'has_lots' => $item['HasLots'] ?? false,
                        'is_auction_online' => $item['IsAuctionOnline'] ?? false,
                        'is_electronic_offer' => $item['IsElectronicOffer'] ?? false,
                        'is_joint_procurement' => $item['IsJointProcurement'] ?? false,
                        'is_master_agreement' => $item['IsMasterAgreement'] ?? false,
                        'is_on_behalf_procurement' => $item['IsOnBehalfProcurement'] ?? false,
                        'is_alternative_offer_allowed' => $item['IsAlternativeOfferAllowed'] ?? false,
                        'is_centralized_procurement' => $item['IsCentralizedProcurement'] ?? false,
                        'is_contract_renewable' => $item['IsContractRenewable'] ?? false,
                        'is_defence_and_security' => $item['IsDefenceAndSecurity'] ?? false,
                        'is_documentation_online' => $item['IsDocumentationOnline'] ?? false,
                        'is_gpa' => $item['IsGpa'] ?? false,
                        'is_international_announcement' => $item['IsInternationalAnnouncement'] ?? false,
                        
                        // Dodatni detalji i kategorije
                        'bidder_count' => $item['BidderCount'] ?? null,
                        'bidding_invitation_type' => $item['BiddingInvitationType'] ?? null,
                        'contact_person_name' => $item['ContactPersonName'] ?? null,
                        'contract_category_id' => $item['ContractCategoryId'] ?? null,
                        'contract_category_name' => $item['ContractCategoryName'] ?? null,
                        'contract_subcategory_id' => $item['ContractSubcategoryId'] ?? null,
                        'contract_subcategory_name' => $item['ContractSubcategoryName'] ?? null,
                        'lot_offer_type' => $item['LotOfferType'] ?? null,
                        'master_agreement_status' => $item['MasterAgreementStatus'] ?? null,
                        'master_agreement_sub_type' => $item['MasterAgreementSubType'] ?? null,
                        'negotiated_procedure_announcement_option' => $item['NegotiatedProcedureAnnouncementOption'] ?? null,
                        'negotiated_suppliers_count' => is_numeric($item['NegotiatedSuppliersCount']) 
                                ? (int)$item['NegotiatedSuppliersCount'] 
                                : null,
                        'no_divison_into_lots_explanation' => $item['NoDivisonIntoLotsExplanation'] ?? null,
                        'offers_submission_explanation' => $item['OffersSubmissionExplanation'] ?? null,
                        'phase_number' => is_numeric($item['PhaseNumber']) 
                  ? (int)$item['PhaseNumber'] 
                  : null,
                        
                        // Notifikacije i povezani ID-ovi
                        'pi_notice_id' => $item['PiNoticeId'] ?? null,
                        'pi_notice_name' => $item['PiNoticeName'] ?? null,
                        'previous_procedure_id' => $item['PreviousProcedureId'] ?? null,
                        'previous_procedure_name' => $item['PreviousProcedureName'] ?? null,
                        'qs_notice_id' => $item['QsNoticeId'] ?? null,
                        'qs_notice_name' => $item['QsNoticeName'] ?? null,
                        
                        // Propisi i razlozi
                        'reasons_for_negotiated_procedure' => $item['ReasonsForNegotiatedProcedure'] ?? null,
                        'regulation_quote_id' => $item['RegulationQuoteId'] ?? null,
                        'regulation_quote_name' => $item['RegulationQuoteName'] ?? null,
                        
                        // CPV i Meta
                        'cpvcodeid' => $item['CpvCodeId'] ?? 0,
                        'last_updated' => $item['LastUpdated'] ? Carbon::parse($item['LastUpdated']) : now(),
                    ]
                );
            }

            $skip += $pageSize;
            
            // Mala pauza da ne preopteretimo ejn.gov.ba
            $this->comment("⏳ Pauza 1s...");
            sleep(1);
        }

        $this->info("🏆 Sve kolone su uspješno sinhronizovane!");
    }
}