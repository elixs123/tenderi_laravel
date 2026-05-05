<?php 

namespace App\Livewire\Tenders;

use App\Models\Procedure;
use App\Models\Lot;
use App\Models\TenderWorkflow;
use Illuminate\Container\Attributes\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListTenders extends Component
{
    use WithPagination;

    public $search = '';
    public $filter = 'all';
    public $selectedUser = ''; 
    public $selectedCity = ''; 

    protected $listeners = ['scroll-top' => 'handleScrollTop'];

    public function handleScrollTop() {
        // Handled via JS listener in Blade
    }

    public function updatingSearch() { 
        $this->resetPage(); 
        $this->dispatch('scroll-top'); 
    }
    
    public function updatingPage() { 
        $this->dispatch('scroll-top'); 
    }

    public function acceptTender($procedureId)
    {
        TenderWorkflow::updateOrCreate(
            ['procedure_id' => $procedureId],
            [
                'user_id' => auth()->id() ?? 1,
                'status' => 'accepted',
                'reason' => null
            ]
        );
    }

    public function rejectTender($procedureId, $reason)
    {
        if (empty($reason)) return;

        TenderWorkflow::updateOrCreate(
            ['procedure_id' => $procedureId],
            [
                'user_id' => auth()->id() ?? 1,
                'status' => 'rejected',
                'reason' => $reason
            ]
        );
    }

    public function markAsWon($workflowId)
    {
        $workflow = \App\Models\TenderWorkflow::find($workflowId);
        if($workflow) {
            $workflow->update(['status' => 'won']);
        }
    }

    public function markAsLost($workflowId)
    {
        $workflow = \App\Models\TenderWorkflow::find($workflowId);
        if($workflow) {
            $workflow->update(['status' => 'lost']);
        }
    }

    public function updatedSelectedUser() { $this->resetPage(); }
    public function updatedSelectedCity() { $this->resetPage(); }

    public function analyzeMarket($authorityId)
    {
        if (!$authorityId) return;

        try {
            $response = Http::timeout(30)->get("https://open.ejn.gov.ba/LotContracts", [
                '$filter' => "ContractingAuthorityId eq $authorityId",
                '$top' => 30
            ]);

            $lots = $response->json('value') ?? [];
            $analiza = collect();
            $ukupnaSuma = 0;

            foreach ($lots as $item) {
                $v = (float) ($item['Value'] ?? $item['ContractValueVatExcluded'] ?? 0);
                $rawNaziv = $item['ProcedureName'] ?? "OSTALO";
                
                $cistiNaziv = preg_replace('/NABAVKA|ZA POTREBE|POTREBE|JU DOM|DIREKTNI|SPORAZUM|OKVIRNI/i', '', $rawNaziv);
                $cistiNaziv = Str::upper(trim(preg_replace('/\d+\/\d+/', '', $cistiNaziv)));

                $match = DB::table('suppliers')->whereRaw('? LIKE CONCAT(\'%\', name, \'%\')', [$rawNaziv])->first();
                $prikaznoIme = $match ? Str::upper($match->name) : $cistiNaziv;

                if (!$analiza->has($prikaznoIme)) {
                    $analiza->put($prikaznoIme, [
                        'ime' => $prikaznoIme, 
                        'ukupno' => 0, 
                        'brojUgovora' => 0, 
                        'is_firma' => (bool)$match
                    ]);
                }

                $current = $analiza->get($prikaznoIme);
                $current['ukupno'] += $v;
                $current['brojUgovora'] += 1;
                $analiza->put($prikaznoIme, $current);
                $ukupnaSuma += $v;
            }

            $this->dispatch('openAnalysisModal', [
                'data' => $analiza->values()->sortByDesc('ukupno')->take(10)->values()->all(),
                'total' => $ukupnaSuma
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Greška pri analizi tržišta.']);
        }
    }

    public function render()
    {
        $query = Procedure::query()->with(['lots', 'workflow.user']);

        if(!auth()->check()) {
         $this->redirectRoute('login');

         return;
        }

        $currentUser = auth()->user();
        $isAdmin = $currentUser->role === 'admin';

        // 1. Osnovna pretraga (Naziv, broj, organ)
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                ->orWhere('number', 'ilike', '%' . $this->search . '%')
                ->orWhere('contracting_authority_name', 'ilike', '%' . $this->search . '%');
            });
        }

        // 2. Filter za današnje objave
        if ($this->filter === 'today') {
            $query->whereDate('announced', now()->toDateString());
        }

        if ($isAdmin) {
            if ($this->selectedCity) {
                $query->where('contracting_authority_city_name', $this->selectedCity);
            }

            if ($this->selectedUser) {
                $selectedEmployee = \App\Models\User::find($this->selectedUser);
                if ($selectedEmployee) {
                    $userCpvIds = $selectedEmployee->assignedCpvs()->pluck('category_id'); 
                    $query->whereIn('cpvcodeid', $userCpvIds);
                }
            }
        } else {
            $userSettings = $currentUser->settings ?? [];
            $userRegions = $userSettings['regions'] ?? [];
            $userTypes = $userSettings['types'] ?? [];

            if (!empty($userRegions)) {
                $query->whereIn('contracting_authority_city_name', $userRegions);
            }
            if (!empty($userTypes)) {
                $query->whereIn('type', $userTypes);
            }

            $query->where(function($q) use ($currentUser) {
                $q->whereHas('workflow', function($sq) use ($currentUser) {
                    $sq->where('user_id', $currentUser->id);
                })
                ->orWhere(function($sq) use ($currentUser) {
                    $userCpvIds = $currentUser->assignedCpvs()->pluck('category_id');
                    $sq->whereIn('cpvcodeid', $userCpvIds)
                    ->whereDoesntHave('workflow'); 
                });
            });
        }

        $statsQuery = clone $query;
        $currentTotalValue = Lot::whereIn('procedure_id', $statsQuery->pluck('id'))->sum('estimated_value');
        $currentAuthoritiesCount = (clone $statsQuery)->distinct('contracting_authority_id')->count();
        $todayCount = (clone $statsQuery)->whereDate('announced', now()->toDateString())->count();
        
        $cities = Procedure::whereNotNull('contracting_authority_city_name')->distinct()->pluck('contracting_authority_city_name');
        $referents = \App\Models\User::where('role', 'employee')->get();

        return view('livewire.tenders.list-tenders', [
            'tenders' => $query->withSum('lots', 'estimated_value')->orderBy('announced', 'desc')->paginate(10),
            'totalValue' => $currentTotalValue,
            'authoritiesCount' => $currentAuthoritiesCount,
            'todayCount' => $todayCount, 
            'cities' => $cities,
            'referents' => $referents,
            'isAdmin' => $isAdmin
        ])->layout('layouts.default');
    }
}