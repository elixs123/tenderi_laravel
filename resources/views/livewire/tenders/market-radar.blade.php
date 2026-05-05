<div class="min-h-screen transition-colors duration-300 bg-slate-50 dark:bg-[#020617] p-4 lg:p-8">
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-blue-600/5 dark:bg-blue-600/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        {{-- HEADER --}}
        <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter text-slate-900 dark:text-white">
                    Market <span class="text-blue-600 dark:text-blue-500">Radar</span>
                </h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.3em] mt-1">Intelligence Dashboard</p>
            </div>
            
            <div class="flex flex-wrap gap-3 w-full md:w-auto">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Pretraga..." class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl py-3 px-4 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500/20 transition-all md:w-64">
                <select wire:model.live="selectedUser" class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl py-3 px-4 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500/20 md:w-64">
                    <option value="">Svi referenti</option>
                    @foreach($referents as $ref) <option value="{{ $ref->id }}">{{ $ref->first_name }} {{ $ref->last_name }}</option> @endforeach
                </select>
            </div>
        </header>

        {{-- TABS --}}
        <div class="flex flex-wrap gap-2 mb-8 bg-slate-200/50 dark:bg-slate-900/50 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 w-fit">
            @foreach(['all' => 'Sve', 'ANNUNCIEMENT' => 'Planovi', 'CONTRACT' => 'Ugovori', 'AWARD' => 'Dodjele', 'NEGOTIATED' => 'Pregovarački', 'NON_PUBLISHED' => 'Aneks II'] as $val => $label)
                <button wire:click="$set('filterType', '{{ $val }}')" class="px-5 py-2 text-[10px] font-black uppercase rounded-xl transition-all {{ $filterType == $val ? 'bg-white dark:bg-blue-600 text-blue-600 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">{{ $label }}</button>
            @endforeach
        </div>

        {{-- FEED --}}
        <div class="space-y-4">
            @forelse($results as $item)
                @php
                    $colors = match($item->type) {
                        'NEGOTIATED' => 'rose', 'ANNUAL_NOTICE' => 'amber', 'NON_PUBLISHED' => 'violet',
                        'CONTRACT' => 'emerald', 'AWARD' => 'indigo', default => 'blue'
                    };
                    $icons = match($item->type) {
                        'NEGOTIATED' => 'fa-bolt', 'AWARD' => 'fa-trophy', 'CONTRACT' => 'fa-file-signature',
                        'NON_PUBLISHED' => 'fa-handshake', default => 'fa-calendar'
                    };
                @endphp
                <div class="group relative overflow-hidden bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/60 rounded-[2rem] p-6 transition-all hover:shadow-2xl hover:shadow-{{ $colors }}-500/5">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-{{ $colors }}-500"></div>
                    <div class="flex flex-col lg:flex-row justify-between gap-6">
                        <div class="flex gap-6 flex-1 min-w-0">
                            <div class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center text-xl bg-{{ $colors }}-50 dark:bg-{{ $colors }}-500/10 text-{{ $colors }}-600 dark:text-{{ $colors }}-500">
                                <i class="fa-solid {{ $icons }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded uppercase border border-{{ $colors }}-200 bg-{{ $colors }}-50 text-{{ $colors }}-600 dark:bg-{{ $colors }}-500/5 dark:text-{{ $colors }}-400 dark:border-{{ $colors }}-500/30">{{ str_replace('_', ' ', $item->type) }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $item->event_date->format('d.m.Y') }}</span>
                                    @if($item->is_master_agreement) <span class="text-[9px] font-black px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 border border-amber-500/20">Okvirni</span> @endif
                                </div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase leading-tight truncate group-hover:text-blue-500">{{ $item->title }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase mt-1"><i class="fa-solid fa-building mr-1"></i>{{ $item->authority_name }}</p>
                            </div>
                        </div>
                        <div class="lg:text-right">
                            @if($item->value > 0)
                                <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Cijena (Bez PDV)</p>
                                <p class="text-2xl font-black text-{{ $colors }}-600 dark:text-{{ $colors }}-400 font-mono tracking-tighter">{{ number_format($item->value, 2, ',', '.') }} KM</p>
                            @endif
                        </div>
                    </div>

                    @if($item->expiry_date)
                        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-wrap justify-between items-center gap-4">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-hourglass-half text-amber-500 text-xs animate-pulse"></i>
                                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase">Ističe: {{ $item->expiry_date->format('d.m.Y') }} ({{ $item->expiry_date->diffForHumans() }})</span>
                            </div>
                            @if($item->expiry_date->diffInMonths(now()) < 4 && !$item->expiry_date->isPast())
                                <span class="text-[9px] font-black px-3 py-1 bg-rose-500 text-white rounded-lg animate-bounce">PRIPREMI PONUDU!</span>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-24 text-center bg-white dark:bg-slate-900/20 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[3rem]">
                    <i class="fa-solid fa-radar text-4xl text-slate-300 mb-4 animate-pulse"></i>
                    <p class="text-slate-400 font-black uppercase text-xs">Radar je čist. Nema podataka.</p>
                </div>
            @endforelse
            <div class="mt-8">{{ $results->links() }}</div>
        </div>
    </div>
</div>