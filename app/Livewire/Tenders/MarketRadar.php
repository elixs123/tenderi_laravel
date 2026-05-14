<?php

namespace App\Livewire\Tenders;

use App\Models\User;
use App\Services\EjnApiService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MarketRadar extends Component
{
    public string $activeTab    = 'procedures';
    public string $selectedUser = '';
    public string $search       = '';
    public int    $currentPage  = 1;
    public int    $perPage      = 15;

    protected $queryString = [
        'activeTab'    => ['except' => 'procedures'],
        'selectedUser' => ['except' => ''],
        'search'       => ['except' => ''],
        'currentPage'  => ['except' => 1],
    ];

    public function updatingSearch(): void       { $this->currentPage = 1; }
    public function updatingSelectedUser(): void { $this->currentPage = 1; }

    public function setTab(string $tab): void
    {
        $this->activeTab   = $tab;
        $this->currentPage = 1;
        $this->search      = '';
    }

    public function nextPage(): void { $this->currentPage++; }
    public function prevPage(): void { if ($this->currentPage > 1) $this->currentPage--; }

    private function getUserCpvCodes(): array
    {
        if (!$this->selectedUser) return [];

        return DB::table('user_to_category')
            ->join('cpvcodes', 'user_to_category.category_id', '=', 'cpvcodes.id') 
            ->where('user_to_category.user_id', $this->selectedUser)
            ->pluck('cpvcodes.code')
            ->toArray();
    }

    public function render(EjnApiService $api)
    {
        $cpvCodes = $this->getUserCpvCodes();
        $skip     = ($this->currentPage - 1) * $this->perPage;

        $items = match($this->activeTab) {
            'procedures' => $api->getProcedures($cpvCodes, $this->search, $this->perPage, $skip),
            'awards'     => $api->getAwards($cpvCodes, $this->search, $this->perPage, $skip),
            'planned'    => $api->getPlannedProcurements($cpvCodes, $this->search, $this->perPage, $skip),
            'pi'         => $api->getPiNotices($cpvCodes, $this->search, $this->perPage, $skip),
            'notices'    => $api->getAwardNotices($cpvCodes, $this->search, $this->perPage, $skip),
            default      => [],
        };

        return view('livewire.tenders.market-radar', [
            'items'     => $items,
            'cpvCodes'  => $cpvCodes,
            'referents' => User::where('role', 'employee')->orderBy('first_name')->get(),
            'hasPrev'   => $this->currentPage > 1,
            'hasNext'   => count($items) >= $this->perPage,
        ])->layout('layouts.default');
    }
}