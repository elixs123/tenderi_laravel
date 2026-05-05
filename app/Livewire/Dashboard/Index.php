<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination; // 1. Dodan trait
use App\Models\TenderWorkflow;
use App\Models\User;

class Index extends Component
{
    use WithPagination; // 2. Aktiviran trait

    public $selectedUser = ''; 
    public $users = [];
    
    public $stats = [
        'ukupno' => 0,
        'u_pripremi' => 0,
        'na_cekanju' => 0,
        'dobijeni' => 0,
        'izgubljeni' => 0,
        'win_rate' => 0,
        'ukupna_vrijednost_dobijenih' => 0
    ];

    // Izbrisali smo public $recentTenders = []; jer paginacija ide kroz render()

    public function mount()
    {
        $this->users = User::all();
        $this->loadStats();
    }

    public function updatedSelectedUser()
    {
        $this->resetPage(); // Vraća na prvu stranicu kad se promijeni filter
        $this->loadStats();
    }

    public function loadStats()
    {
        $query = TenderWorkflow::query();

        if (!empty($this->selectedUser) && $this->selectedUser !== 'All') {
            $query->where('user_id', $this->selectedUser);
        }

        $this->stats['ukupno'] = (clone $query)->count();
        $this->stats['u_pripremi'] = (clone $query)->whereIn('status', ['new', 'documentation_uploaded'])->count();
        $this->stats['na_cekanju'] = (clone $query)->where('status', 'offer_submitted')->count();
        $this->stats['dobijeni'] = (clone $query)->where('status', 'accepted')->count();
        $this->stats['izgubljeni'] = (clone $query)->where('status', 'rejected')->count();

        $zavrseni = $this->stats['dobijeni'] + $this->stats['izgubljeni'];
        if ($zavrseni > 0) {
            $this->stats['win_rate'] = round(($this->stats['dobijeni'] / $zavrseni) * 100, 1);
        } else {
            $this->stats['win_rate'] = 0;
        }

        // Vraćeno tvoje originalno rješenje koje radi!
        $this->stats['ukupna_vrijednost_dobijenih'] = (clone $query)
            ->where('status', 'accepted')
            ->withSum('lots', 'estimated_value')
            ->get()
            ->sum('lots_sum_estimated_value');
    }

    public function render()
    {
        $query = TenderWorkflow::query();

        if ($this->selectedUser !== '') {
            $query->where('user_id', $this->selectedUser);
        }

        // Tvoj originalni upit, samo sa paginate(10) umjesto take(10)->get()
        $recentTenders = (clone $query)
            ->with(['user', 'procedure']) 
            ->withSum('lots as ukupna_vrijednost', 'estimated_value')
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.dashboard.index', [
            'recentTenders' => $recentTenders
        ])->layout('layouts.default');
    }
}