<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\Procedure;
use App\Models\Lot;
use App\Models\SyncJobLog;
use App\Notifications\NewTenderDetected;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('app:sync-lots')]
#[Description('Sync lotova filtriranjem po LastUpdated direktno na API-ju')]
class SyncLots extends Command
{
    public function handle()
    {
        $pageSize    = 100;
        $skip        = 0;
        $insertedIds = [];
        $updatedIds  = [];
        $startedAt   = now();
        $syncedTo    = $startedAt->copy();

        $syncedFrom = SyncJobLog::lastSyncedTo('lots')
            ?? Carbon::yesterday()->startOfDay();

        // $syncedFrom = Carbon::parse('2026-05-15')->startOfDay();

        $logEntry = SyncJobLog::create([
            'job'         => 'lots',
            'status'      => 'running',
            'synced_from' => $syncedFrom,
            'synced_to'   => $syncedTo,
            'started_at'  => $startedAt,
        ]);

        $fromDate = $syncedFrom->format('Y-m-d\TH:i:s\Z');

        $this->info("🚀 Pokrećem sync lotova...");
        $this->warn("📅 Period: {$syncedFrom->format('d.m.Y H:i')} → sada");

        try {
            while (true) {
                $this->info("📡 Request na Skip: {$skip}...");

                $response = Http::withoutVerifying()->timeout(60)->get("https://open.ejn.gov.ba/LotsBase", [
                    '$top'     => $pageSize,
                    '$skip'    => $skip,
                    '$orderby' => 'LastUpdated desc',
                    '$filter'  => "LastUpdated ge {$fromDate}",
                ]);

                if ($response->failed()) {
                    $this->error("❌ API Greška! Status: " . $response->status());
                    break;
                }

                $lots = $response->json('value');

                if (empty($lots)) {
                    $this->info("🏁 Nema više lotova za ovaj period.");
                    break;
                }

                $this->line("📦 Paket: " . count($lots) . " lotova");

                $lotIds       = array_column($lots, 'Id');
                $procedureIds = array_unique(array_column($lots, 'ProcedureId'));

                $existingProcedureIds = Procedure::whereIn('id', $procedureIds)
                    ->pluck('id')
                    ->flip();

                $lots = array_filter($lots, fn($lot) => isset($existingProcedureIds[$lot['ProcedureId']]));

                if (empty($lots)) {
                    $this->warn("⚠️ Sve procedure iz ovog paketa nisu još syncane, preskačem.");
                    $skip += $pageSize;
                    continue;
                }

                $lotIds = array_column($lots, 'Id');

                $cpvLinks = Http::withoutVerifying()->get("https://open.ejn.gov.ba/LotCpvCodeLinks", [
                    '$filter' => "LotId in (" . implode(',', $lotIds) . ")",
                ])->json('value', []);

                $cpvByLot = collect($cpvLinks)->keyBy('LotId');

                foreach ($lots as $lot) {
                    $cpvCodeId = $cpvByLot[$lot['Id']]['CpvCodeId'] ?? null;

                    $this->info("   🔄 Lot ID: {$lot['Id']} (Proc: {$lot['ProcedureId']}) | CPV: " . ($cpvCodeId ?? 'N/A'));

                    $lotModel = Lot::updateOrCreate(
                        ['id' => $lot['Id']],
                        [
                            'id'           => $lot['Id'],
                            'procedure_id' => $lot['ProcedureId'],
                            'name'         => $lot['Name'] ?? null,
                            'no'           => $lot['No'] ?? null,
                            'status'       => $lot['Status'] ?? null,
                            'additional_information'   => $lot['AdditionalInformation'] ?? null,
                            'contract_duration'        => $lot['ContractDuration'] ?? null,
                            'estimated_value'          => $lot['EstimatedValue'] ?? 0,
                            'extended_duration_reason' => $lot['ExtendedDurationReason'] ?? null,
                            'has_complaint'            => $lot['HasComplaint'] ?? false,
                            'location'                 => $lot['Location'] ?? null,
                            'master_agreement_duration' => $lot['MasterAgreementDuration'] ?? null,
                            'master_agreement_duration_interval_type' => $lot['MasterAgreementDurationIntervalType'] ?? null,
                            'quantity'          => $lot['Quantity'] ?? null,
                            'short_description' => $lot['ShortDescription'] ?? null,
                            'application_deadline_date_time'       => $lot['ApplicationDeadlineDateTime'] ? Carbon::parse($lot['ApplicationDeadlineDateTime']) : null,
                            'bid_opening_date_time'                => $lot['BidOpeningDateTime'] ? Carbon::parse($lot['BidOpeningDateTime']) : null,
                            'documentation_take_over_deadline_date' => $lot['DocumentationTakeOverDeadlineDate'] ? Carbon::parse($lot['DocumentationTakeOverDeadlineDate']) : null,
                            'last_updated' => Carbon::parse($lot['LastUpdated']),
                        ]
                    );

                    if ($lotModel->wasRecentlyCreated) {
                        $insertedIds[] = $lotModel->id;
                    } else {
                        $updatedIds[] = $lotModel->id;
                    }

                    if ($cpvCodeId) {
                        Procedure::where('id', $lot['ProcedureId'])->update(['cpvcodeid' => $cpvCodeId]);
                    }
                }

                $skip += $pageSize;
                usleep(500000);
            }

            $logEntry->update([
                'status'         => 'completed',
                'finished_at'    => now(),
                'inserted_count' => count($insertedIds),
                'updated_count'  => count($updatedIds),
            ]);

            $this->info("✅ Završeno. Inserovano: " . count($insertedIds) . ", updateovano: " . count($updatedIds));

            $this->sendNewTenderNotifications();

        } catch (\Throwable $e) {
            $logEntry->update(['status' => 'failed', 'finished_at' => now()]);
            throw $e;
        }

        Log::channel('sync')->info('sync-lots', [
            'started_at'  => $startedAt->toDateTimeString(),
            'finished_at' => now()->toDateTimeString(),
            'synced_from' => $syncedFrom->toDateTimeString(),
            'inserted'    => $insertedIds,
            'updated'     => $updatedIds,
            'total'       => count($insertedIds) + count($updatedIds),
        ]);
    }

