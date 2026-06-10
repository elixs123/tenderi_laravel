<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TenderWorkflow;
use App\Models\Lot;
use App\Models\Procedure;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $selectedUser = '';
    public $statusFilter = '';
    public $users = [];

    public $stats = [
        'ukupno'                      => 0,
        'u_pripremi'                  => 0,
        'na_cekanju'                  => 0,
        'dobijeni'                    => 0,
        'izgubljeni'                  => 0,
        'prekinuti'                   => 0,
        'win_rate'                    => 0,
        'ukupna_vrijednost_dobijenih' => 0,
        'prosjek_vrijednosti'         => 0,
        'vrijednost_ovaj_mj'          => 0,
        'rast'                        => 0,
    ];

    public $expandedTender    = null;
    public $managementComments = [];
    public $competitorStats   = [];
    public $trendData         = [];
    public $upcomingDeadlines = [];
    public $topCpv            = [];
    public $pipelineStats     = [];
    public $recentWins        = [];
    public $employeeStats     = [];
    public $modalTenderId     = null;
    public $modalComment      = '';

    public $viewMode = 'dashboard'; 
    public $selectedTenderId = null;

    public function mount()
    {
        $this->users = User::orderBy('first_name')->get();
        $this->loadStats();
    }

    public function updatedSelectedUser() { $this->resetPage(); $this->loadStats(); }
    public function updatedStatusFilter()  { $this->resetPage(); }

    public function viewProcess($id)
    {
        $this->selectedTenderId = $id;
        $this->viewMode = 'process';
    }

    public function loadStats()
    {
        $q = TenderWorkflow::query();
        if (!empty($this->selectedUser)) {
            $q->where('user_id', $this->selectedUser);
        }

        $this->stats['ukupno']     = (clone $q)->count();
        $this->stats['u_pripremi'] = (clone $q)->whereIn('status', ['accepted', 'documentation_uploaded'])->count();
        $this->stats['na_cekanju'] = (clone $q)->where('status', 'offer_submitted')->count();
        $this->stats['dobijeni']   = (clone $q)->where('status', 'won')->count();
        $this->stats['izgubljeni'] = (clone $q)->whereIn('status', ['rejected', 'lost'])->count();
        $this->stats['prekinuti']  = (clone $q)->where('status', 'cancelled')->count();

        $zavrseni = $this->stats['dobijeni'] + $this->stats['izgubljeni'];
        $this->stats['win_rate'] = $zavrseni > 0
            ? round(($this->stats['dobijeni'] / $zavrseni) * 100, 1)
            : 0;

        $this->stats['ukupna_vrijednost_dobijenih'] = (clone $q)
            ->where('status', 'won')
            ->withSum('lots', 'estimated_value')
            ->get()
            ->sum('lots_sum_estimated_value');

        // --- Top konkurencija (iz winner_supplier polja) ---
        $competitors = (clone $q)
            ->whereIn('status', ['lost'])
            ->whereNotNull('winner_supplier')
            ->where('winner_supplier', '!=', '')
            ->select('winner_supplier', DB::raw('count(*) as cnt'))
            ->groupBy('winner_supplier')
            ->orderByDesc('cnt')
            ->limit(3)
            ->get();

        $totalLost = (clone $q)->where('status', 'lost')->whereNotNull('winner_supplier')->count();

        $this->competitorStats = $competitors->map(fn($c) => [
            'name'    => $c->winner_supplier,
            'count'   => $c->cnt,
            'percent' => $totalLost > 0 ? round(($c->cnt / $totalLost) * 100) : 0,
        ])->toArray();

        // Fallback na ai_parsed_data ako winner_supplier nije popunjen
        if (empty($this->competitorStats)) {
            $lostTenders = (clone $q)->whereIn('status', ['rejected', 'lost'])->get();
            $compCounts = [];
            foreach ($lostTenders as $t) {
                $data  = $t->ai_parsed_data ?? [];
                $cName = trim($data['konkurencija']['ime'] ?? '');
                if (!empty($cName)) {
                    $compCounts[$cName] = ($compCounts[$cName] ?? 0) + 1;
                }
            }
            arsort($compCounts);
            $total = array_sum($compCounts);
            $this->competitorStats = [];
            foreach (array_slice($compCounts, 0, 3) as $name => $count) {
                $this->competitorStats[] = [
                    'name'    => $name,
                    'count'   => $count,
                    'percent' => $total > 0 ? round(($count / $total) * 100) : 0,
                ];
            }
        }

        // --- Trend po mjesecima (zadnjih 12) ---
        $trend = (clone $q)
            ->select(
                DB::raw("to_char(created_at, 'YYYY-MM') as mjesec"),
                DB::raw('count(*) as ukupno'),
                DB::raw("count(*) filter (where status = 'won') as dobijeno"),
                DB::raw("count(*) filter (where status in ('lost','rejected')) as izgubljeno")
            )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('mjesec')
            ->orderBy('mjesec')
            ->get();

        $trendMap = $trend->keyBy('mjesec');
        $allMonths = [];
        for ($i = 11; $i >= 0; $i--) {
            $allMonths[] = now()->subMonths($i)->format('Y-m');
        }
        $this->trendData = array_map(fn($m) => [
            'label'      => \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M y'),
            'ukupno'     => (int) ($trendMap[$m]->ukupno ?? 0),
            'dobijeno'   => (int) ($trendMap[$m]->dobijeno ?? 0),
            'izgubljeno' => (int) ($trendMap[$m]->izgubljeno ?? 0),
        ], $allMonths);

        // --- Upcoming deadlines (sljedecih 7 dana) ---
        $this->upcomingDeadlines = Lot::with(['procedure.workflow.user'])
            ->whereBetween('application_deadline_date_time', [now(), now()->addDays(7)])
            ->whereHas('procedure.workflow', function ($q) {
                $q->whereIn('status', ['accepted', 'documentation_uploaded', 'offer_submitted']);
                if (!empty($this->selectedUser)) {
                    $q->where('user_id', $this->selectedUser);
                }
            })
            ->orderBy('application_deadline_date_time')
            ->limit(5)
            ->get()
            ->map(fn($lot) => [
                'naziv'      => $lot->procedure->name ?? 'N/A',
                'organ'      => $lot->procedure->contracting_authority_name ?? '',
                'deadline'   => $lot->application_deadline_date_time,
                'days_left'  => (int) now()->diffInDays($lot->application_deadline_date_time, false),
                'hours_left' => (int) now()->diffInHours($lot->application_deadline_date_time, false),
                'user'       => $lot->procedure->workflow?->user?->first_name ?? '',
                'wf_id'      => $lot->procedure->workflow?->id,
            ])
            ->toArray();

        // --- Pipeline funnel ---
        $this->pipelineStats = [
            ['label' => 'Prihvaćeni',    'count' => (clone $q)->where('status', 'accepted')->count(),                'color' => 'blue',    'icon' => 'fa-file-circle-check'],
            ['label' => 'Dokumentacija', 'count' => (clone $q)->where('status', 'documentation_uploaded')->count(),  'color' => 'amber',   'icon' => 'fa-file-arrow-up'],
            ['label' => 'Predato',       'count' => (clone $q)->where('status', 'offer_submitted')->count(),         'color' => 'indigo',  'icon' => 'fa-paper-plane'],
            ['label' => 'Dobijeni',      'count' => $this->stats['dobijeni'],                                        'color' => 'emerald', 'icon' => 'fa-trophy'],
        ];

        // --- Nedavne pobjede ---
        $this->recentWins = TenderWorkflow::with(['procedure', 'user'])
            ->where('status', 'won')
            ->when(!empty($this->selectedUser), fn($q) => $q->where('user_id', $this->selectedUser))
            ->withSum('lots as vrijednost', 'estimated_value')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn($w) => [
                'naziv'    => $w->procedure->name ?? 'N/A',
                'organ'    => $w->procedure->contracting_authority_name ?? '',
                'user'     => $w->user->first_name ?? '',
                'vrijednost' => floatval($w->vrijednost ?? 0),
                'datum'    => $w->updated_at->format('d.m.Y'),
                'wf_id'    => $w->id,
            ])->toArray();

        // --- Učinak po zaposleniku ---
        $this->employeeStats = User::select([
                'users.id', 'users.first_name', 'users.last_name',
                DB::raw("(SELECT COALESCE(SUM(l.estimated_value), 0) FROM tender_workflows tw JOIN lots l ON l.procedure_id = tw.procedure_id WHERE tw.user_id = users.id AND tw.status = 'won') as vrijednost_dobijenih"),
            ])
            ->withCount([
                'workflows as ukupno',
                'workflows as dobijeni'   => fn($q) => $q->where('status', 'won'),
                'workflows as izgubljeni' => fn($q) => $q->whereIn('status', ['lost', 'rejected']),
            ])
            ->whereHas('workflows')
            ->get()
            ->map(fn($u) => [
                'name'      => $u->first_name . ' ' . $u->last_name,
                'ukupno'    => $u->ukupno,
                'dobijeni'  => $u->dobijeni,
                'izgubljeni' => $u->izgubljeni,
                'win_rate'  => ($u->dobijeni + $u->izgubljeni) > 0
                    ? round(($u->dobijeni / ($u->dobijeni + $u->izgubljeni)) * 100)
                    : 0,
                'vrijednost' => floatval($u->vrijednost_dobijenih),
            ])
            ->sortByDesc('win_rate')
            ->values()
            ->toArray();

        // --- Prosjek + Rast ---
        $this->stats['prosjek_vrijednosti'] = (clone $q)
            ->withSum('lots', 'estimated_value')
            ->get()
            ->avg('lots_sum_estimated_value') ?? 0;

        $this->stats['vrijednost_ovaj_mj'] = (clone $q)
            ->where('status', 'won')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->withSum('lots', 'estimated_value')
            ->get()->sum('lots_sum_estimated_value');

        $vrijednostProsliMj = (clone $q)
            ->where('status', 'won')
            ->whereMonth('updated_at', now()->subMonth()->month)
            ->whereYear('updated_at', now()->subMonth()->year)
            ->withSum('lots', 'estimated_value')
            ->get()->sum('lots_sum_estimated_value');

        $this->stats['rast'] = $vrijednostProsliMj > 0
            ? round((($this->stats['vrijednost_ovaj_mj'] - $vrijednostProsliMj) / $vrijednostProsliMj) * 100)
            : ($this->stats['vrijednost_ovaj_mj'] > 0 ? 100 : 0);

        // --- Top CPV kategorije ---
        $this->topCpv = TenderWorkflow::query()
            ->when(!empty($this->selectedUser), fn($q) => $q->where('user_id', $this->selectedUser))
            ->join('procedures', 'tender_workflows.procedure_id', '=', 'procedures.id')
            ->join('cpvcodes', 'procedures.cpvcodeid', '=', 'cpvcodes.id')
            ->select(
                'cpvcodes.root_description as naziv',
                DB::raw('count(*) as ukupno'),
                DB::raw("count(*) filter (where tender_workflows.status = 'won') as dobijeno")
            )
            ->whereNotNull('cpvcodes.root_description')
            ->groupBy('cpvcodes.root_description')
            ->orderByDesc('ukupno')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'naziv'    => $r->naziv,
                'ukupno'   => (int) $r->ukupno,
                'dobijeno' => (int) $r->dobijeno,
                'rate'     => $r->ukupno > 0 ? round(($r->dobijeno / $r->ukupno) * 100) : 0,
            ])
            ->toArray();
    }

    public function toggleExpand($tenderId)
    {
        if ($this->expandedTender === $tenderId) {
            $this->expandedTender = null;
        } else {
            $this->expandedTender = $tenderId;
            $tender = TenderWorkflow::find($tenderId);
            $data   = $tender->ai_parsed_data ?? [];
            $this->managementComments[$tenderId] = $data['uprava_komentar'] ?? '';
        }
    }

    public function openModal($tenderId)
    {
        $this->modalTenderId = $tenderId;
        $tender = TenderWorkflow::find($tenderId);
        $this->modalComment = $tender->ai_parsed_data['uprava_komentar'] ?? '';
    }

    public function closeModal()
    {
        $this->modalTenderId = null;
        $this->modalComment  = '';
    }

    public function saveModalComment()
    {
        $tender = TenderWorkflow::find($this->modalTenderId);
        if ($tender) {
            $data = $tender->ai_parsed_data ?? [];
            $data['uprava_komentar'] = $this->modalComment;
            $tender->update(['ai_parsed_data' => $data]);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Komentar spašen.']);
        }
    }

    public function saveComment($tenderId)
    {
        $tender = TenderWorkflow::find($tenderId);
        if ($tender) {
            $data = $tender->ai_parsed_data ?? [];
            $data['uprava_komentar'] = $this->managementComments[$tenderId] ?? '';
            $tender->update(['ai_parsed_data' => $data]);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Komentar spašen.']);
        }
    }

    public function render()
    {
        $query = TenderWorkflow::query()
            ->with(['user', 'procedure', 'lots'])
            ->withSum('lots as ukupna_vrijednost', 'estimated_value');

        if ($this->selectedUser !== '') {
            $query->where('user_id', $this->selectedUser);
        }

        if ($this->statusFilter === 'won') {
            $query->where('status', 'won');
        } elseif ($this->statusFilter === 'lost') {
            $query->whereIn('status', ['rejected', 'lost']);
        } elseif ($this->statusFilter === 'active') {
            $query->whereIn('status', ['accepted', 'documentation_uploaded', 'offer_submitted']);
        } elseif ($this->statusFilter === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $recentTenders = $query->latest('updated_at')->paginate(10);

        foreach ($recentTenders as $tender) {
            $acceptedLotIds = is_array($tender->accepted_lots) ? $tender->accepted_lots : [];

            $tender->vrijednost_prihvacenih_lotova = !empty($acceptedLotIds)
                ? $tender->lots->whereIn('id', $acceptedLotIds)->sum('estimated_value')
                : 0;
        }

        return view('livewire.dashboard.index', [
            'recentTenders' => $recentTenders,
        ])->layout('layouts.default');
    }
}
