<div class="relative min-h-screen bg-slate-50 text-slate-800 dark:bg-[#050b14] dark:text-slate-200 font-sans pb-20 selection:bg-indigo-500/30 transition-colors duration-500">
    {{-- Premium pozadinski efekti --}}
    <div class="fixed top-0 left-1/4 w-[800px] h-[500px] bg-indigo-400/10 dark:bg-indigo-600/10 blur-[120px] rounded-full pointer-events-none transition-colors duration-500"></div>
    <div class="fixed bottom-0 right-1/4 w-[600px] h-[400px] bg-emerald-400/10 dark:bg-emerald-600/5 blur-[150px] rounded-full pointer-events-none transition-colors duration-500"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto p-6 lg:p-12">
        
        {{-- HEADER I FILTER --}}
        <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div>
                <h1 class="text-4xl font-black uppercase tracking-tighter bg-gradient-to-r from-slate-900 to-slate-500 dark:from-white dark:to-slate-400 bg-clip-text text-transparent">
                    Glavni <span class="text-indigo-500 dark:text-indigo-400">Pregled</span>
                </h1>
                <p class="text-slate-500 font-bold text-[11px] uppercase tracking-[0.2em] mt-2">
                    <i class="fa-solid fa-chart-pie mr-2 text-indigo-500/70"></i> Analitički panel i Praćenje učinka
                </p>
            </div>

            {{-- Filter za Upravu --}}
            <div class="bg-white/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/50 p-2.5 rounded-2xl flex items-center gap-4 shadow-xl dark:shadow-2xl backdrop-blur-xl transition-all hover:border-indigo-400 dark:hover:border-indigo-500/30">
                <div class="w-10 h-10 bg-indigo-50 dark:bg-transparent dark:bg-gradient-to-br dark:from-indigo-500/20 dark:to-purple-500/10 border border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center shadow-sm dark:shadow-inner">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="pr-4 relative group">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-0.5">Analiza po korisniku</p>
                    <div class="relative flex items-center">
                        <select wire:model.live="selectedUser" class="bg-transparent text-sm font-black text-slate-900 dark:text-white outline-none cursor-pointer border-b border-transparent hover:border-indigo-500 pb-0.5 transition-colors w-60 appearance-none pr-8">
                            <option value="" class="bg-white dark:bg-slate-900">Svi korisnici</option>
    
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" class="bg-white dark:bg-slate-900">{{ $user->first_name }} {{ $user->last_name }}</option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-0 text-[10px] text-slate-400 dark:text-slate-600 pointer-events-none group-hover:text-indigo-500 transition-colors"></i>
                    </div>
                </div>
            </div>
        </header>

        {{-- GLAVNI KPI INDIKATORI (Kartice) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            
            {{-- Ukupno aktivnih --}}
            <div class="bg-white dark:bg-slate-900/50 backdrop-blur-md border border-slate-200 dark:border-slate-700/50 border-t-4 border-t-indigo-500 rounded-3xl p-6 relative overflow-hidden group shadow-md dark:shadow-xl transition-colors">
                <div class="absolute -right-4 -top-4 text-indigo-50 dark:text-indigo-500/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12"><i class="fa-solid fa-folder-open"></i></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1 relative z-10">Ukupno Obrađeno</p>
                <h3 class="text-4xl font-black text-slate-900 dark:text-white relative z-10">{{ $stats['ukupno'] }}</h3>
                <div class="mt-3 flex items-center gap-2 relative z-10">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Tendera u bazi</p>
                </div>
            </div>

            {{-- Na čekanju (Poslano) --}}
            <div class="bg-white dark:bg-slate-900/50 backdrop-blur-md border border-slate-200 dark:border-slate-700/50 border-t-4 border-t-amber-500 rounded-3xl p-6 relative overflow-hidden group shadow-md dark:shadow-xl transition-colors">
                <div class="absolute -right-4 -top-4 text-amber-50 dark:text-amber-500/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12"><i class="fa-solid fa-hourglass-half"></i></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-500/80 mb-1 relative z-10">Poslano - Čekamo Odluku</p>
                <h3 class="text-4xl font-black text-slate-900 dark:text-white relative z-10">{{ $stats['na_cekanju'] }}</h3>
                <div class="mt-3 flex items-center gap-2 relative z-10">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Aktivnih na e-Nabavke</p>
                </div>
            </div>

            {{-- Dobijeno (Win) --}}
            <div class="bg-emerald-50 dark:bg-transparent dark:bg-gradient-to-br dark:from-emerald-950/40 dark:to-slate-900/50 backdrop-blur-md border border-emerald-200 dark:border-emerald-500/20 border-t-4 border-t-emerald-500 rounded-3xl p-6 relative overflow-hidden group shadow-md dark:shadow-[0_10px_40px_-10px_rgba(16,185,129,0.15)] transition-colors">
                <div class="absolute -right-4 -top-4 text-emerald-100 dark:text-emerald-500/5 text-8xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12"><i class="fa-solid fa-trophy"></i></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-1 relative z-10">Uspješno Dobijeni</p>
                <h3 class="text-4xl font-black text-emerald-600 dark:text-emerald-400 relative z-10">{{ $stats['dobijeni'] }}</h3>
                <div class="mt-3 p-2 bg-white/60 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg relative z-10">
                    <p class="text-[9px] font-black uppercase text-emerald-600/70 dark:text-emerald-500/70 mb-0.5">Vrijednost Ugovora</p>
                    <p class="text-xs font-mono text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($stats['ukupna_vrijednost_dobijenih'], 2, ',', '.') }} KM</p>
                </div>
            </div>

            {{-- Win Rate Procenat --}}
            <div class="bg-white dark:bg-slate-900/50 backdrop-blur-md border border-slate-200 dark:border-slate-700/50 border-t-4 {{ $stats['win_rate'] >= 50 ? 'border-t-emerald-500' : 'border-t-rose-500' }} rounded-3xl p-6 relative overflow-hidden shadow-md dark:shadow-xl transition-colors">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1 relative z-10">Stopa Uspješnosti</p>
                <h3 class="text-4xl font-black {{ $stats['win_rate'] >= 50 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} relative z-10">{{ $stats['win_rate'] }}%</h3>
                
                {{-- Progress Bar za Win Rate --}}
                <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800/80 rounded-full mt-4 mb-2 relative z-10 overflow-hidden shadow-inner">
                    <div class="h-full {{ $stats['win_rate'] >= 50 ? 'bg-gradient-to-r from-emerald-500 to-emerald-400 dark:from-emerald-600 dark:to-emerald-400 dark:shadow-[0_0_10px_rgba(52,211,153,0.5)]' : 'bg-gradient-to-r from-rose-500 to-rose-400 dark:from-rose-600 dark:to-rose-400 dark:shadow-[0_0_10px_rgba(251,113,133,0.5)]' }} transition-all duration-1000 rounded-full" style="width: {{ $stats['win_rate'] }}%"></div>
                </div>
                <p class="text-[9px] font-black text-slate-500 relative z-10 uppercase flex justify-between">
                    <span>Izgubljeno: <span class="text-rose-500 dark:text-rose-400">{{ $stats['izgubljeni'] }}</span></span>
                </p>
            </div>
        </div>

        {{-- TABELA ZADNJIH AKTIVNOSTI --}}
        <div class="bg-white/60 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700/50 rounded-3xl p-1 shadow-md dark:shadow-2xl backdrop-blur-md transition-colors">
            <div class="bg-white dark:bg-slate-950/50 rounded-[22px] overflow-hidden">
                
                {{-- Header tabele --}}
                <div class="p-6 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50 dark:bg-transparent dark:bg-gradient-to-r dark:from-transparent dark:to-slate-900/50">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 dark:text-white flex items-center gap-3">
                        <span class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 w-8 h-8 rounded-lg flex items-center justify-center text-xs border border-indigo-100 dark:border-indigo-500/20">
                            <i class="fa-solid fa-list-check"></i>
                        </span> 
                        Registar Aktivnih i Nedavnih Tendera
                    </h3>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-900/80 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-100 dark:border-transparent">
                            <tr>
                                <th class="px-6 py-5 w-1/2">Ugovorni Organ / Predmet</th>
                                <th class="px-6 py-5 text-center">Korisnik</th>
                                <th class="px-6 py-5 text-center">Zadnja izmjena</th>
                                <th class="px-6 py-5 text-center">Status</th>
                                <th class="px-6 py-5 text-right">Akcija</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                            @forelse($recentTenders as $tender)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors duration-200 group">
                                
                                {{-- Info o tenderu --}}
                                <td class="px-6 py-4 max-w-md">
                                    <div class="mb-1.5">
                                        <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-0.5">
                                            {{ $tender->procedure->contracting_authority_name ?? 'Nepoznat Ugovorni Organ' }}
                                        </p>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white leading-snug" title="{{ $tender->procedure->name ?? '' }}">
                                            {{ str($tender->procedure->name ?? 'Nepoznat predmet nabavke')->limit(75) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">
                                            ID: <span class="text-slate-600 dark:text-slate-300">{{ $tender->procedure_id }}</span>
                                        </p>
                                        <span class="text-[10px] text-slate-300 dark:text-slate-700">|</span>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">
                                            Iznos: <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($tender->ukupna_vrijednost ?? 0, 2, ',', '.') }} KM</span>
                                        </p>
                                    </div>
                                </td>

                                {{-- Referent --}}
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/50 px-3 py-1.5 rounded-xl shadow-sm dark:shadow-inner">
                                        <div class="w-5 h-5 rounded-md bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-[9px] font-black shadow-sm">
                                            {{ substr($tender->user->first_name ?? 'N', 0, 1) }}
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $tender->user->first_name  ?? 'Korisnik' }} {{ $tender->user->last_name  ?? 'Korisnik' }}</span>
                                    </div>
                                </td>

                                {{-- Datum --}}
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <p class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">{{ $tender->updated_at->format('d.m.Y') }}</p>
                                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-widest mt-0.5">{{ $tender->updated_at->format('H:i') }}</p>
                                </td>

                                {{-- NOVI STATUS BEDGEVI (USKLAĐENI) --}}
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if($tender->status == 'accepted')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30 rounded-lg text-[9px] font-black uppercase tracking-widest"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Prihvaćen</span>
                                    @elseif($tender->status == 'documentation_uploaded')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-500 border border-amber-200 dark:border-amber-500/30 rounded-lg text-[9px] font-black uppercase tracking-widest"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> U Procesu</span>
                                    @elseif(in_array($tender->status, ['offer_submitted', 'completed']))
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/30 rounded-lg text-[9px] font-black uppercase tracking-widest shadow-[0_0_10px_rgba(99,102,241,0.15)]"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Predat</span>
                                    @elseif($tender->status == 'won')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 rounded-lg text-[9px] font-black uppercase tracking-widest"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Dobijen</span>
                                    @elseif($tender->status == 'lost')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/30 rounded-lg text-[9px] font-black uppercase tracking-widest"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Izgubljen</span>
                                    @elseif($tender->status == 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700/50 rounded-lg text-[9px] font-black uppercase tracking-widest"><span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span> Odbijen</span>
                                    @else
                                        {{-- Default / New --}}
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800/50 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-700 rounded-lg text-[9px] font-black uppercase tracking-widest"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Novo</span>
                                    @endif
                                </td>

                                {{-- Akcija Dugme --}}
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('tender.progress', $tender->id) }}" wire:navigate class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 bg-white dark:bg-slate-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-500/30 px-4 py-2.5 rounded-xl transition-all duration-300">
                                        Detalji <i class="fa-solid fa-arrow-right-long transition-transform group-hover:translate-x-1"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-200 dark:border-slate-700/50">
                                        <i class="fa-solid fa-folder-open text-2xl text-slate-400 dark:text-slate-500"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Trenutno nema dostupnih tendera.</p>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mt-2">Sistem čeka na prvi unos</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    {{-- PAGINACIJA --}}
                    <div class="mt-6 px-4 pb-4">
                        {{ $recentTenders->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>