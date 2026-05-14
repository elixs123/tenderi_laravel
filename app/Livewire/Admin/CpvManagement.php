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

    public bool $showCpvBrowser = false;
    public string $browserSearch = '';
    public string $browserTypeFilter = 'all';
    public int $browserPage = 1;
    public int $browserPerPage = 100;

    public function render()
    {
        $users = User::where('role', 'employee')
            ->where(function ($q) {
                $q->where('first_name', 'ilike', "%{$this->userSearch}%")
                  ->orWhere('last_name', 'ilike', "%{$this->userSearch}%")
                  ->orWhere('email', 'ilike', "%{$this->userSearch}%");
            })
            ->get();

        $activeUser = $this->selectedUserId
            ? User::with('assignedCpvs')->find($this->selectedUserId)
            : null;

        $groupedAssigned = collect();
        if ($activeUser) {
            $groupedAssigned = $activeUser->assignedCpvs()
                ->where(function ($q) {
                    $q->where('code', 'ilike', "%{$this->localSearch}%")
                      ->orWhere('description', 'ilike', "%{$this->localSearch}%");
                })
                ->get()
                ->groupBy(fn($item) => $item->pivot->category_root_id);
        }

        $allCpvsBrowser = collect();
        $allCpvsFiltered = 0;

        if ($this->showCpvBrowser) {
            $browserQuery = CpvCode::when($this->browserSearch, function ($q) {
                    $q->where('code', 'like', $this->browserSearch . '%')
                      ->orWhere('description', 'ilike', '%' . $this->browserSearch . '%');
                })
                ->when($this->browserTypeFilter === 'root', fn($q) => $q->whereNull('root_id'))
                ->when($this->browserTypeFilter === 'child', fn($q) => $q->whereNotNull('root_id'))
                ->orderBy('code');

            $allCpvsFiltered = (clone $browserQuery)->count();
            $allCpvsBrowser  = $browserQuery->limit($this->browserPage * $this->browserPerPage)->get();
        }

        return view('livewire.admin.cpv-management', [
            'users'           => $users,
            'activeUser'      => $activeUser,
            'groupedAssigned' => $groupedAssigned,
            'rootCpvs'        => CpvCode::whereNull('root_id')->limit(20)->get(),
            'allCpvsTotal'    => CpvCode::count(),
            'allCpvsFiltered' => $allCpvsFiltered,
            'allCpvsBrowser'  => $allCpvsBrowser,
        ])->layout('layouts.default');
    }

    public function openCpvBrowser(): void
    {
        $this->showCpvBrowser = true;
        $this->browserSearch = '';
        $this->browserTypeFilter = 'all';
        $this->browserPage = 1;
    }

    public function closeCpvBrowser(): void
    {
        $this->showCpvBrowser = false;
        $this->browserPage = 1;
    }

    public function loadMoreCpvs(): void
    {
        $this->browserPage++;
    }

    public function updatedBrowserSearch(): void
    {
        $this->browserPage = 1;
    }

    public function updatedBrowserTypeFilter(): void
    {
        $this->browserPage = 1;
    }

    public function selectUser($id): void
    {
        $this->selectedUserId = $id;
        $this->localSearch = '';
        $this->openSectors = [];
    }

    public function toggleSector($id): void
    {
        if (in_array($id, $this->openSectors)) {
            $this->openSectors = array_diff($this->openSectors, [$id]);
        } else {
            $this->openSectors[] = $id;
        }
    }

    public function assignCpv($cpvId, $mode = 'single'): void
    {
        if (!$this->selectedUserId) return;

        $user = User::find($this->selectedUserId);
        $cpv  = CpvCode::find($cpvId);

        if (!$cpv) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'CPV kod nije pronađen.']);
            return;
        }

        if ($mode === 'single') {
            $exists = $user->assignedCpvs()->where('category_id', $cpvId)->exists();

            if ($exists) {
                $this->dispatch('notify', [
                    'type'    => 'warning',
                    'message' => "Korisnik već ima dodijeljen CPV kod: {$cpv->code} - {$cpv->description}.",
                ]);
                return;
            }

            $user->assignedCpvs()->attach($cpvId, [
                'category_root_id' => $cpv->root_id ?? $cpv->id,
                'is_main'          => 'true',
            ]);

            $this->dispatch('notify', ['type' => 'success', 'message' => "Kod {$cpv->code} uspješno dodan!"]);

        } elseif ($mode === 'all') {
            $rootId = $cpv->root_id ?? $cpv->id;

            $allIds      = CpvCode::where('root_id', $rootId)->pluck('id')->toArray();
            $existingIds = $user->assignedCpvs()->whereIn('category_id', $allIds)->pluck('category_id')->toArray();
            $newIds      = array_diff($allIds, $existingIds);

            if (empty($newIds)) {
                $this->dispatch('notify', [
                    'type'    => 'info',
                    'message' => 'Korisnik već posjeduje sve CPV kodove iz ovog sektora.',
                ]);
                return;
            }

            $attachData = [];
            foreach ($newIds as $id) {
                $attachData[$id] = [
                    'category_root_id' => $rootId,
                    'is_main'          => ($id == $rootId) ? 'true' : 'false',
                ];
            }

            $user->assignedCpvs()->attach($attachData);

            $msg = 'Dodano novih: ' . count($newIds) . ' kodova.';
            if (count($existingIds) > 0) {
                $msg .= ' Preskočeno: ' . count($existingIds) . ' (već dodijeljeni).';
            }

            $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
        }

        $this->cpvSearch    = '';
        $this->searchResults = [];
    }

    public function removeCpv($categoryId): void
    {
        $user = User::find($this->selectedUserId);
        $user->assignedCpvs()->detach($categoryId);

        $this->dispatch('notify', ['type' => 'info', 'message' => 'Kategorija uklonjena.']);
    }

    public function updatedCpvSearch(): void
    {
        if (strlen($this->cpvSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = CpvCode::where('code', 'like', $this->cpvSearch . '%')
            ->orWhere('description', 'ilike', '%' . $this->cpvSearch . '%')
            ->limit(10)
            ->get();
    }

    public function assignCpvToUser(int $cpvId, int $userId, string $mode = 'single'): void
    {
        $user = User::find($userId);
        $cpv  = CpvCode::find($cpvId);

        if (!$user || !$cpv) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Korisnik ili CPV kod nije pronađen.']);
            return;
        }

        if ($mode === 'single') {
            $exists = $user->assignedCpvs()->where('category_id', $cpvId)->exists();

            if ($exists) {
                $this->dispatch('notify', [
                    'type'    => 'warning',
                    'message' => "Korisnik već ima dodijeljen CPV kod: {$cpv->code} - {$cpv->description}.",
                ]);
                return;
            }

            $user->assignedCpvs()->attach($cpvId, [
                'category_root_id' => $cpv->root_id ?? $cpv->id,
                'is_main'          => 'true',
            ]);

            $this->dispatch('notify', ['type' => 'success', 'message' => "Kod {$cpv->code} uspješno dodan korisniku {$user->first_name} {$user->last_name}!"]);

        } elseif ($mode === 'all') {
            $rootId      = $cpv->root_id ?? $cpv->id;
            $allIds      = CpvCode::where('root_id', $rootId)->pluck('id')->toArray();
            $existingIds = $user->assignedCpvs()->whereIn('category_id', $allIds)->pluck('category_id')->toArray();
            $newIds      = array_diff($allIds, $existingIds);

            if (empty($newIds)) {
                $this->dispatch('notify', [
                    'type'    => 'info',
                    'message' => "Korisnik već posjeduje sve CPV kodove iz ovog sektora.",
                ]);
                return;
            }

            $attachData = [];
            foreach ($newIds as $id) {
                $attachData[$id] = [
                    'category_root_id' => $rootId,
                    'is_main'          => ($id == $rootId) ? 'true' : 'false',
                ];
            }

            $user->assignedCpvs()->attach($attachData);

            $msg = 'Dodano novih: ' . count($newIds) . ' kodova korisniku ' . $user->first_name . ' ' . $user->last_name . '.';
            if (count($existingIds) > 0) {
                $msg .= ' Preskočeno: ' . count($existingIds) . ' (već dodijeljeni).';
            }

            $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
        }

        if ($this->selectedUserId === $userId) {
            $this->dispatch('$refresh');
        }
    }
    public function getUsersForSelect(): array
    {
        return User::where('role', 'employee')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->toArray();
    }
}