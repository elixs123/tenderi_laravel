<div class="flex h-screen bg-slate-50 dark:bg-[#0f172a] text-sm text-slate-800 dark:text-slate-100 overflow-hidden transition-colors duration-300">
    <style>
        .rotate-180 { transform: rotate(180deg); }
        .user-active { background: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6; }
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scroll::-webkit-scrollbar-thumb { background: #334155; }
    </style>

    {{-- SIDEBAR: Users --}}
    <aside class="w-80 border-r border-slate-200 dark:border-slate-800 flex flex-col bg-white dark:bg-slate-900/20 transition-colors duration-300">
        <div class="p-6">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                <input wire:model.live.debounce.300ms="userSearch" type="text" placeholder="Pretraži uposlenike..." 
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl py-2.5 pl-10 pr-4 text-xs font-bold focus:border-blue-500 outline-none transition text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500">
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto custom-scroll">
            @foreach($users as $user)
                <div wire:click="selectUser({{ $user->id }})" 
                     class="p-5 cursor-pointer border-b border-slate-100 dark:border-slate-800/50 flex items-center gap-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/40 {{ $selectedUserId == $user->id ? 'user-active' : '' }}">
                    <div class="w-10 h-10 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-transparent rounded-lg flex items-center justify-center font-black text-slate-600 dark:text-white uppercase shadow-inner">
                        {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black truncate text-slate-800 dark:text-white">{{ $user->first_name }} {{ $user->last_name }}</p>
                        <p class="text-[10px] text-slate-500 font-bold truncate uppercase tracking-tighter">{{ $user->email }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 flex flex-col min-w-0">
        <header class="h-20 flex items-center justify-between px-8 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/50 backdrop-blur-md transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="bg-blue-100 dark:bg-blue-500/10 p-2.5 rounded-xl text-blue-600 dark:text-blue-500">
                    <i class="fa-solid fa-users-gear text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black tracking-tight uppercase italic text-slate-800 dark:text-white">Admin <span class="text-blue-600 dark:text-blue-500">Control</span></h1>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-widest">CPV Upravljanje</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-base font-mono font-black tracking-widest text-slate-800 dark:text-white" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('en-GB'), 1000)" x-text="time"></p>
                <span class="text-[10px] text-emerald-600 dark:text-green-500 font-black uppercase">Sistem Aktivan</span>
            </div>
        </header>

        <div class="flex-1 p-8 overflow-y-auto bg-slate-50/50 dark:bg-[#020617] custom-scroll transition-colors duration-300">
            <div class="max-w-7xl mx-auto">
                @if($activeUser)
                    <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 mb-8 flex items-center gap-6 shadow-sm dark:shadow-2xl transition-colors duration-300">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 dark:from-blue-600 dark:to-blue-800 rounded-2xl flex items-center justify-center text-2xl shadow-lg text-white">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black uppercase italic text-slate-800 dark:text-white">{{ $activeUser->first_name }} <span class="text-blue-600 dark:text-blue-500">{{ $activeUser->last_name }}</span></h2>
                            <p class="text-slate-500 font-bold text-xs uppercase tracking-widest mt-1">Dodijeljeno: <span class="text-blue-600 dark:text-blue-500">{{ $activeUser->assignedCpvs->count() }} kodova</span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                        {{-- SEARCH & QUICK ASSIGN --}}
                        <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 space-y-8 min-h-[600px] flex flex-col shadow-sm dark:shadow-none transition-colors duration-300">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4 block">Pretraga baze</label>
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-blue-500"></i>
                                    <input wire:model.live.debounce.400ms="cpvSearch" type="text" placeholder="Upiši naziv ili CPV kod..." 
                                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl py-4 pl-12 pr-4 font-bold text-slate-800 dark:text-white outline-none focus:border-blue-500 transition shadow-sm dark:shadow-2xl placeholder-slate-400 dark:placeholder-slate-500">
                                    
                                    @if(count($searchResults) > 0)
                                        <div class="absolute top-full left-0 right-0 mt-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl dark:shadow-2xl z-[100] max-h-80 overflow-y-auto p-2 backdrop-blur-xl custom-scroll">
                                            @foreach($searchResults as $item)
                                                <div onclick="confirmAssign('{{ $item->id }}', '{{ addslashes($item->description) }}')" 
                                                     class="flex items-center justify-between p-4 hover:bg-blue-50 dark:hover:bg-blue-600/20 cursor-pointer border-b border-slate-100 dark:border-white/5 last:border-0 rounded-xl transition group">
                                                    <div class="min-w-0 pr-4">
                                                        <p class="text-sm font-black text-blue-600 dark:text-blue-400">{{ $item->code }}</p>
                                                        <p class="text-[10px] text-slate-700 dark:text-white font-bold uppercase truncate">{{ $item->description }}</p>
                                                    </div>
                                                    <i class="fa-solid fa-plus text-blue-600 dark:text-blue-500 opacity-0 group-hover:opacity-100 transition"></i>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex-1 flex flex-col min-h-0">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Brza dodjela (Root sektori)</p>
                                <div class="flex-1 overflow-y-auto space-y-3 pr-2 custom-scroll">
                                    @foreach($rootCpvs as $root)
                                        <button onclick="confirmAssign('{{ $root->id }}', '{{ addslashes($root->description) }}')" 
                                                class="w-full bg-slate-50/80 dark:bg-slate-950/50 hover:bg-white dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:border-blue-400 dark:hover:border-blue-500/50 p-4 rounded-2xl transition text-left flex items-center gap-4 group">
                                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-500 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500/20 transition-colors">
                                                <i class="fa-solid fa-layer-group text-sm"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="text-[10px] font-black text-slate-700 dark:text-slate-200 uppercase italic line-clamp-1">{{ $root->description }}</span>
                                                <span class="text-[9px] mt-1 bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded font-mono font-black border border-slate-200 dark:border-white/5 inline-block">{{ $root->code }}</span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- ASSIGNED LIST --}}
                        <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 flex flex-col min-h-[600px] shadow-sm dark:shadow-none transition-colors duration-300">
                            <h3 class="text-[10px] font-black uppercase text-slate-500 mb-6 tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-box-archive text-blue-500"></i> Aktivni kodovi korisnika
                            </h3>
                            
                            <div class="mb-4 relative">
                                <i class="fa-solid fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                                <input wire:model.live.debounce.300ms="localSearch" type="text" placeholder="Filter dodijeljenih..." 
                                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl py-3 pl-11 text-xs font-bold outline-none focus:border-blue-500 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-all">
                            </div>

                            <div class="space-y-3 overflow-y-auto custom-scroll flex-1 pr-2">
                                @forelse($groupedAssigned as $rootId => $codes)
                                    <div class="bg-slate-50 dark:bg-slate-950/30 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden transition-colors">
                                        <div wire:click="toggleSector('{{ $rootId }}')" class="p-4 flex items-center justify-between cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <i class="fa-solid fa-folder text-blue-500"></i>
                                                <p class="text-[10px] font-black text-slate-800 dark:text-white uppercase truncate">{{ $codes->first()->root_description ?? 'Sektor '.$rootId }}</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="bg-blue-100 dark:bg-blue-600/20 text-blue-700 dark:text-blue-400 text-[9px] px-2 py-0.5 rounded-full font-black">{{ $codes->count() }}</span>
                                                <i class="fa-solid fa-chevron-down text-slate-400 dark:text-slate-600 transition-transform {{ in_array($rootId, $openSectors) ? 'rotate-180' : '' }}"></i>
                                            </div>
                                        </div>

                                        @if(in_array($rootId, $openSectors))
                                            <div class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 divide-y divide-slate-100 dark:divide-slate-800/50">
                                                @foreach($codes as $cpv)
                                                    <div class="p-4 flex items-center justify-between hover:bg-blue-50 dark:hover:bg-blue-600/5 transition group">
                                                        <div class="min-w-0 pr-4">
                                                            <p class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase leading-tight">{{ $cpv->description }}</p>
                                                            <span class="text-[9px] font-mono text-slate-500">{{ $cpv->code }}</span>
                                                        </div>
                                                        <button wire:click="removeCpv({{ $cpv->id }})" class="text-slate-400 dark:text-slate-600 hover:text-rose-500 p-2 transition">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="h-40 flex items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-transparent opacity-50 dark:opacity-30">
                                        <p class="font-black uppercase tracking-widest text-[10px] text-slate-500 dark:text-slate-100">Prazna lista</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    <div class="h-[600px] flex flex-col items-center justify-center text-slate-400 dark:text-slate-600 bg-white dark:bg-transparent border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm dark:shadow-none transition-colors duration-300">
                        <i class="fa-solid fa-user-plus text-6xl mb-4 text-slate-300 dark:text-slate-600"></i>
                        <p class="text-base font-black uppercase tracking-[0.3em]">Odaberite korisnika</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <script>
        // Pametna SweetAlert funkcija koja čita temu iz dokumenta
        window.confirmAssign = (id, name) => {
            const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');

            Swal.fire({
                title: 'Potvrda dodjele',
                html: `Želite li korisniku dodijeliti kod:<br><b class="${isDark ? 'text-blue-400' : 'text-blue-600'}">${name}</b>?`,
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Samo ovaj',
                denyButtonText: 'Cijeli sektor',
                confirmButtonColor: '#3b82f6',
                denyButtonColor: '#10b981',
                background: isDark ? '#0f172a' : '#ffffff',
                color: isDark ? '#fff' : '#1e293b',
                customClass: { 
                    popup: `border ${isDark ? 'border-slate-700' : 'border-slate-200 shadow-xl'} rounded-3xl` 
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.assignCpv(id, 'single');
                } else if (result.isDenied) {
                    @this.assignCpv(id, 'all');
                }
            });
        }

        window.addEventListener('notify', event => {
            const isDark = document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');

            Swal.fire({
                title: 'Sistem',
                text: event.detail[0].message,
                icon: event.detail[0].type,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                background: isDark ? '#1e293b' : '#ffffff',
                color: isDark ? '#fff' : '#1e293b',
                customClass: { 
                    popup: `shadow-lg border ${isDark ? 'border-slate-700' : 'border-slate-100'}` 
                }
            });
        });
    </script>
</div>