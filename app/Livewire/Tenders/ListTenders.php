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
use Illuminate\Http\Client\Pool;

class ListTenders extends Component
{
    use WithPagination;

    public $search = '';
    public $filter = 'all';
    public $sort = 'announced';
    public $selectedUser = '';
    public $selectedCity = '';

    public $acceptingProcedureId = null;
    public $availableLots = [];
    public $selectedLots = []; 
    public $isAcceptingWithoutLots = false; 

    public $wonTenderId = null;
    public $wonPrice = '';
    public $wonSupplier = '';

    public $viewingProcedure = null;

    protected $listeners = ['scroll-top' => 'handleScrollTop'];

    public function mount()
    {
        $procedureId = request('otvori');
        if ($procedureId) {
            $this->viewProcedure($procedureId);
        }
    }

    public function viewProcedure($procedureId)
    {
        $this->viewingProcedure = Procedure::with('lots')->find($procedureId);
        $this->dispatch('hide-sidebar');
    }

    public function closeDetailModal()
    {
        $this->viewingProcedure = null;
        $this->dispatch('show-sidebar');
    }

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

   public function saveWinner($workflowId, $supplier, $price)
    {
        $workflow = \App\Models\TenderWorkflow::find($workflowId);
        if ($workflow) {
            $workflow->update([
                'winner_supplier' => $supplier ?: null,
                'final_price'     => $price ?: null,
            ]);
        }
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Snimljeno!']);
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
        if (!$workflow) return;

        $workflow->update(['status' => 'lost']);

        try {
            // API ne vraća pobjednika direktno — mora se ići kroz aukcije (radi samo za e-aukcije)
            $auctionRes = Http::withoutVerifying()->timeout(10)->get('https://open.ejn.gov.ba/Auctions', [
                '$filter' => "ProcedureId eq {$workflow->procedure_id} and Status eq 'Completed'",
                '$top' => 1,
            ]);

            $auction = $auctionRes->json('value.0') ?? null;
            if (!$auction) return; // nije e-aukcija, ručni unos ostaje

            $partRes = Http::withoutVerifying()->timeout(10)->get('https://open.ejn.gov.ba/AuctionParticipations', [
                '$filter' => "AuctionId eq {$auction['Id']}",
            ]);

            $winner = collect($partRes->json('value') ?? [])->sortBy('FinalIndex')->first();
            if (!$winner) return;

            $finalPrice = $winner['FinalIndex'] ?? null;
            $sgId = $winner['SupplierGroupId'] ?? null;
            $supplierName = null;

            if ($sgId) {
                $linkRes = Http::withoutVerifying()->timeout(5)->get('https://open.ejn.gov.ba/SupplierGroupSupplierLinks', [
                    '$filter' => "SupplierGroupId eq {$sgId}",
                ]);
                $supplierId = collect($linkRes->json('value') ?? [])->sortByDesc('IsLead')->first()['SupplierId'] ?? null;

                if ($supplierId) {
                    $local = DB::table('suppliers')->where('supplier_id', $supplierId)->first();
                    $supplierName = $local ? $local->name : null;

                    if (!$supplierName) {
                        $supRes = Http::withoutVerifying()->timeout(5)->get("https://open.ejn.gov.ba/SuppliersBase/{$supplierId}");
                        $supplierName = $supRes->json('Name') ?? null;
                    }
                }
            }

            if ($supplierName || $finalPrice) {
                $workflow->update([
                    'winner_supplier' => Str::upper($supplierName ?? ''),
                    'final_price'     => $finalPrice,
                ]);
                $this->dispatch('notify', ['type' => 'info', 'message' => 'Pobjednik automatski povučen: ' . ($supplierName ?? 'N/A')]);
            }
        } catch (\Exception $e) {
            // tiho — ručni unos ostaje dostupan
        }
    }

    public function openAcceptModal($procedureId)
    {
        $procedure = Procedure::with('lots')->find($procedureId);
        
        if (!$procedure) return;

        $this->acceptingProcedureId = $procedureId;
        $this->availableLots = $procedure->lots->toArray();
        $this->selectedLots = []; 

        // Ako tender nema lotove, preskačemo modal i nudimo direktno prihvatanje
        if (count($this->availableLots) === 0) {
            $this->isAcceptingWithoutLots = true;
            $this->confirmAccept(); 
            return;
        }

        $this->isAcceptingWithoutLots = false;
        
        $this->dispatch('open-modal', 'accept-tender-modal'); 
    }

    public function acceptSingleLot($procedureId)
    {
        $procedure = Procedure::with('lots')->find($procedureId);
        
        if (!$procedure) return;

        $selectedLots = [];
        if ($procedure->lots->count() === 1) {
            $selectedLots = [$procedure->lots->first()->id];
        }

        TenderWorkflow::updateOrCreate(
            ['procedure_id' => $procedureId],
            [
                'user_id' => auth()->id(),
                'status' => 'accepted',
                'reason' => null,
                'accepted_lots' => $selectedLots
            ]
        );

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Uspješno preuzet tender!']);
    }

