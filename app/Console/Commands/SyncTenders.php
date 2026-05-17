<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Procedure;
use App\Models\SyncJobLog;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('app:sync-tenders')]
#[Description('Potpuna sinhronizacija svih kolona sa e-Nabavke API-ja')]
class SyncTenders extends Command
{
    public function handle()
    {
        $pageSize  = 100;
        $lastId    = 0;
        $insertedIds = [];
        $updatedIds  = [];
        $startedAt   = now();
        $syncedTo    = $startedAt->copy();

        $syncedFrom = Carbon::parse('2026-05-15')->startOfDay();


        // $syncedFrom = SyncJobLog::lastSyncedTo('tenders')
        //     ?? Carbon::yesterday()->startOfDay();

        $logEntry = SyncJobLog::create([
            'job'         => 'tenders',
            'status'      => 'running',
            'synced_from' => $syncedFrom,
            'synced_to'   => $syncedTo,
            'started_at'  => $startedAt,
        ]);

        $fromDate = $syncedFrom->format('Y-m-d\TH:i:s\Z');

        $this->info("🚀 Započinjem sinhronizaciju...");
        $this->warn("📅 Period: {$syncedFrom->format('d.m.Y H:i')} → sada");

        try {
            while (true) {
                $this->info("📡 Request, lastId: {$lastId}...");

                $filter = "Announced ge {$fromDate} and Id gt {$lastId}";

                $response = Http::withoutVerifying()->timeout(60)->get("https://open.ejn.gov.ba/Procedures", [
                    '$top'     => $pageSize,
                    '$orderby' => 'Id asc',
                    '$filter'  => $filter,
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

                $lastId = end($items)['Id'];

                $firstDate = Carbon::parse($items[0]['Announced'])->format('d.m. H:i');
                $lastDate  = Carbon::parse(end($items)['Announced'])->format('d.m. H:i');
                $this->line("📦 Paket: " . count($items) . " zapisa ($firstDate - $lastDate) | lastId: {$lastId}");

                $procedureIds = array_column($items, 'Id');
                $noticesMap   = $this->fetchNotices($procedureIds);

                foreach ($items as $item) {
                    $notice = $noticesMap[$item['Id']] ?? [];

                    $procedure = Procedure::updateOrCreate(
                        ['id' => $item['Id']],
                        [
                            'announced'    => $item['Announced'] ? Carbon::parse($item['Announced']) : null,
                            'award_criterion' => $item['AwardCriterion'] ?? null,
                            'award_type'   => $item['AwardType'] ?? null,

                            'contracting_authority_id'                       => $item['ContractingAuthorityId'] ?? null,
                            'contracting_authority_name'                     => $item['ContractingAuthorityName'] ?? null,
                            'contracting_authority_tax_number'               => $item['ContractingAuthorityTaxNumber'] ?? null,
                            'contracting_authority_city_name'                => $item['ContractingAuthorityCityName'] ?? null,
                            'contracting_authority_type'                     => $item['ContractingAuthorityType'] ?? null,
                            'contracting_authority_activity_type_name'       => $item['ContractingAuthorityActivityTypeName'] ?? null,
                            'contracting_authority_administrative_unit_type' => $item['ContractingAuthorityAdministrativeUnitType'] ?? null,
                            'contracting_authority_administrative_unit_name' => $item['ContractingAuthorityAdministrativeUnitName'] ?? null,

                            'name'          => $item['Name'] ?? 'Bez naziva',
                            'number'        => $item['Number'] ?? null,
                            'status'        => $item['Status'] ?? null,
                            'type'          => $item['Type'] ?? null,
                            'contract_type' => $item['ContractType'] ?? null,

                            'has_complaint'                => $item['HasComplaint'] ?? false,
                            'has_lots'                     => $item['HasLots'] ?? false,
                            'is_auction_online'            => $item['IsAuctionOnline'] ?? false,
                            'is_electronic_offer'          => $item['IsElectronicOffer'] ?? false,
                            'is_joint_procurement'         => $item['IsJointProcurement'] ?? false,
                            'is_master_agreement'          => $item['IsMasterAgreement'] ?? false,
                            'is_on_behalf_procurement'     => $item['IsOnBehalfProcurement'] ?? false,
                            'is_alternative_offer_allowed' => $item['IsAlternativeOfferAllowed'] ?? false,
                            'is_centralized_procurement'   => $item['IsCentralizedProcurement'] ?? false,
                            'is_contract_renewable'        => $item['IsContractRenewable'] ?? false,
                            'is_defence_and_security'      => $item['IsDefenceAndSecurity'] ?? false,
                            'is_documentation_online'      => $item['IsDocumentationOnline'] ?? false,
                            'is_gpa'                       => $item['IsGpa'] ?? false,
                            'is_international_announcement' => $item['IsInternationalAnnouncement'] ?? false,

                            'bidder_count'             => $item['BidderCount'] ?? null,
                            'bidding_invitation_type'  => $item['BiddingInvitationType'] ?? null,
                            'contact_person_name'      => $item['ContactPersonName'] ?? null,
                            'contract_category_id'     => $item['ContractCategoryId'] ?? null,
                            'contract_category_name'   => $item['ContractCategoryName'] ?? null,
                            'contract_subcategory_id'  => $item['ContractSubcategoryId'] ?? null,
                            'contract_subcategory_name' => $item['ContractSubcategoryName'] ?? null,
                            'lot_offer_type'           => $item['LotOfferType'] ?? null,
                            'master_agreement_status'  => $item['MasterAgreementStatus'] ?? null,
                            'master_agreement_sub_type' => $item['MasterAgreementSubType'] ?? null,
                            'negotiated_procedure_announcement_option' => $item['NegotiatedProcedureAnnouncementOption'] ?? null,
                            'negotiated_suppliers_count' => is_numeric($item['NegotiatedSuppliersCount'])
                                ? (int) $item['NegotiatedSuppliersCount']
                                : null,
                            'no_divison_into_lots_explanation' => $item['NoDivisonIntoLotsExplanation'] ?? null,
                            'offers_submission_explanation'    => $item['OffersSubmissionExplanation'] ?? null,
                            'phase_number' => is_numeric($item['PhaseNumber'])
                                ? (int) $item['PhaseNumber']
                                : null,

                            'pi_notice_id'           => $item['PiNoticeId'] ?? null,
                            'pi_notice_name'         => $item['PiNoticeName'] ?? null,
                            'previous_procedure_id'  => $item['PreviousProcedureId'] ?? null,
                            'previous_procedure_name' => $item['PreviousProcedureName'] ?? null,
                            'qs_notice_id'           => $item['QsNoticeId'] ?? null,
                            'qs_notice_name'         => $item['QsNoticeName'] ?? null,

                            'reasons_for_negotiated_procedure' => $item['ReasonsForNegotiatedProcedure'] ?? null,
                            'regulation_quote_id'   => $item['RegulationQuoteId'] ?? null,
                            'regulation_quote_name' => $item['RegulationQuoteName'] ?? null,

                            'cpvcodeid'    => $item['CpvCodeId'] ?? 0,
                            'last_updated' => $item['LastUpdated'] ? Carbon::parse($item['LastUpdated']) : now(),

                            // Podaci iz ProcurementNotices
                            'notice_number'                  => $notice['Number'] ?? null,
                            'application_deadline_date_time' => isset($notice['ApplicationDeadlineDateTime'])
                                ? Carbon::parse($notice['ApplicationDeadlineDateTime'])
                                : null,
                            'bid_opening_date_time'          => isset($notice['BidOpeningDateTime'])
                                ? Carbon::parse($notice['BidOpeningDateTime'])
                                : null,
                            'contact_email'   => $notice['AdditionalInformationEmailAddress'] ?? null,
                            'contact_phone'   => $notice['AdditionalInformationPhoneNumber'] ?? null,
                            'contact_website' => $notice['AdditionalInformationWebSite'] ?? null,
                        ]
                    );

                    if ($procedure->wasRecentlyCreated) {
                        $insertedIds[] = $procedure->id;
                    } else {
                        $updatedIds[] = $procedure->id;
                    }
                }


                $this->comment("⏳ Pauza 1s...");
                sleep(1);
            }

            $logEntry->update([
                'status'         => 'completed',
                'finished_at'    => now(),
                'inserted_count' => count($insertedIds),
                'updated_count'  => count($updatedIds),
                'inserted_ids'   => $insertedIds,
            ]);

            $this->info("🏆 Sve kolone su uspješno sinhronizovane!");

        } catch (\Throwable $e) {
            $logEntry->update(['status' => 'failed', 'finished_at' => now()]);
            throw $e;
        }

        Log::channel('sync')->info('sync-tenders', [
            'started_at'  => $startedAt->toDateTimeString(),
            'finished_at' => now()->toDateTimeString(),
            'synced_from' => $syncedFrom->toDateTimeString(),
            'inserted'    => $insertedIds,
            'updated'     => $updatedIds,
            'total'       => count($insertedIds) + count($updatedIds),
        ]);
    }

    // Dohvata ProcurementNotices za batch ID-ova i vraća mapu [ProcedureId => notice]
    private function fetchNotices(array $procedureIds): array
    {
        if (empty($procedureIds)) {
            return [];
        }

        $idList = implode(',', $procedureIds);

        $response = Http::withoutVerifying()->timeout(60)->get('https://open.ejn.gov.ba/ProcurementNotices', [
            '$filter' => "ProcedureId in ({$idList})",
            '$top'    => count($procedureIds),
        ]);

        if ($response->failed()) {
            $this->warn("⚠️  ProcurementNotices fetch nije uspio za batch: {$idList}");
            return [];
        }

        $map = [];
        foreach ($response->json('value') ?? [] as $notice) {
            $map[$notice['ProcedureId']] = $notice;
        }

        return $map;
    }
}