    private function sendNewTenderNotifications(): void
    {
        $insertedIds = SyncJobLog::lastCompletedTendersInsertedIds();

        if (empty($insertedIds)) {
            return;
        }

        // Leaf CPV IDs + Root CPV IDs — isto kao list-tenders
        $trackedCpvIds = DB::table('user_to_category')
            ->selectRaw('category_id, category_root_id')
            ->get()
            ->flatMap(fn($r) => array_filter([$r->category_id, $r->category_root_id]))
            ->unique()
            ->values()
            ->all();

        // Regioni i tipovi sa svih korisnika (ne samo user 11)
        $users = DB::table('users')->whereNotNull('settings')->pluck('settings');

        $allRegions = $users->flatMap(function ($s) {
            $decoded = is_string($s) ? json_decode($s, true) : [];
            return $decoded['regions'] ?? [];
        })->filter()->unique()->values()->all();

        $allTypes = $users->flatMap(function ($s) {
            $decoded = is_string($s) ? json_decode($s, true) : [];
            return $decoded['types'] ?? [];
        })->filter()->unique()->values()->all();

        $query = Procedure::whereIn('id', $insertedIds)
            ->whereIn('cpvcodeid', $trackedCpvIds);

        if (!empty($allRegions)) {
            $query->whereIn('contracting_authority_city_name', $allRegions);
        }

        if (!empty($allTypes)) {
            $query->whereIn('type', $allTypes);
        }

        $procedures = $query->with('lots')->get();

        if ($procedures->isEmpty()) {
            $this->info("📭 Nema novih tendera koji odgovaraju praćenim CPV kodovima i regionima.");
            return;
        }

        Notification::route('mail', env('MAIL_TO_PRODAJA'))
            ->notify(new NewTenderDetected($procedures));

        $this->info("📧 Mail poslan — {$procedures->count()} tendera odgovara praćenim CPV kodovima i regionima.");
    }
}