    public function confirmAccept()
    {
        if (!$this->isAcceptingWithoutLots && empty($this->selectedLots)) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Morate odabrati barem jedan lot!']);
            return;
        }

        TenderWorkflow::updateOrCreate(
            ['procedure_id' => $this->acceptingProcedureId],
            [
                'user_id' => auth()->id(),
                'status' => 'accepted',
                'reason' => null,
                'accepted_lots' => $this->selectedLots 
            ]
        );

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Uspješno preuzet tender/lotovi!']);
        $this->dispatch('close-modal', 'accept-tender-modal');
        
        $this->acceptingProcedureId = null;
        $this->availableLots = [];
        $this->selectedLots = [];
    }

    public function updatedSelectedUser() { $this->resetPage(); }
    public function updatedSelectedCity() { $this->resetPage(); }

    public function analyzeMarket($authorityId, $tenderId = null)
    {
        if (!$authorityId) return;

        try {
            $currentTender = Procedure::find($tenderId);
            $authorityName = $currentTender ? $currentTender->contracting_authority_name : 'Ugovorni Organ';

            // 1. Povlačimo ZAVRŠENE AUKCIJE za ovaj ugovorni organ
            $auctionsResponse = Http::withoutVerifying()
                ->timeout(15)
                ->get("https://open.ejn.gov.ba/Auctions", [
                    '$filter' => "ContractingAuthorityId eq $authorityId and Status eq 'Completed'",
                    '$orderby' => "StartDateTime desc",
                    '$top' => 15 
                ]);

            if (!$auctionsResponse->successful()) throw new \Exception('Auctions API nije dostupan');
            
            $auctions = collect($auctionsResponse->json('value') ?? []);
            if ($auctions->isEmpty()) {
                 $this->dispatch('notify', ['type' => 'info', 'message' => 'Nema završenih e-aukcija za ovaj organ.']);
                 return;
            }

            $competitors = collect();
            $recentContracts = [];
            $totalSuma = 0;

            foreach ($auctions as $auction) {
                $auctionId = $auction['Id'];
                $procName = $auction['ProcedureName'] ?? $auction['LotName'] ?? 'Nedefinisan naziv';
                $awardDate = $auction['StartDateTime'] ?? now()->toIso8601String(); 

                // 2. Povlačimo UČEŠĆA (Participations) za ovu aukciju
                $partRes = Http::withoutVerifying()->get("https://open.ejn.gov.ba/AuctionParticipations", [
                    '$filter' => "AuctionId eq $auctionId"
                ]);
                
                $participations = collect($partRes->json('value') ?? []);
                
                if ($participations->isNotEmpty()) {
                    // Pobjednik je onaj sa najnižom cijenom na kraju
                    $winnerPart = $participations->sortBy('FinalIndex')->first();
                    
                    $initial = (float) $winnerPart['InitialIndex'];
                    $final = (float) $winnerPart['FinalIndex']; 
                    $pad = $initial > 0 ? (($initial - $final) / $initial) * 100 : 0;
                    
                    $auctionData = [
                        'initial' => $initial,
                        'final' => $final,
                        'pad_procenat' => round($pad, 2)
                    ];
                    
                    // Već imamo SupplierGroupId od pobjednika aukcije!
                    $sgId = $winnerPart['SupplierGroupId'];
                    $supplierName = 'Nepoznat Dobavljač';
                    $actualSupplierId = null;

                    // 3. Idemo direktno u LINKS da nađemo pravi SupplierId (onog ko je Lead)
                    if ($sgId) {
                        $linksRes = Http::withoutVerifying()->get("https://open.ejn.gov.ba/SupplierGroupSupplierLinks", [
                            '$filter' => "SupplierGroupId eq $sgId"
                        ]);

                        if ($linksRes->successful() && !empty($linksRes->json('value'))) {
                            $linkData = collect($linksRes->json('value'))->sortByDesc('IsLead')->first();
                            $actualSupplierId = $linkData['SupplierId'];
                        }
                    }

                    // 4. Vadimo tačno ime iz tvoje LOKALNE BAZE!
                    if ($actualSupplierId) {
                        $localSupplier = \Illuminate\Support\Facades\DB::table('suppliers')
                            ->where('supplier_id', $actualSupplierId)
                            ->first();

                        if ($localSupplier) {
                            $supplierName = $localSupplier->name;
                        } else {
                            // FALLBACK API ako nije u lokalnoj bazi
                            $supRes = Http::withoutVerifying()->get("https://open.ejn.gov.ba/SuppliersBase/$actualSupplierId");
                            if ($supRes->successful()) {
                                $supplierName = $supRes->json('Name') ?? 'Nepoznat Dobavljač';
                            }
                        }
                    }

                    $supplierName = \Illuminate\Support\Str::upper($supplierName);

                    // 5. Dodajemo u Top Konkurenciju
                    if ($final > 0) {
                        if (!$competitors->has($supplierName)) {
                            $competitors->put($supplierName, ['supplier_name' => $supplierName, 'total_value' => 0, 'contracts_count' => 0]);
                        }
                        $current = $competitors->get($supplierName);
                        $current['total_value'] += $final;
                        $current['contracts_count'] += 1;
                        $competitors->put($supplierName, $current);
                        
                        $totalSuma += $final;
                    }

                    // 6. Dodajemo u desnu kolonu modala (Zadnji ugovori)
                    if (count($recentContracts) < 10) {
                        $recentContracts[] = [
                            'ProcedureName' => $procName,
                            'SupplierName' => $supplierName,
                            'ContractValue' => $final,
                            'AwardDate' => $awardDate,
                            'Auction' => $auctionData
                        ];
                    }
                }
            }

            $topCompetitors = $competitors->sortByDesc('total_value')->take(5)->values()->all();

            $this->dispatch('openAnalysisModal', [
                'authorityName' => \Illuminate\Support\Str::upper($authorityName),
                'topCompetitors' => $topCompetitors,
                'recentContracts' => $recentContracts,
                'totalValue' => $totalSuma,
                'totalContracts' => count($auctions) 
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Greška pri analizi: ' . $e->getMessage()]);
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

        // 1. Osnovna pretraga (Naziv, broj, notice broj, organ)
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                ->orWhere('number', 'ilike', '%' . $this->search . '%')
                ->orWhere('notice_number', 'ilike', '%' . $this->search . '%')
                ->orWhere('contracting_authority_name', 'ilike', '%' . $this->search . '%');
            });
        }

        // 2. Filteri
        if ($this->filter === 'today') {
            $query->whereDate('announced', now()->toDateString());
        } elseif ($this->filter === 'week') {
            $query->whereHas('lots', function ($q) {
                $q->whereBetween('application_deadline_date_time', [now(), now()->addDays(7)]);
            });
        }

        if ($isAdmin) {
            if ($this->selectedCity) {
                $query->where('contracting_authority_city_name', $this->selectedCity);
            }

            if ($this->selectedUser) {
                $selectedEmployee = \App\Models\User::find($this->selectedUser);
                if ($selectedEmployee) {
                    $userCpvIds = $selectedEmployee->assignedCpvs()->pluck('category_id');
                    $userRootIds = \DB::table('user_to_category')
                        ->where('user_id', $selectedEmployee->id)
                        ->whereNotNull('category_root_id')
                        ->distinct()
                        ->pluck('category_root_id');
                    $query->whereIn('cpvcodeid', $userCpvIds->merge($userRootIds)->unique());
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
                    $userRootIds = \DB::table('user_to_category')
                        ->where('user_id', $currentUser->id)
                        ->whereNotNull('category_root_id')
                        ->distinct()
                        ->pluck('category_root_id');
                    $allCpvIds = $userCpvIds->merge($userRootIds)->unique();
                    $sq->whereIn('cpvcodeid', $allCpvIds)
                    ->whereDoesntHave('workflow', fn($wq) => $wq->whereNotIn('status', ['rejected', 'lost']));
                });
            });
        }

        $statsQuery = clone $query;
        $currentTotalValue = Lot::whereIn('procedure_id', $statsQuery->pluck('id'))->sum('estimated_value');
        $currentAuthoritiesCount = (clone $statsQuery)->distinct('contracting_authority_id')->count();
        $todayCount = (clone $statsQuery)->whereDate('announced', now()->toDateString())->count();
        
        $cities = Procedure::whereNotNull('contracting_authority_city_name')->distinct()->pluck('contracting_authority_city_name');
        $referents = \App\Models\User::where('role', 'employee')->get();

        $orderedQuery = $query->withSum('lots', 'estimated_value');

        if ($this->sort === 'deadline') {
            $orderedQuery->orderByRaw('(SELECT MIN(application_deadline_date_time) FROM lots WHERE lots.procedure_id = procedures.id) ASC NULLS LAST');
        } else {
            $orderedQuery->orderBy('announced', 'desc');
        }

        return view('livewire.tenders.list-tenders', [
            'tenders' => $orderedQuery->paginate(10),
            'totalValue' => $currentTotalValue,
            'authoritiesCount' => $currentAuthoritiesCount,
            'todayCount' => $todayCount, 
            'cities' => $cities,
            'referents' => $referents,
            'isAdmin' => $isAdmin
        ])->layout('layouts.default');
    }
}