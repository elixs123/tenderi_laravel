<div id="main-layout" class="flex-1 h-full overflow-y-auto p-4 lg:p-6 bg-slate-50 dark:bg-[#0f172a] custom-scrollbar scroll-smooth relative transition-colors duration-300">
    <style>
        .tender-card { 
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s ease-in-out;
        }
        .dark .tender-card { 
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid rgba(51, 65, 85, 0.5);
            box-shadow: none;
        }
        
        .label-sm { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .dark .label-sm { color: #475569; }
        .data-mono { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .dark .stats-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(51, 65, 85, 0.5);
        }
        
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    </style>

    {{-- SCANNING LOADER --}}
    <div wire:loading.flex wire:target="analyzeMarket" class="fixed inset-0 z-[150] bg-white/90 dark:bg-[#0f172a]/95 backdrop-blur-sm flex flex-col items-center justify-center">
        <div class="relative flex items-center justify-center">
            <div class="absolute w-24 h-24 border-2 border-rose-500/20 rounded-full"></div>
            <div class="absolute w-24 h-24 border-t-2 border-rose-500 rounded-full animate-spin"></div>
            <i data-lucide="crosshair" class="text-rose-500 w-8 h-8 animate-pulse"></i>
        </div>
        <p class="mt-8 text-rose-500 font-black uppercase tracking-[0.4em] text-[10px]">Market Scan Active_</p>
    </div>

    {{-- STATS GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['L' => 'Tržišni Potencijal', 'V' => number_format($totalValue, 0, ',', '.') . ' KM', 'I' => 'trending-up', 'C' => 'text-blue-600 dark:text-blue-400', 'B' => 'border-b-blue-500'],
            ['L' => 'Aktivni Tenderi', 'V' => $tenders->total(), 'I' => 'clipboard-check', 'C' => 'text-emerald-600 dark:text-emerald-400', 'B' => 'border-b-emerald-500'],
            ['L' => 'Ugovorni Organi', 'V' => $authoritiesCount, 'I' => 'building-2', 'C' => 'text-amber-600 dark:text-amber-400', 'B' => 'border-b-amber-500'],
            ['L' => 'Danas Objavljeno', 'V' => '12', 'I' => 'zap', 'C' => 'text-rose-600 dark:text-rose-400', 'B' => 'border-b-rose-500']
        ] as $stat)
        <div class="stats-card p-5 rounded-2xl transition-all hover:translate-y-[-2px] border-b-2 {{ $stat['B'] }}">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800/50">
                    <i data-lucide="{{ $stat['I'] }}" class="w-5 h-5 {{ $stat['C'] }}"></i>
                </div>
                <span class="label-sm opacity-60">{{ $stat['L'] }}</span>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white data-mono tracking-tight">{{ $stat['V'] }}</p>
        </div>
        @endforeach
    </div>

<div class="space-y-4 mb-8">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Search Input --}}
            <div class="relative flex-1 group">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Traži po nazivu, organu ili broju postupka..." 
                    class="w-full bg-white dark:bg-[#1e293b]/60 border border-slate-200 dark:border-slate-800 rounded-2xl py-4 pl-12 pr-4 text-sm text-slate-900 dark:text-white focus:border-blue-600 outline-none transition-all shadow-sm">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-500"></i>
            </div>

            {{-- Filter Dugmad (Svi/Danas) --}}
            <div class="flex bg-white dark:bg-slate-900 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm shrink-0">
                <button wire:click="$set('filter', 'all')" class="px-6 py-2 text-[10px] font-black uppercase rounded-lg transition-all {{ $filter === 'all' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:text-blue-500' }}">Svi</button>
                <button wire:click="$set('filter', 'today')" class="px-6 py-2 text-[10px] font-black uppercase rounded-lg transition-all {{ $filter === 'today' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:text-blue-500' }}">Današnji</button>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
            {{-- DODATNI FILTERI (Vidljivo samo adminu) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Filter po gradu --}}
                <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 p-2 rounded-2xl flex items-center gap-3 shadow-sm transition-all hover:border-emerald-500/30">
                    <div class="w-10 h-10 bg-emerald-500/10 text-emerald-500 rounded-xl flex items-center justify-center border border-emerald-500/20">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1 pr-3">
                        <p class="label-sm !mb-0 text-[8px] opacity-70">Region / Lokacija</p>
                        <select wire:model.live="selectedCity" class="w-full bg-transparent text-xs font-bold text-slate-900 dark:text-white outline-none appearance-none cursor-pointer">
                            <option value="">Sve lokacije u BiH</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter po referentu --}}
                <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 p-2 rounded-2xl flex items-center gap-3 shadow-sm transition-all hover:border-indigo-500/30">
                    <div class="w-10 h-10 bg-indigo-500/10 text-indigo-500 rounded-xl flex items-center justify-center border border-indigo-500/20">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1 pr-3">
                        <p class="label-sm !mb-0 text-[8px] opacity-70">Referent</p>
                        <select wire:model.live="selectedUser" class="w-full bg-transparent text-xs font-bold text-slate-900 dark:text-white outline-none appearance-none cursor-pointer">
                            <option value="">Svi uposlenici</option>
                            @foreach($referents as $ref)
                                <option value="{{ $ref->id }}">{{ $ref->first_name }} {{ $ref->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @else
            {{-- INFO BANER ZA ZAPOSLENE --}}
            <div class="flex items-center gap-2 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 rounded-xl">
                <i data-lucide="info" class="w-4 h-4 text-blue-500"></i>
                <p class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">
                    Prikazuju se tenderi prema vašim personalizovanim postavkama.
                </p>
            </div>
        @endif
    </div>
    {{-- TENDER FEED --}}
    <div class="space-y-6">
        @foreach($tenders as $tender)
        
        @php
            // NOVA, PRECIZNA LOGIKA STATUSA
            $statusInfo = match($tender->workflow?->status) {
                'accepted' => ['color' => 'blue', 'bg' => 'bg-blue-50 dark:bg-blue-500/5', 'border' => 'border-blue-500', 'text' => 'text-blue-600 dark:text-blue-500', 'label' => 'PRIHVAĆEN'],
                'documentation_uploaded' => ['color' => 'amber', 'bg' => 'bg-amber-50 dark:bg-amber-500/5', 'border' => 'border-amber-500', 'text' => 'text-amber-600 dark:text-amber-500', 'label' => 'U PROCESU'],
                'offer_submitted', 'completed' => ['color' => 'indigo', 'bg' => 'bg-indigo-50 dark:bg-indigo-500/5', 'border' => 'border-indigo-400', 'text' => 'text-indigo-600 dark:text-indigo-400', 'label' => 'PREDAT'],
                'won' => ['color' => 'emerald', 'bg' => 'bg-emerald-50 dark:bg-emerald-500/5', 'border' => 'border-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-500', 'label' => 'DOBIJEN'],
                'lost' => ['color' => 'rose', 'bg' => 'bg-rose-50 dark:bg-rose-500/5', 'border' => 'border-rose-500', 'text' => 'text-rose-600 dark:text-rose-500', 'label' => 'IZGUBLJEN'],
                'rejected' => ['color' => 'slate', 'bg' => 'bg-slate-100 dark:bg-slate-800/50', 'border' => 'border-slate-500', 'text' => 'text-slate-500 dark:text-slate-400', 'label' => 'ODBIJEN'],
                default => ['color' => 'slate', 'bg' => 'bg-white dark:bg-transparent', 'border' => 'border-slate-300 dark:border-slate-700', 'text' => 'text-slate-400 dark:text-slate-500', 'label' => 'NOVO']
            };
        @endphp

        <article wire:key="tender-{{ $tender->id }}" 
            class="tender-card relative rounded-2xl overflow-hidden transition-all duration-300 flex border-l-0 {{ $statusInfo['bg'] }}">
            
            {{-- VERTIKALNI STATUS BANNER --}}
            <div class="w-8 shrink-0 flex items-center justify-center border-r transition-colors duration-300 border-slate-200 dark:border-slate-800/50 {{ $statusInfo['bg'] }}">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-{{ $statusInfo['color'] }}-500"></div>
                <span class="transform -rotate-90 whitespace-nowrap text-[8px] font-black tracking-[0.3em] uppercase {{ $statusInfo['text'] }}">
                    {{ $statusInfo['label'] }}
                </span>
            </div>

            <div class="p-6 lg:p-8 flex-1 min-w-0">
                <div class="flex flex-col lg:flex-row justify-between gap-8">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[8px] font-black px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700 uppercase">{{ $tender->type }}</span>
                            <span class="text-slate-400 dark:text-slate-600 text-[10px] font-bold data-mono">#{{ $tender->number }}</span>
                            
                            <span class="text-[9px] font-bold text-slate-400 dark:text-slate-600 ml-auto flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                SISTEM: {{ $tender->created_at->format('d.m.Y H:i') }}
                            </span>
                        </div>

                        <h2 class="text-xl font-black text-slate-900 dark:text-white leading-tight mb-6 uppercase tracking-tight">{{ $tender->name }}</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            {{-- PODACI O ORGANU I KONTAKTU --}}
                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="w-8 h-8 bg-slate-100 dark:bg-slate-800/50 rounded flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700"><i data-lucide="landmark" class="w-4 h-4 text-blue-500 dark:text-blue-400"></i></div>
                                    <div class="truncate">
                                        <p class="label-sm">Ugovorni Organ</p>
                                        <p class="text-[11px] font-bold text-slate-700 dark:text-slate-200 truncate">{{ $tender->contracting_authority_name }}</p>
                                    </div>
                                </div>
                                @if($tender->workflow?->created_at)
                                <div class="flex gap-4">
                                    <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-500/10 rounded flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-500/30"><i data-lucide="user-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i></div>
                                    <div>
                                        <p class="label-sm">Preuzeto od strane tima</p>
                                        <p class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400">{{ $tender->workflow->created_at->format('d.m.Y @ H:i') }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="w-8 h-8 bg-slate-100 dark:bg-slate-800/50 rounded flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700"><i data-lucide="map-pin" class="w-4 h-4 text-emerald-500 dark:text-emerald-400"></i></div>
                                    <div>
                                        <p class="label-sm">Lokacija</p>
                                        <p class="text-[11px] font-bold text-slate-700 dark:text-slate-200">{{ $tender->contracting_authority_city_name ?? 'BiH' }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-8 h-8 bg-slate-100 dark:bg-slate-800/50 rounded flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700"><i data-lucide="tag" class="w-4 h-4 text-rose-500 dark:text-rose-400"></i></div>
                                    <div>
                                        <p class="label-sm">CPV Klasifikacija</p>
                                        <p class="text-[10px] font-bold text-slate-700 dark:text-slate-200 data-mono uppercase tracking-tighter">{{ $tender->contract_category_name ?? 'Opšta nabavka' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @php $deadline = $tender->lots[0]->application_deadline_date_time ?? null; @endphp
                        <div class="inline-flex items-center gap-3 px-4 py-2 bg-slate-100 dark:bg-black/40 rounded-lg border border-slate-200 dark:border-slate-800/50">
                            <i data-lucide="calendar-clock" class="w-4 h-4 text-rose-500"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-rose-600 dark:text-rose-500">
                                {{ $deadline ? 'ROK ZA PRIJAVU: '.\Carbon\Carbon::parse($deadline)->format('d.m.Y @ H:i') : 'ROK NIJE DEFINISAN' }}
                            </span>
                        </div>
                    </div>

                    {{-- SEKCIJA ZA AKCIJE --}}
                    <div class="flex flex-col justify-between items-start lg:items-end min-w-[260px] border-t lg:border-t-0 lg:border-l border-slate-200 dark:border-slate-800/50 pt-6 lg:pt-0 lg:pl-8">
                        <div class="text-left lg:text-right w-full mb-8">
                            <p class="label-sm mb-1">Procjenjena Vrijednost</p>
                            <p class="text-4xl font-black text-slate-900 dark:text-white data-mono tracking-tighter">{{ number_format($tender->lots_sum_estimated_value, 2, ',', '.') }}<span class="text-xs ml-1 opacity-40">KM</span></p>
                            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-600 uppercase mt-2">Interni ID: #{{ $tender->id }}</p>
                        </div>

                        <div class="w-full space-y-3">
                            <button wire:click="analyzeMarket('{{ $tender->contracting_authority_id }}')" 
                                class="w-full bg-slate-100 dark:bg-[#1e293b]/50 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 py-3 rounded-xl text-[10px] font-black uppercase transition-all flex items-center justify-center gap-3 border border-slate-200 dark:border-slate-700">
                                <i data-lucide="bar-chart-3" class="w-4 h-4 text-blue-500"></i> Analiza tržišta
                            </button>
                            
                            <div class="w-full">
                                {{-- NOVA LOGIKA DUGMADI ZASNOVANA NA TVOM OPISU --}}
                                @if(in_array($tender->workflow?->status, ['accepted', 'documentation_uploaded']))
                                    {{-- AKO JE PRIHVAĆEN ILI SE RADI DOKUMENTACIJA --}}
                                    <a href="{{ route('tender.progress', $tender->workflow->id) }}" wire:navigate 
                                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl flex items-center justify-center gap-2 transition-all shadow-md">
                                        <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Nastavi Rad</span>
                                    </a>
                                
                                @elseif(in_array($tender->workflow?->status, ['offer_submitted', 'completed']))
                                    {{-- AKO JE PREDAT NA PORTAL (ČEKA SE ODLUKA) --}}
                                    <div class="flex gap-2">
                                        <button wire:click="markAsWon('{{ $tender->workflow->id }}')" 
                                            class="flex-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 py-3 rounded-xl text-[10px] font-black uppercase transition-all flex justify-center items-center gap-1 shadow-sm">
                                            <i data-lucide="trophy" class="w-3 h-3"></i> Dobijen
                                        </button>
                                        <button wire:click="markAsLost('{{ $tender->workflow->id }}')" 
                                            class="flex-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 py-3 rounded-xl text-[10px] font-black uppercase transition-all flex justify-center items-center gap-1 shadow-sm">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i> Izgubljen
                                        </button>
                                    </div>
                                
                                @elseif($tender->workflow?->status === 'won')
                                    {{-- AKO JE DOBIEN --}}
                                    <div class="w-full py-3 bg-emerald-500 text-white border border-emerald-400 rounded-xl flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                                        <i data-lucide="trophy" class="w-4 h-4"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Tender Dobijen</span>
                                    </div>
                                    
                                @elseif($tender->workflow?->status === 'lost')
                                    {{-- AKO JE IZGUBLJEN --}}
                                    <div class="w-full py-3 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-600 dark:text-rose-500 rounded-xl flex items-center justify-center gap-2">
                                        <i data-lucide="frown" class="w-4 h-4"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Izgubljen</span>
                                    </div>

                                @elseif($tender->workflow?->status === 'rejected')
                                    {{-- AKO JE ODBIJEN U STARTU (Nismo htjeli raditi) --}}
                                    <div class="w-full py-3 bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl flex items-center justify-center gap-2">
                                        <i data-lucide="ban" class="w-4 h-4"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Odbijeno u startu</span>
                                    </div>

                                @else
                                    {{-- POTPUNO NOV TENDER --}}
                                    <div class="flex gap-3">
                                        <button wire:click="acceptTender('{{ $tender->id }}')" 
                                            class="flex-1 bg-blue-600 text-white py-3 rounded-xl text-[10px] font-black uppercase hover:bg-blue-700 transition-all tracking-widest shadow-md">
                                            Prihvati
                                        </button>
                                        <button onclick="openRejectModal('{{ $tender->id }}')" 
                                            class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 py-3 rounded-xl text-[10px] font-black uppercase hover:border-rose-600 dark:hover:text-rose-600 dark:hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/5 transition-all tracking-widest">
                                            Odbij
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- LOT TABELA --}}
                <div class="mt-8 bg-slate-50 dark:bg-black/30 rounded-2xl border border-slate-200 dark:border-slate-800/50 overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800/50 bg-slate-100/50 dark:bg-transparent">
                                <th class="px-6 py-3 label-sm w-16">LOT</th>
                                <th class="px-4 py-3 label-sm">Opis stavke</th>
                                <th class="px-6 py-3 label-sm text-right">Vrijednost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/20">
                            @foreach($tender->lots as $lot)
                            <tr class="hover:bg-white dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-6 py-3 text-[10px] font-black text-rose-500 data-mono">0{{ $lot->no ?: '1' }}</td>
                                <td class="px-4 py-3 text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tight leading-relaxed">{{ str($lot->short_description ?: ($lot->name ?: $tender->name))->limit(120) }}</td>
                                <td class="px-6 py-3 text-[10px] font-black text-slate-800 dark:text-slate-200 text-right data-mono">{{ number_format($lot->estimated_value, 2, ',', '.') }} KM</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    {{-- PAGINATION --}}
    <div class="mt-12">
        {{ $tenders->links('livewire.custom-pagination') }}
    </div>

    {{-- BRIEFING ANALYSIS MODAL --}}
    <div id="analysisModal" class="fixed inset-0 z-[200] flex items-center justify-center hidden bg-slate-900/40 dark:bg-[#080c14]/98 backdrop-blur-xl">
        <div class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 w-full max-w-4xl rounded-[1.5rem] shadow-2xl mx-4 overflow-hidden flex flex-col md:flex-row h-[85vh] relative">
            <button onclick="closeAnalysisModal()" class="absolute top-6 right-6 text-slate-500 hover:text-slate-900 dark:hover:text-white transition-all z-[210] p-2 bg-slate-100 dark:bg-slate-800/50 rounded-full"><i data-lucide="x" class="w-5 h-5"></i></button>
            <div class="w-full md:w-80 bg-slate-50 dark:bg-slate-900/50 p-8 border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-rose-500/5 rounded-full blur-3xl"></div>
                <div class="relative z-10">
                    <div class="w-10 h-10 bg-rose-600 rounded-lg mb-6 flex items-center justify-center shadow-lg shadow-rose-900/40"><i data-lucide="bar-chart-3" class="text-white w-5 h-5"></i></div>
                    <div class="space-y-6">
                        <div><p class="label-sm mb-1">Ukupan Budžet</p><p id="totalBudget" class="text-2xl font-black text-slate-900 dark:text-white data-mono"></p></div>
                        <div><p class="label-sm mb-1">Status Sistema</p><span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-500 text-[8px] font-black rounded uppercase border border-emerald-200 dark:border-emerald-500/20">Sync Aktivan</span></div>
                    </div>
                </div>
                <button onclick="closeAnalysisModal()" class="relative z-10 w-full py-3 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-lg text-[9px] font-black uppercase tracking-[0.2em] transition-all">Zatvori</button>
            </div>
            <div class="flex-1 p-8 lg:p-12 overflow-y-auto custom-scrollbar bg-white dark:bg-black/20">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-200 dark:border-slate-800/50">
                    <p class="label-sm">Top 10 konkurenata prema ugovorenoj vrijednosti</p>
                    <i data-lucide="trending-up" class="w-4 h-4 text-rose-500 opacity-50"></i>
                </div>
                <div id="analysisContent" class="space-y-4"></div>
            </div>
        </div>
    </div>

    {{-- REJECT MODAL --}}
    <div id="rejectModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden bg-slate-900/40 dark:bg-black/90 backdrop-blur-md">
        <div class="bg-white dark:bg-[#1e293b] border border-slate-200 dark:border-slate-700 w-full max-w-md p-8 rounded-2xl shadow-2xl">
            <h3 class="text-slate-900 dark:text-white font-black text-lg mb-6 uppercase tracking-widest flex items-center gap-3"><i data-lucide="alert-triangle" class="text-rose-500 w-5 h-5"></i> Reject_Log</h3>
            <textarea id="rejectReason" class="w-full bg-slate-50 dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 rounded-xl p-4 text-xs text-slate-900 dark:text-slate-300 focus:border-rose-600 outline-none h-32 data-mono" placeholder="Navedite interni razlog za odbijanje..."></textarea>
            <div class="flex gap-4 mt-8">
                <button onclick="closeRejectModal()" class="flex-1 py-4 text-[10px] font-black text-slate-500 hover:text-slate-900 dark:hover:text-white uppercase">Odustani</button>
                <button id="confirmRejectBtn" class="flex-1 py-4 bg-rose-600 text-white text-[10px] font-black uppercase rounded-xl shadow-md dark:shadow-lg dark:shadow-rose-900/40 hover:bg-rose-700">Potvrdi Odbijanje</button>
            </div>
        </div>
    </div>

    {{-- SKRIPTE --}}
    <script>
        const initUI = () => { if (typeof lucide !== 'undefined') lucide.createIcons(); };
        document.addEventListener('livewire:navigated', initUI);
        document.addEventListener('livewire:init', () => { 
            initUI();
            Livewire.hook('morph.updated', ({ el, component }) => { initUI(); });
            Livewire.on('scroll-top', () => {
                const container = document.getElementById('main-layout');
                if (container) { setTimeout(() => { container.scrollTo({ top: 0, behavior: 'smooth' }); }, 50); }
            });
            document.getElementById('analysisModal').addEventListener('click', function(e) { if (e.target === this) closeAnalysisModal(); });
            document.getElementById('rejectModal').addEventListener('click', function(e) { if (e.target === this) closeRejectModal(); });
        });

        window.addEventListener('openAnalysisModal', event => {
            const payload = event.detail[0] || event.detail;
            let data = payload.data || [];
            const total = payload.total || 0;
            if (!Array.isArray(data)) { data = Object.values(data); }
            const content = document.getElementById('analysisContent');
            const budgetEl = document.getElementById('totalBudget');
            content.innerHTML = '';
            budgetEl.innerText = `${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2 }).format(total)} KM`;
            data.forEach((item, index) => {
                const percent = total > 0 ? ((item.ukupno / total) * 100).toFixed(1) : 0;
                content.innerHTML += `
                    <div class="group bg-slate-50 dark:bg-slate-800/10 hover:bg-slate-100 dark:hover:bg-slate-800/30 border border-slate-200 dark:border-slate-800/50 p-5 rounded-xl transition-all">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-4">
                                <span class="text-[10px] font-black text-slate-400 dark:text-slate-700 data-mono">0${index + 1}</span>
                                <div><p class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight mb-1">${item.ime}</p><p class="text-[9px] text-slate-500 dark:text-slate-600 font-bold uppercase tracking-widest">${item.brojUgovora} Realizacija</p></div>
                            </div>
                            <div class="text-right"><p class="text-sm font-black text-slate-900 dark:text-white data-mono">${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2 }).format(item.ukupno)} <span class="text-[10px] text-slate-500 dark:text-slate-700">KM</span></p><p class="text-[9px] font-black text-rose-500 data-mono">${percent}%</p></div>
                        </div>
                        <div class="h-1 w-full bg-slate-200 dark:bg-slate-900 rounded-full overflow-hidden shadow-inner"><div class="h-full bg-gradient-to-r from-rose-500 to-rose-400 dark:from-rose-700 dark:to-rose-400 shadow-[0_0_10px_rgba(225,29,72,0.4)] transition-all duration-1000" style="width: ${percent}%"></div></div>
                    </div>`;
            });
            document.getElementById('analysisModal').classList.remove('hidden');
            initUI();
        });

        function closeAnalysisModal() { document.getElementById('analysisModal').classList.add('hidden'); }
        let currentTenderId = null;
        function openRejectModal(id) { currentTenderId = id; document.getElementById('rejectModal').classList.remove('hidden'); }
        function closeRejectModal() { document.getElementById('rejectModal').classList.add('hidden'); document.getElementById('rejectReason').value = ''; }
        
        document.getElementById('confirmRejectBtn').addEventListener('click', () => {
            @this.rejectTender(currentTenderId, document.getElementById('rejectReason').value);
            closeRejectModal();
        });
    </script>
</div>