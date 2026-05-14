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
    <div wire:loading.flex wire:target="analyzeMarket" class="fixed inset-0 z-[250] bg-white/90 dark:bg-[#0f172a]/95 backdrop-blur-sm flex flex-col items-center justify-center">
        <div class="relative flex items-center justify-center">
            <div class="absolute w-24 h-24 border-2 border-blue-500/20 rounded-full"></div>
            <div class="absolute w-24 h-24 border-t-2 border-blue-500 rounded-full animate-spin"></div>
            <i data-lucide="radar" class="text-blue-500 w-8 h-8 animate-pulse"></i>
        </div>
        <p class="mt-8 text-blue-500 font-black uppercase tracking-[0.4em] text-[10px]">Deep Market Scan Active_</p>
        <p class="mt-2 text-slate-400 font-bold text-[9px] uppercase tracking-widest">Prikupljanje istorije i e-aukcija...</p>
    </div>

    {{-- STATS GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['L' => 'Tržišni Potencijal', 'V' => number_format($totalValue, 0, ',', '.') . ' KM', 'I' => 'trending-up', 'C' => 'text-blue-600 dark:text-blue-400', 'B' => 'border-b-blue-500'],
            ['L' => 'Aktivni Tenderi', 'V' => $tenders->total(), 'I' => 'clipboard-check', 'C' => 'text-emerald-600 dark:text-emerald-400', 'B' => 'border-b-emerald-500'],
            ['L' => 'Ugovorni Organi', 'V' => $authoritiesCount, 'I' => 'building-2', 'C' => 'text-amber-600 dark:text-amber-400', 'B' => 'border-b-amber-500'],
            ['L' => 'Danas Objavljeno', 'V' => $todayCount, 'I' => 'zap', 'C' => 'text-rose-600 dark:text-rose-400', 'B' => 'border-b-rose-500']
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

            {{-- Filter + Sort Dugmad --}}
            <div class="flex flex-wrap gap-2 shrink-0">
                <div class="flex bg-white dark:bg-slate-900 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <button wire:click="$set('filter', 'all')" class="px-5 py-2 text-[10px] font-black uppercase rounded-lg transition-all {{ $filter === 'all' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:text-blue-500' }}">Svi</button>
                    <button wire:click="$set('filter', 'today')" class="px-5 py-2 text-[10px] font-black uppercase rounded-lg transition-all {{ $filter === 'today' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:text-blue-500' }}">Danas</button>
                    <button wire:click="$set('filter', 'week')" class="px-5 py-2 text-[10px] font-black uppercase rounded-lg transition-all {{ $filter === 'week' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-500 hover:text-rose-500' }}">Ističe uskoro</button>
                </div>
                <div class="flex bg-white dark:bg-slate-900 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <button wire:click="$set('sort', 'announced')" class="px-5 py-2 text-[10px] font-black uppercase rounded-lg transition-all {{ $sort === 'announced' ? 'bg-slate-700 text-white shadow-md' : 'text-slate-500 hover:text-slate-700 dark:hover:text-white' }}">
                        <i data-lucide="calendar" class="w-3 h-3 inline mr-1"></i> Datum
                    </button>
                    <button wire:click="$set('sort', 'deadline')" class="px-5 py-2 text-[10px] font-black uppercase rounded-lg transition-all {{ $sort === 'deadline' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-500 hover:text-rose-500' }}">
                        <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i> Rok
                    </button>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
            {{-- DODATNI FILTERI (Vidljivo samo adminu) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 p-2 rounded-2xl flex items-center gap-3 shadow-sm transition-all hover:border-emerald-500/30">
                    <div class="w-10 h-10 bg-emerald-500/10 text-emerald-500 rounded-xl flex items-center justify-center border border-emerald-500/20">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1 pr-3">
                        <p class="label-sm !mb-0 text-[8px] opacity-70">Region / Lokacija</p>
                        <select wire:model.live="selectedCity" class="w-full bg-transparent text-xs font-bold text-slate-900 dark:text-white outline-none appearance-none cursor-pointer">
                            <option value="">Sve lokacije u BiH</option>
                            @foreach($cities as $city) <option value="{{ $city }}">{{ $city }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 p-2 rounded-2xl flex items-center gap-3 shadow-sm transition-all hover:border-indigo-500/30">
                    <div class="w-10 h-10 bg-indigo-500/10 text-indigo-500 rounded-xl flex items-center justify-center border border-indigo-500/20">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1 pr-3">
                        <p class="label-sm !mb-0 text-[8px] opacity-70">Referent</p>
                        <select wire:model.live="selectedUser" class="w-full bg-transparent text-xs font-bold text-slate-900 dark:text-white outline-none appearance-none cursor-pointer">
                            <option value="">Svi uposlenici</option>
                            @foreach($referents as $ref) <option value="{{ $ref->id }}">{{ $ref->first_name }} {{ $ref->last_name }}</option> @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @else
            {{-- INFO BANER ZA ZAPOSLENE --}}
            <div class="flex items-center gap-2 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 rounded-xl">
                <i data-lucide="info" class="w-4 h-4 text-blue-500"></i>
                <p class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">Prikazuju se tenderi prema vašim personalizovanim postavkama.</p>
            </div>
        @endif
    </div>
    
    {{-- TENDER FEED --}}
    <div class="space-y-6">
        @foreach($tenders as $tender)
        
        @php
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

        <article wire:key="tender-{{ $tender->id }}" class="tender-card relative rounded-2xl overflow-hidden transition-all duration-300 flex border-l-0 {{ $statusInfo['bg'] }}">
            <div class="w-8 shrink-0 flex items-center justify-center border-r transition-colors duration-300 border-slate-200 dark:border-slate-800/50 {{ $statusInfo['bg'] }}">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-{{ $statusInfo['color'] }}-500"></div>
                <span class="transform -rotate-90 whitespace-nowrap text-[8px] font-black tracking-[0.3em] uppercase {{ $statusInfo['text'] }}">{{ $statusInfo['label'] }}</span>
            </div>

            <div class="p-6 lg:p-8 flex-1 min-w-0">
                <div class="flex flex-col lg:flex-row justify-between gap-8">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[8px] font-black px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700 uppercase">{{ $tender->type }}</span>
                            <span class="text-slate-400 dark:text-slate-600 text-[10px] font-bold data-mono">#{{ $tender->number }}</span>
                            <span class="text-[9px] font-bold text-slate-400 dark:text-slate-600 ml-auto flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i> SISTEM: {{ $tender->created_at->format('d.m.Y H:i') }}
                            </span>
                        </div>

                        <h2 class="text-xl font-black text-slate-900 dark:text-white leading-tight mb-6 uppercase tracking-tight">{{ $tender->name }}</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="w-8 h-8 bg-slate-100 dark:bg-slate-800/50 rounded flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700"><i data-lucide="landmark" class="w-4 h-4 text-blue-500 dark:text-blue-400"></i></div>
                                    <div class="truncate">
                                        <p class="label-sm">Ugovorni Organ</p>
                                        <p class="text-[11px] font-bold text-slate-700 dark:text-slate-200 truncate">{{ $tender->contracting_authority_name }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="w-8 h-8 bg-slate-100 dark:bg-slate-800/50 rounded flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700"><i data-lucide="map-pin" class="w-4 h-4 text-emerald-500 dark:text-emerald-400"></i></div>
                                    <div>
                                        <p class="label-sm">Lokacija</p>
                                        <p class="text-[11px] font-bold text-slate-700 dark:text-slate-200">{{ $tender->contracting_authority_city_name ?? 'BiH' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @php
                            $deadline = $tender->lots->min('application_deadline_date_time');
                            $daysLeft = $deadline ? (int) now()->diffInDays(\Carbon\Carbon::parse($deadline), false) : null;
                            $hoursLeft = $deadline ? (int) now()->diffInHours(\Carbon\Carbon::parse($deadline), false) : null;

                            if ($daysLeft === null) {
                                $deadlineLabel = 'ROK NIJE DEFINISAN';
                                $deadlineColor = 'text-slate-400 dark:text-slate-500';
                                $deadlineBg = 'bg-slate-100 dark:bg-black/40 border-slate-200 dark:border-slate-800/50';
                            } elseif ($daysLeft < 0) {
                                $deadlineLabel = 'ISTEKLO · ' . \Carbon\Carbon::parse($deadline)->format('d.m.Y');
                                $deadlineColor = 'text-slate-400 dark:text-slate-500';
                                $deadlineBg = 'bg-slate-100 dark:bg-black/30 border-slate-200 dark:border-slate-700/30';
                            } elseif ($hoursLeft < 24) {
                                $deadlineLabel = 'ISTIČE ZA ' . $hoursLeft . 'h · ' . \Carbon\Carbon::parse($deadline)->format('H:i');
                                $deadlineColor = 'text-rose-600 dark:text-rose-400';
                                $deadlineBg = 'bg-rose-50 dark:bg-rose-500/10 border-rose-300 dark:border-rose-500/30';
                            } elseif ($daysLeft <= 3) {
                                $deadlineLabel = 'ISTIČE ZA ' . $daysLeft . ' ' . ($daysLeft === 1 ? 'DAN' : 'DANA') . ' · ' . \Carbon\Carbon::parse($deadline)->format('d.m.Y');
                                $deadlineColor = 'text-orange-600 dark:text-orange-400';
                                $deadlineBg = 'bg-orange-50 dark:bg-orange-500/10 border-orange-300 dark:border-orange-500/30';
                            } elseif ($daysLeft <= 7) {
                                $deadlineLabel = 'ISTIČE ZA ' . $daysLeft . ' DANA · ' . \Carbon\Carbon::parse($deadline)->format('d.m.Y');
                                $deadlineColor = 'text-amber-600 dark:text-amber-400';
                                $deadlineBg = 'bg-amber-50 dark:bg-amber-500/10 border-amber-300 dark:border-amber-500/30';
                            } else {
                                $deadlineLabel = 'ROK: ' . \Carbon\Carbon::parse($deadline)->format('d.m.Y @ H:i') . ' (' . $daysLeft . ' dana)';
                                $deadlineColor = 'text-slate-600 dark:text-slate-400';
                                $deadlineBg = 'bg-slate-100 dark:bg-black/40 border-slate-200 dark:border-slate-800/50';
                            }
                        @endphp
                        <div class="inline-flex items-center gap-3 px-4 py-2 rounded-lg border {{ $deadlineBg }}">
                            <i data-lucide="calendar-clock" class="w-4 h-4 {{ $deadlineColor }} shrink-0"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest {{ $deadlineColor }}">{{ $deadlineLabel }}</span>
                        </div>
                    </div>

                    {{-- SEKCIJA ZA AKCIJE --}}
                    <div class="flex flex-col justify-between items-start lg:items-end min-w-[260px] border-t lg:border-t-0 lg:border-l border-slate-200 dark:border-slate-800/50 pt-6 lg:pt-0 lg:pl-8">
                        <div class="text-left lg:text-right w-full mb-8">
                            @php
                                $isAccepted = in_array($tender->workflow?->status, ['accepted', 'documentation_uploaded', 'offer_submitted', 'completed', 'won', 'lost']);
                                $acceptedLots = is_string($tender->workflow?->accepted_lots) ? json_decode($tender->workflow?->accepted_lots, true) : ($tender->workflow?->accepted_lots ?? []);
                                $displayValue = ($isAccepted && count($acceptedLots) > 0) ? $tender->lots->whereIn('id', $acceptedLots)->sum('estimated_value') : $tender->lots_sum_estimated_value;
                            @endphp
                            <p class="label-sm mb-1">Procjenjena vrijednost</p>
                            <p class="text-4xl font-black text-slate-900 dark:text-white data-mono tracking-tighter">{{ number_format($displayValue, 2, ',', '.') }}<span class="text-xs ml-1 opacity-40">KM</span></p>
                        </div>

                        <div class="w-full space-y-3">
                            <button wire:click="analyzeMarket('{{ $tender->contracting_authority_id }}', '{{ $tender->id }}')" 
                                class="w-full bg-slate-100 dark:bg-[#1e293b]/50 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 py-3 rounded-xl text-[10px] font-black uppercase transition-all flex items-center justify-center gap-3 border border-slate-200 dark:border-slate-700 relative overflow-hidden group">
                                <div class="absolute inset-0 bg-blue-500/5 translate-y-full group-hover:translate-y-0 transition-transform"></div>
                                <i data-lucide="radar" class="w-4 h-4 text-blue-500 relative z-10"></i> 
                                <span class="relative z-10">Analiza tržišta</span>
                            </button>
                            
                            <div class="w-full">
                                @if(in_array($tender->workflow?->status, ['accepted', 'documentation_uploaded']))
                                    <a href="{{ route('tender.progress', $tender->workflow->id) }}" wire:navigate class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl flex items-center justify-center gap-2 transition-all shadow-md text-[10px] font-black uppercase tracking-widest"><i data-lucide="arrow-right-circle" class="w-4 h-4"></i> Nastavi Rad</a>
                                @elseif(in_array($tender->workflow?->status, ['offer_submitted', 'completed']))
                                    <div class="flex gap-2">
                                        <button wire:click="markAsWon('{{ $tender->workflow->id }}')" class="flex-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 py-3 rounded-xl text-[10px] font-black uppercase transition-all flex justify-center items-center gap-1 shadow-sm"><i data-lucide="trophy" class="w-3 h-3"></i> Dobijen</button>
                                        <button wire:click="markAsLost('{{ $tender->workflow->id }}')" class="flex-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 py-3 rounded-xl text-[10px] font-black uppercase transition-all flex justify-center items-center gap-1 shadow-sm"><i data-lucide="x-circle" class="w-3 h-3"></i> Izgubljen</button>
                                    </div>
                                @elseif($tender->workflow?->status === 'won')
                                    <div class="w-full py-3 bg-emerald-500 text-white border border-emerald-400 rounded-xl flex items-center justify-center gap-2"><i data-lucide="trophy" class="w-4 h-4"></i><span class="text-[10px] font-black uppercase tracking-widest">Tender Dobijen</span></div>
                                @elseif($tender->workflow?->status === 'lost')
    <div class="w-full py-3 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-600 dark:text-rose-500 rounded-xl flex items-center justify-center gap-2">
        <i data-lucide="frown" class="w-4 h-4"></i>
        <span class="text-[10px] font-black uppercase tracking-widest">Izgubljen</span>
    </div>

    @if($tender->workflow->winner_supplier)
        <div class="mt-2 px-3 py-2 bg-slate-50 dark:bg-black/30 border border-slate-200 dark:border-slate-700 rounded-xl">
            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Dobio tender</p>
            <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300 truncate">{{ $tender->workflow->winner_supplier }}</p>
            @if($tender->workflow->final_price)
                <p class="text-[10px] font-black text-slate-500 data-mono mt-0.5">
                    {{ number_format($tender->workflow->final_price, 2, ',', '.') }} KM
                </p>
            @endif
        </div>
    @else
        <div class="mt-2 flex gap-2">
            <input
                type="text"
                placeholder="Ko je dobio tender?"
                class="flex-1 bg-slate-50 dark:bg-black/30 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-[11px] text-slate-700 dark:text-slate-300 outline-none focus:border-slate-400 dark:focus:border-slate-500 transition-all"
                id="winner-input-{{ $tender->workflow->id }}"
            >
            <input
                type="number"
                placeholder="KM"
                step="0.01"
                class="w-24 bg-slate-50 dark:bg-black/30 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-[11px] text-slate-700 dark:text-slate-300 outline-none focus:border-slate-400 dark:focus:border-slate-500 transition-all data-mono"
                id="winner-price-{{ $tender->workflow->id }}"
            >
            <button
                onclick="saveWinner('{{ $tender->workflow->id }}')"
                class="px-3 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-xl transition-all">
                <i data-lucide="check" class="w-4 h-4"></i>
            </button>
        </div>
    @endif
                                @elseif($tender->workflow?->status === 'rejected')
                                    <div class="w-full py-3 bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl flex items-center justify-center gap-2"><i data-lucide="ban" class="w-4 h-4"></i><span class="text-[10px] font-black uppercase tracking-widest">Odbijeno u startu</span></div>
                                @else
                                    <div class="flex gap-3">
                                        @if($tender->lots->count() > 1)
                                            <button wire:click="openAcceptModal({{ $tender->id }})" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl flex items-center justify-center gap-2 transition-all shadow-md text-[10px] font-black uppercase tracking-widest">Izaberi</button>
                                        @else
                                            <button wire:click="acceptSingleLot({{ $tender->id }})" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl flex items-center justify-center gap-2 transition-all shadow-md text-[10px] font-black uppercase tracking-widest">Prihvati</button>
                                        @endif
                                        <button onclick="openRejectModal('{{ $tender->id }}')" class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 py-3 rounded-xl text-[10px] font-black uppercase hover:border-rose-600 dark:hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/5 transition-all tracking-widest">Odbij</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- LOT SUMMARY CHIP --}}
                @if($tender->lots->count() > 0)
                @php
                    $totalLots = $tender->lots->count();
                    $acceptedLotObjects = ($isAccepted && count($acceptedLots) > 0)
                        ? $tender->lots->whereIn('id', $acceptedLots)
                        : ($isAccepted ? $tender->lots : collect());
                    $acceptedCount = $acceptedLotObjects->count();
                    $acceptedValue = $acceptedLotObjects->sum('estimated_value');
                @endphp
                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Lotovi:</span>
                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-black rounded-lg border border-slate-200 dark:border-slate-700">
                        {{ $totalLots }} ukupno
                    </span>
                    @if($isAccepted && $acceptedCount > 0)
                    <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[10px] font-black rounded-lg border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-1">
                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                        {{ $acceptedCount }} prihvaćen{{ $acceptedCount > 1 ? 'o' : '' }} · {{ number_format($acceptedValue, 0, ',', '.') }} KM
                    </span>
                    @if($totalLots - $acceptedCount > 0)
                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800/50 text-slate-400 dark:text-slate-500 text-[10px] font-black rounded-lg border border-slate-200 dark:border-slate-700/50">
                        {{ $totalLots - $acceptedCount }} odbačeno
                    </span>
                    @endif
                    @endif
                </div>
                @endif

                {{-- LOT TABELA --}}
                <div class="mt-4 bg-slate-50 dark:bg-black/30 rounded-2xl border border-slate-200 dark:border-slate-800/50 overflow-hidden">
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
                            @php
                                $isThisLotAccepted = false; $isFaded = false;
                                if ($isAccepted) {
                                    if (count($acceptedLots) > 0) {
                                        $isThisLotAccepted = in_array($lot->id, $acceptedLots);
                                        $isFaded = !$isThisLotAccepted; 
                                    } else { $isThisLotAccepted = true; }
                                }
                            @endphp
                            <tr class="{{ $isFaded ? 'opacity-40 grayscale bg-slate-50/50 dark:bg-transparent' : 'hover:bg-white dark:hover:bg-slate-800/20' }} transition-all duration-300">
                                <td class="px-6 py-3 text-[10px] font-black {{ $isFaded ? 'text-slate-400' : 'text-rose-500' }} data-mono align-top pt-4">0{{ $lot->no ?: '1' }}
                                    @if($isThisLotAccepted)<span class="block mt-2 text-[8px] bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 px-1 py-1 rounded border border-emerald-200 dark:border-emerald-500/20 text-center uppercase tracking-widest shadow-sm">Prihvaćen</span>
                                    @elseif($isAccepted && !$isThisLotAccepted)<span class="block mt-2 text-[8px] bg-slate-100 text-slate-400 dark:bg-slate-800/50 dark:text-slate-500 px-1 py-1 rounded text-center uppercase tracking-widest">Odbačen</span>@endif
                                </td>
                                <td class="px-4 py-3 text-[10px] font-bold {{ $isFaded ? 'text-slate-400 dark:text-slate-600' : 'text-slate-600 dark:text-slate-300' }} uppercase tracking-tight leading-relaxed align-top pt-4">{{ str($lot->short_description ?: ($lot->name ?: $tender->name))->limit(120) }}</td>
                                <td class="px-6 py-3 text-[10px] font-black {{ $isFaded ? 'text-slate-400 dark:text-slate-600' : 'text-slate-800 dark:text-slate-200' }} text-right data-mono align-top pt-4">{{ number_format($lot->estimated_value, 2, ',', '.') }} KM</td>
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
    <div class="mt-12">{{ $tenders->links('livewire.custom-pagination') }}</div>

    {{-- ========================================== --}}
    {{-- MODALI (SVI MORAJU BITI VAN PETLJE)        --}}
    {{-- ========================================== --}}

    {{-- ADVANCED ANALYSIS MODAL --}}
    <div id="analysisModal" class="fixed inset-0 z-[200] flex items-center justify-center hidden bg-slate-900/60 dark:bg-[#080c14]/98 backdrop-blur-xl transition-all">
        <div class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 w-full max-w-5xl rounded-[1.5rem] shadow-2xl mx-4 overflow-hidden flex flex-col h-[90vh] md:h-[80vh] relative">
            
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-900/20 relative overflow-hidden">
                        <i data-lucide="radar" class="text-white w-6 h-6 relative z-10 animate-[spin_4s_linear_infinite]"></i>
                        <div class="absolute inset-0 border-2 border-white/20 rounded-xl animate-ping"></div>
                    </div>
                    <div>
                        <h3 class="text-slate-900 dark:text-white font-black text-lg uppercase tracking-tight" id="modalAuthorityName">Analiza Ugovornog Organa</h3>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Uživo podaci sa EJN
                        </p>
                    </div>
                </div>
                <button onclick="closeAnalysisModal()" class="text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all p-2 bg-slate-200 dark:bg-slate-800 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
                <div class="w-full md:w-1/2 p-6 overflow-y-auto custom-scrollbar border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-transparent">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/30 rounded-2xl border border-slate-200 dark:border-slate-700/50">
                            <p class="label-sm mb-1">Ukupan Budžet (Prikazano)</p>
                            <p id="totalBudget" class="text-xl font-black text-blue-600 dark:text-blue-400 data-mono"></p>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/30 rounded-2xl border border-slate-200 dark:border-slate-700/50">
                            <p class="label-sm mb-1">Broj Ugovora</p>
                            <p id="totalContractsCount" class="text-xl font-black text-slate-900 dark:text-white data-mono"></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-slate-300">Najjači Konkurenti (Top 5)</p>
                        <i data-lucide="trophy" class="w-4 h-4 text-amber-500"></i>
                    </div>
                    <div id="topCompetitorsContent" class="space-y-3"></div>
                </div>

                <div class="w-full md:w-1/2 p-6 overflow-y-auto custom-scrollbar bg-slate-50/50 dark:bg-slate-900/20">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-slate-300">Zadnji slični ugovori</p>
                        <i data-lucide="history" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <div id="recentContractsContent" class="space-y-4"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACCEPT MODAL --}}
    <div id="acceptTenderModal" class="fixed inset-0 z-[200] flex items-center justify-center hidden bg-slate-900/40 dark:bg-black/90 backdrop-blur-md">
        <div class="bg-white dark:bg-[#1e293b] border border-slate-200 dark:border-slate-700 w-full max-w-2xl mx-4 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-[#0f172a]">
                <h3 class="text-slate-900 dark:text-white font-black text-lg uppercase tracking-widest flex items-center gap-3"><i data-lucide="check-square" class="text-blue-500 w-5 h-5"></i> Odabir Lotova</h3>
                <button onclick="document.getElementById('acceptTenderModal').classList.add('hidden')" wire:click="$set('acceptingProcedureId', null)" class="text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-white dark:bg-[#1e293b]">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-6 uppercase tracking-wider">Ovaj tender sadrži više lotova. Označite one na kojima želite učestvovati:</p>
                <div class="space-y-3">
                    @foreach($availableLots as $lot)
                        <label for="lot_{{ $lot['id'] }}" class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group">
                            <div class="mt-1"><input type="checkbox" wire:model="selectedLots" value="{{ $lot['id'] }}" id="lot_{{ $lot['id'] }}" class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-2 dark:bg-slate-700 cursor-pointer"></div>
                            <div class="flex-1">
                                <span class="block text-xs font-black text-rose-500 mb-1 uppercase">Lot {{ $lot['no'] ?? ($lot['lot_number'] ?? $loop->iteration) }}</span> 
                                <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-3 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ \Illuminate\Support\Str::limit($lot['short_description'] ?? ($lot['name'] ?? 'Opšta stavka'), 120) }}</span>
                                <span class="inline-flex px-2 py-1 bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 text-[10px] font-black rounded border border-slate-200 dark:border-slate-800 data-mono">{{ number_format($lot['estimated_value'] ?? 0, 2) }} KM</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#0f172a] flex justify-end gap-4">
                <button onclick="document.getElementById('acceptTenderModal').classList.add('hidden')" wire:click="$set('acceptingProcedureId', null)" class="px-6 py-3 text-[10px] font-black text-slate-500 uppercase">Odustani</button>
                <button wire:click="confirmAccept" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase rounded-xl flex items-center gap-2"><i data-lucide="check" class="w-4 h-4"></i> Potvrdi odabir</button>
            </div>
        </div>
    </div>

    {{-- REJECT MODAL --}}
    <div id="rejectModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden bg-slate-900/40 dark:bg-black/90 backdrop-blur-md">
        <div class="bg-white dark:bg-[#1e293b] border border-slate-200 dark:border-slate-700 w-full max-w-md p-8 rounded-2xl shadow-2xl">
            <h3 class="text-slate-900 dark:text-white font-black text-lg mb-6 uppercase tracking-widest flex items-center gap-3"><i data-lucide="alert-triangle" class="text-rose-500 w-5 h-5"></i> Razlog odbijanja</h3>
            <textarea id="rejectReason" class="w-full bg-slate-50 dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 rounded-xl p-4 text-xs text-slate-900 dark:text-slate-300 focus:border-rose-600 outline-none h-32 data-mono" placeholder="Navedite interni razlog za odbijanje..."></textarea>
            <div class="flex gap-4 mt-8">
                <button onclick="closeRejectModal()" class="flex-1 py-4 text-[10px] font-black text-slate-500 hover:text-slate-900 dark:hover:text-white uppercase">Odustani</button>
                <button id="confirmRejectBtn" class="flex-1 py-4 bg-rose-600 text-white text-[10px] font-black uppercase rounded-xl shadow-md hover:bg-rose-700">Potvrdi Odbijanje</button>
            </div>
        </div>
    </div>

    {{-- JS SKRIPTE --}}
    <script>
        window.initUI = () => { if (typeof lucide !== 'undefined') lucide.createIcons(); };
        document.addEventListener('livewire:navigated', window.initUI);
        document.addEventListener('livewire:init', () => { 
            window.initUI();
            Livewire.hook('morph.updated', ({ el, component }) => { window.initUI(); });
            Livewire.on('scroll-top', () => {
                const container = document.getElementById('main-layout');
                if (container) { setTimeout(() => { container.scrollTo({ top: 0, behavior: 'smooth' }); }, 50); }
            });
            document.getElementById('analysisModal').addEventListener('click', function(e) { if (e.target === this) closeAnalysisModal(); });
            document.getElementById('rejectModal').addEventListener('click', function(e) { if (e.target === this) closeRejectModal(); });
            document.getElementById('acceptTenderModal').addEventListener('click', function(e) { if (e.target === this) { this.classList.add('hidden'); @this.set('acceptingProcedureId', null); } });
        });

        // Novi Analysis Modal JS Listener
        window.addEventListener('openAnalysisModal', event => {
            const payload = event.detail[0] || event.detail;
            const topCompetitors = payload.topCompetitors || [];
            const recentContracts = payload.recentContracts || [];
            const totalValue = payload.totalValue || 0;
            const totalContracts = payload.totalContracts || 0;
            const authorityName = payload.authorityName || 'Analiza Tržišta';

            document.getElementById('modalAuthorityName').innerText = authorityName;
            document.getElementById('totalBudget').innerText = `${new Intl.NumberFormat('de-DE').format(totalValue)} KM`;
            document.getElementById('totalContractsCount').innerText = totalContracts;

            const competitorsDiv = document.getElementById('topCompetitorsContent');
            competitorsDiv.innerHTML = '';
            topCompetitors.forEach((item, index) => {
                const percent = totalValue > 0 ? ((item.total_value / totalValue) * 100).toFixed(1) : 0;
                competitorsDiv.innerHTML += `
                    <div class="group bg-white dark:bg-slate-800/40 hover:bg-blue-50 dark:hover:bg-blue-900/20 border border-slate-200 dark:border-slate-700/50 p-4 rounded-xl transition-all relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b ${index === 0 ? 'from-amber-400 to-amber-600' : 'from-slate-300 to-slate-400 dark:from-slate-600 dark:to-slate-700'}"></div>
                        <div class="flex justify-between items-center pl-2">
                            <div class="pr-2 flex-1">
                                <p class="text-[11px] font-black text-slate-900 dark:text-white uppercase leading-tight mb-1">${item.supplier_name}</p>
                                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">${item.contracts_count} Pobjeda</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-black text-slate-900 dark:text-white data-mono">${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2 }).format(item.total_value)}</p>
                                <p class="text-[9px] font-black text-blue-500 data-mono">${percent}% učešća</p>
                            </div>
                        </div>
                    </div>`;
            });

            const contractsDiv = document.getElementById('recentContractsContent');
            contractsDiv.innerHTML = '';
            if(recentContracts.length === 0) {
                contractsDiv.innerHTML = `<p class="text-xs text-slate-500 text-center py-10 font-bold uppercase">Nema podataka o prethodnim ugovorima.</p>`;
            } else {
                recentContracts.forEach(contract => {
                    const date = new Date(contract.AwardDate).toLocaleDateString('bs-BA');
                    let auctionHtml = '';
                    if (contract.Auction && contract.Auction.pad_procenat > 0) {
                        auctionHtml = `
                            <div class="mt-3 p-2 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 rounded-lg flex justify-between items-center">
                                <div class="flex items-center gap-2"><i data-lucide="trending-down" class="w-3 h-3 text-rose-500"></i><span class="text-[9px] font-black uppercase text-rose-600 dark:text-rose-400">Pad na aukciji</span></div>
                                <div class="text-right">
                                    <span class="line-through text-[9px] text-slate-400 mr-1">${new Intl.NumberFormat('de-DE').format(contract.Auction.initial)}</span>
                                    <i data-lucide="arrow-right" class="w-2 h-2 inline text-slate-400"></i>
                                    <span class="text-[10px] font-black text-rose-600 dark:text-rose-400 ml-1">${new Intl.NumberFormat('de-DE').format(contract.Auction.final)} KM</span>
                                    <span class="ml-2 px-1 py-0.5 bg-rose-500 text-white text-[8px] rounded font-bold">-${contract.Auction.pad_procenat}%</span>
                                </div>
                            </div>`;
                    } else if (contract.Auction && contract.Auction.pad_procenat === 0) {
                        auctionHtml = `<div class="mt-2 text-[8px] font-bold text-slate-400 uppercase tracking-widest"><i data-lucide="minus" class="w-2 h-2 inline"></i> Bez promjene cijene na e-aukciji</div>`;
                    }

                    contractsDiv.innerHTML += `
                        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 p-4 rounded-xl shadow-sm">
                            <div class="flex justify-between items-start mb-3">
                                <span class="bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-[8px] font-black px-2 py-0.5 rounded uppercase border border-emerald-200 dark:border-emerald-500/20">${date}</span>
                                <span class="text-xs font-black text-slate-900 dark:text-white data-mono">${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2 }).format(contract.ContractValue)} KM</span>
                            </div>
                            <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 leading-relaxed mb-3 uppercase">${contract.ProcedureName || 'N/A'}</p>
                            <div class="flex items-center gap-2 pt-3 border-t border-slate-100 dark:border-slate-700/50">
                                <i data-lucide="check-circle" class="w-3 h-3 text-blue-500"></i>
                                <p class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider truncate">Dobitnik: ${contract.SupplierName}</p>
                            </div>
                            ${auctionHtml}
                        </div>`;
                });
            }
            document.getElementById('analysisModal').classList.remove('hidden');
            window.initUI();
        });

        function closeAnalysisModal() { document.getElementById('analysisModal').classList.add('hidden'); }

        function saveWinner(workflowId) {
            const supplier = document.getElementById('winner-input-' + workflowId)?.value?.trim();
            const price = document.getElementById('winner-price-' + workflowId)?.value;
            if (!supplier && !price) return;
            @this.saveWinner(workflowId, supplier, price);
        }
        
        let currentTenderId = null;
        function openRejectModal(id) { currentTenderId = id; document.getElementById('rejectModal').classList.remove('hidden'); }
        function closeRejectModal() { document.getElementById('rejectModal').classList.add('hidden'); document.getElementById('rejectReason').value = ''; }
        document.getElementById('confirmRejectBtn').addEventListener('click', () => { @this.rejectTender(currentTenderId, document.getElementById('rejectReason').value); closeRejectModal(); });

        window.addEventListener('open-modal', event => { if (event.detail[0] === 'accept-tender-modal' || event.detail === 'accept-tender-modal') { document.getElementById('acceptTenderModal').classList.remove('hidden'); window.initUI(); } });
        window.addEventListener('close-modal', event => { if (event.detail[0] === 'accept-tender-modal' || event.detail === 'accept-tender-modal') { document.getElementById('acceptTenderModal').classList.add('hidden'); } });
    </script>
</div>