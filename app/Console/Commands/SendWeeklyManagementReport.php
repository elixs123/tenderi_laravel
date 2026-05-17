<?php

namespace App\Console\Commands;

use App\Models\Lot;
use App\Models\Procedure;
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

        // Učitaj lotove za prihvaćene workflowove
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

            $w->lot_names = $lots->map(fn($l) => $l->name ?: ('LOT ' . $l->no))->join(', ');
            $w->lot_value = $lots->sum('estimated_value');
            $w->has_lots  = $lots->isNotEmpty();
            return $w;
        });

        // Settings, CPV kodovi (leaf + root) i regioni od usera 11
        $user11Settings = DB::table('users')->where('id', 11)->value('settings');
        $user11Settings = is_string($user11Settings) ? json_decode($user11Settings, true) : [];

        $user11Regions = array_values(array_filter($user11Settings['regions'] ?? []));
        $user11Types   = array_values(array_filter($user11Settings['types'] ?? []));

        $user11CpvIds = DB::table('user_to_category')
            ->where('user_id', 11)
            ->get(['category_id', 'category_root_id'])
            ->flatMap(fn($r) => array_filter([$r->category_id, $r->category_root_id]))
            ->unique()
            ->values()
            ->all();

        $pendingQuery = Procedure::query()
            ->where('created_at', '>=', now()->subWeek())
            ->whereDoesntHave('workflow')
            ->whereIn('cpvcodeid', $user11CpvIds);

        if (!empty($user11Regions)) {
            $pendingQuery->whereIn('contracting_authority_city_name', $user11Regions);
        }
        if (!empty($user11Types)) {
            $pendingQuery->whereIn('type', $user11Types);
        }

        $pending = $pendingQuery
            ->orderByDesc('created_at')
            ->get(['id', 'name as procedure_name', 'contracting_authority_name as contracting_authority', 'created_at']);

        $this->info("  ✅ Prihvaćeni: {$accepted->count()}");
        $this->info("  ❌ Odbijeni:   {$rejected->count()}");
        $this->info("  ⏳ Na čekanju: {$pending->count()}");

        Notification::route('mail', env('MAIL_TO_UPRAVA'))
            ->notify(new WeeklyManagementReport(
                accepted: $accepted,
                rejected: $rejected,
                pending:  $pending,
                weekFrom: $from->format('d.m.Y'),
                weekTo:   $to->format('d.m.Y'),
            ));

        $this->info("📧 Izvještaj poslan na " . env('MAIL_TO_UPRAVA'));
    }
}
