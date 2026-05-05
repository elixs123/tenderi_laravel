<?php 
namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\CpvCode; 
use App\Models\Category;
use Livewire\Component;

class CpvManagement extends Component
{
    public $userSearch = '';
    public $cpvSearch = '';
    public $localSearch = '';
    public $selectedUserId = null;
    public $searchResults = [];
    public $openSectors = [];

   
    public function render()
    {
        $users = User::where('role', 'employee')->where(function($q) {
                $q->where('first_name', 'ilike', "%{$this->userSearch}%")
                  ->orWhere('last_name', 'ilike', "%{$this->userSearch}%")
                  ->orWhere('email', 'ilike', "%{$this->userSearch}%");
                  
            })->get();

        $activeUser = $this->selectedUserId ? User::with('assignedCpvs')->find($this->selectedUserId) : null;
        
        $groupedAssigned = collect();
        if ($activeUser) {
            $groupedAssigned = $activeUser->assignedCpvs()
                ->where(function($q) {
                    // $q->where('name', 'ilike', "%{$this->localSearch}%")
                    // ->orWhere('code', 'like', "%{$this->localSearch}%");
                    $q->where('code', 'ilike', "%{$this->localSearch}%");
                })
                ->get()
                ->groupBy(function($item) {
                    return $item->pivot->category_root_id; 
                });
        }

        return view('livewire.admin.cpv-management', [
            'users' => $users,
            'activeUser' => $activeUser,
            'groupedAssigned' => $groupedAssigned,
            'rootCpvs' => CpvCode::whereNull('root_id')->limit(20)->get()
        ])->layout('layouts.default');
    }

    public function selectUser($id)
    {
        $this->selectedUserId = $id;
        $this->localSearch = '';
        $this->openSectors = [];
    }

    public function toggleSector($id)
    {
        if (in_array($id, $this->openSectors)) {
            $this->openSectors = array_diff($this->openSectors, [$id]);
        } else {
            $this->openSectors[] = $id;
        }
    }

    public function assignCpv($cpvId, $mode = 'single')
    {
        if (!$this->selectedUserId) return;

        $user = User::find($this->selectedUserId);
        $cpv = CpvCode::find($cpvId);

        if (!$cpv) {
            $this->dispatch('notify', ['type' => 'error', 'message' => "CPV kod nije pronađen."]);
            return;
        }

        if ($mode === 'single') {
            $exists = $user->assignedCpvs()->where('category_id', $cpvId)->exists();

            if ($exists) {
                $this->dispatch('notify', [
                    'type' => 'warning', 
                    'message' => "Korisnik već ima dodijeljen cpv kod: {$cpv->code} - {$cpv->description}."
                ]);
                return;
            }

            $user->assignedCpvs()->attach($cpvId, [
                'category_root_id' => $cpv->root_id ?? $cpv->id,
                'is_main' => 'true'
            ]);

            $this->dispatch('notify', ['type' => 'success', 'message' => "Kod {$cpv->code} uspješno dodan!"]);
        } 

        else if ($mode === 'all') {
            $rootId = $cpv->root_id ?? $cpv->id;
            
            $allInSector = CpvCode::where('root_id', $rootId)->get();
            $allIds = $allInSector->pluck('id')->toArray();

            $existingIds = $user->assignedCpvs()
                ->whereIn('category_id', $allIds)
                ->pluck('category_id')
                ->toArray();

            $newIds = array_diff($allIds, $existingIds);

            if (empty($newIds)) {
                $this->dispatch('notify', [
                    'type' => 'info', 
                    'message' => "Korisnik već posjeduje sve cpv kodove iz ovog sektora."
                ]);
                return;
            }

            // Priprema podataka za masovni attach
            $attachData = [];
            foreach ($newIds as $id) {
                $attachData[$id] = [
                    'category_root_id' => $rootId,
                    'is_main' => ($id == $rootId) ? 'true' : 'false'
                ];
            }

            $user->assignedCpvs()->attach($attachData);

            // Formiranje poruke
            $addedCount = count($newIds);
            $skippedCount = count($existingIds);
            
            $msg = "Dodano novih: {$addedCount} kodova.";
            if ($skippedCount > 0) {
                $msg .= " Preskočeno: {$skippedCount} (već dodijeljeni).";
            }

            $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
        }

        $this->cpvSearch = '';
        $this->searchResults = [];
    }

    public function removeCpv($categoryId)
    {
        $user = User::find($this->selectedUserId);
        $user->assignedCpvs()->detach($categoryId);
        
        $this->dispatch('notify', ['type' => 'info', 'message' => "Kategorija uklonjena."]);
    }

    public function updatedCpvSearch()
    {
        if (strlen($this->cpvSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = CpvCode::where('code', 'like', $this->cpvSearch . '%')
            ->orWhere('description', 'ilike', '%' . $this->cpvSearch . '%') // ilike za case-insensitive na Postgresu
            ->limit(10)
            ->get();
    }
}