<?php

namespace App\Console\Commands;

use App\Models\Lot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WeeklyManagementReport;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('app:send-weekly-report')]
#[Description('Šalje sedmični izvještaj tendera upravi')]
class SendWeeklyManagementReport extends Command
{
    const ACCEPTED_STATUSES = ['accepted', 'offer_submitted', 'documentation_uploaded', 'won', 'completed'];
    const REJECTED_STATUSES = ['rejected', 'lost'];

    public function handle()
    {
        $from = Carbon::now()->subWeek()->startOfDay();
        $to   = Carbon::now()->endOfDay();

        $this->info("📊 Generišem sedmični izvještaj ({$from->format('d.m.Y')} — {$to->format('d.m.Y')})...");

        $workflows = collect(DB::select("
            SELECT tw.id, tw.procedure_id, tw.status, tw.reason, tw.created_at, tw.updated_at,
                   tw.accepted_lots,
                   p.name as procedure_name,
                   p.contracting_authority_name as contracting_authority,
                   CONCAT(u.first_name, ' ', u.last_name) as user_name
            FROM tender_workflows tw
            JOIN procedures p ON p.id = tw.procedure_id
            JOIN users u ON u.id = tw.user_id
            WHERE tw.updated_at >= NOW() - INTERVAL '7 days'
            ORDER BY tw.updated_at DESC
        "));

        $accepted = $workflows->whereIn('status', self::ACCEPTED_STATUSES)->values();
        $rejected = $workflows->whereIn('status', self::REJECTED_STATUSES)->values();

        // Učitaj lotove za sve prihvaćene workflowove koji imaju accepted_lots
        $allLotIds = $accepted->flatMap(function ($w) {
            $ids = is_string($w->accepted_lots) ? json_decode($w->accepted_lots, true) : [];
            return is_array($ids) ? $ids : [];
        })->filter()->unique()->values();

        $lotsById = $allLotIds->isNotEmpty()
            ? Lot::whereIn('id', $allLotIds)->get()->keyBy('id')
            : collect();

        $accepted = $accepted->map(function ($w) use ($lotsById) {
            $ids  = is_string($w->accepted_lots) ? json_decode($w->accepted_lots, true) : [];
            $ids  = is_array($ids) ? $ids : [];
            $lots = collect($ids)->map(fn($id) => $lotsById->get($id))->filter();

            $w->lot_names    = $lots->map(fn($l) => $l->name ?: ('LOT ' . $l->no))->join(', ');
            $w->lot_value    = $lots->sum('estimated_value');
            $w->has_lots     = $lots->isNotEmpty();
            return $w;
        });

        $pending = collect(DB::select("
            SELECT p.id, p.name as procedure_name,
                   p.contracting_authority_name as contracting_authority,
                   p.created_at
            FROM procedures p
            WHERE p.created_at >= NOW() - INTERVAL '7 days'
              AND NOT EXISTS (
                  SELECT 1 FROM tender_workflows tw WHERE tw.procedure_id = p.id
              )
              AND p.cpvcodeid IN (
                  SELECT category_id FROM user_to_category WHERE user_id = 11
              )
            ORDER BY p.created_at DESC
        "));

        $this->info("  ✅ Prihvaćeni: {$accepted->count()}");
        $this->info("  ❌ Odbijeni:   {$rejected->count()}");
        $this->info("  ⏳ Na čekanju: {$pending->count()}");

        Notification::route('mail', env('MAIL_TO_UPRAVA')
            ->notify(new WeeklyManagementReport(
                accepted: $accepted,
                rejected: $rejected,
                pending:  $pending,
                weekFrom: $from->format('d.m.Y'),
                weekTo:   $to->format('d.m.Y'),
            ));

        $this->info("📧 Izvještaj poslan na elvissarajcic@gmail.com");
    }
}
