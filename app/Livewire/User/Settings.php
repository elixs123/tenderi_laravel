<?php 
namespace App\Livewire\User;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Procedure;

class Settings extends Component
{
    public array $selectedRegions = [];
    public array $selectedTypes = [];
    public string $contactPhone = '';
    public string $contactEmail = '';
    
    public array $availableRegions = [];
    public array $availableTypes = [];

    public function mount()
    {
        $this->availableRegions = Procedure::whereNotNull('contracting_authority_city_name')
            ->where('contracting_authority_city_name', '!=', '')
            ->distinct()
            ->orderBy('contracting_authority_city_name')
            ->pluck('contracting_authority_city_name')
            ->toArray();

        $this->availableTypes = Procedure::whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->toArray();

        $settings = Auth::user()->settings ?? [];
        $this->selectedRegions = $settings['regions'] ?? [];
        $this->selectedTypes = $settings['types'] ?? [];
        $this->contactPhone = $settings['contact']['phone'] ?? '';
        $this->contactEmail = $settings['contact']['email'] ?? '';
    }

    public function saveSettings()
    {
        Auth::user()->update([
            'settings' => [
                'regions' => $this->selectedRegions,
                'types' => $this->selectedTypes,
                'contact' => [
                    'phone' => $this->contactPhone,
                    'email' => $this->contactEmail,
                ]
            ]
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Postavke su uspješno sačuvane!']);
    }

    public function render()
    {
        return view('livewire.user.settings')->layout('layouts.default');
    }
}