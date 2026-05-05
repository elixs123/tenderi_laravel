<?php

namespace App\Livewire\Tenders;

use App\Models\MarketIntelligence;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class MarketRadar extends Component
{
    use WithPagination;

    public $selectedUser = '';
    public $filterType = 'all'; // 'all', 'ANNUNCIEMENT', 'CONTRACT', 'AWARD'
    public $search = '';

    protected $queryString = [
        'selectedUser' => ['except' => ''],
        'filterType' => ['except' => 'all'],
        'search' => ['except' => '']
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedUser() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }

    public function render()
    {
        $query = MarketIntelligence::query();

        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('authority_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('title', 'ilike', '%' . $this->search . '%')
                  ->orWhere('supplier_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('cpv_code', 'like', $this->search . '%');
            });
        }

        // 3. MASTER LOGIKA: Filter po CPV kodovima referenta
        if ($this->selectedUser) {
            $user = User::find($this->selectedUser);
            
            if ($user) {
                $userCpvCodes = DB::table('user_to_category')
                    ->where('user_id', $this->selectedUser)
                    ->pluck('category_id');

                $query->where(function($q) use ($userCpvCodes) {
                    $q->where(function($sub) use ($userCpvCodes) {
                        $sub->whereIn('type', ['CONTRACT', 'AWARD'])
                            ->where(function($cpvFilter) use ($userCpvCodes) {
                                foreach ($userCpvCodes as $code) {
                                    $cpvFilter->orWhere('cpv_code', 'like', $code . '%');
                                }
                            });
                    })
                    ->orWhere('type', 'ANNUNCIEMENT');
                });
            }
        }

        return view('livewire.tenders.market-radar', [
            'results' => $query->orderBy('event_date', 'desc')->paginate(15),
            'referents' => User::where('role', 'employee')->orderBy('first_name')->get()
        ])->layout('layouts.default');
    }
}