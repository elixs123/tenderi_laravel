<div class="relative min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-200 font-sans pb-20 selection:bg-indigo-500/30">
    
    <div class="relative z-10 max-w-7xl mx-auto p-6 lg:p-12">
        
        {{-- HEADER I FILTER --}}
        <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div class="space-y-1.5">
                <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-slate-900 dark:text-white">
                    Glavni <span class="text-indigo-600 dark:text-indigo-500">Pregled</span>
                </h1>
                <p class="text-slate-500 dark:text-slate-400 font-bold text-xs uppercase tracking-widest flex items-center">
                    <i class="fa-solid fa-chart-pie mr-2 text-indigo-500"></i> Analitički panel i Praćenje učinka
                </p>
            </div>

            {{-- Filteri --}}
            <div class="flex flex-wrap gap-4">
                {{-- Filter po korisniku --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-2.5 rounded-xl flex items-center gap-3 shadow-sm hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors group">
                    <div class="w-10 h-10 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center text-sm transition-transform group-hover:scale-105">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="pr-4 relative">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-0.5">Korisnik</p>
                        <div class="relative flex items-center">
                            <select wire:model.live="selectedUser" class="bg-transparent text-sm font-black text-slate-800 dark:text-slate-100 outline-none cursor-pointer border-b-2 border-transparent hover:border-indigo-500 pb-0.5 transition-colors w-40 appearance-none focus:ring-0">
                                <option value="" class="bg-white dark:bg-slate-900">Svi korisnici</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" class="bg-white dark:bg-slate-900">{{ $user->first_name }} {{ $user->last_name }}</option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-0 text-xs text-slate-400 pointer-events-none transition-transform group-hover:translate-y-0.5"></i>
                        </div>
                    </div>
                </div>

                {{-- Filter po statusu --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-2.5 rounded-xl flex items-center gap-3 shadow-sm hover:border-emerald-400 dark:hover:border-emerald-500 transition-colors group">
                    <div class="w-10 h-10 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center text-sm transition-transform group-hover:scale-105">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <div class="pr-4 relative">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-0.5">Pregled</p>
                        <div class="relative flex items-center">
                            <select wire:model.live="statusFilter" class="bg-transparent text-sm font-black text-slate-800 dark:text-slate-100 outline-none cursor-pointer border-b-2 border-transparent hover:border-emerald-500 pb-0.5 transition-colors w-32 appearance-none focus:ring-0">
                                <option value="" class="bg-white dark:bg-slate-900">Svi tenderi</option>
                                <option value="active" class="bg-white dark:bg-slate-900">Aktivni</option>
                                <option value="won" class="bg-white dark:bg-slate-900">Dobijeni</option>
                                <option value="lost" class="bg-white dark:bg-slate-900">Izgubljeni</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-0 text-xs text-slate-400 pointer-events-none transition-transform group-hover:translate-y-0.5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- GLAVNI KPI INDIKATORI (Kartice) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            
            {{-- Ukupno aktivnih --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 border-t-4 border-t-indigo-600 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <p class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">Ukupno Obrađeno</p>
                <h3 class="text-4xl font-black text-slate-900 dark:text-white mb-3">{{ $stats['ukupno'] }}</h3>
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/50 w-max px-2.5 py-1 rounded border border-slate-100 dark:border-slate-800">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <p class="text-[10px] text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider">Tendera u bazi</p>
                </div>
            </div>

            {{-- Na čekanju (Poslano) --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 border-t-4 border-t-amber-500 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <p class="text-xs font-black uppercase tracking-widest text-amber-600 dark:text-amber-500 mb-1">Poslano - Čekamo</p>
                <h3 class="text-4xl font-black text-slate-900 dark:text-white mb-3">{{ $stats['na_cekanju'] }}</h3>
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/50 w-max px-2.5 py-1 rounded border border-slate-100 dark:border-slate-800">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <p class="text-[10px] text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider">Aktivnih na e-Nabavke</p>
                </div>
            </div>

            {{-- Dobijeno (Win) --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 border-t-4 border-t-emerald-500 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <p class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-500 mb-1">Uspješno Dobijeni</p>
                <h3 class="text-4xl font-black text-emerald-600 dark:text-emerald-500 mb-3">{{ $stats['dobijeni'] }}</h3>
                <div class="bg-slate-50 dark:bg-slate-800/50 px-3 py-2 rounded-lg border border-slate-100 dark:border-slate-800 flex flex-col">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Vrijednost Ugovora</p>
                    <p class="text-sm font-mono text-slate-800 dark:text-slate-200 font-black">{{ number_format($stats['ukupna_vrijednost_dobijenih'], 2, ',', '.') }} KM</p>
                </div>
            </div>

            {{-- Win Rate Procenat --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 border-t-4 {{ $stats['win_rate'] >= 50 ? 'border-t-emerald-500' : 'border-t-rose-500' }} rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <p class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">Stopa Uspješnosti</p>
                <h3 class="text-4xl font-black {{ $stats['win_rate'] >= 50 ? 'text-emerald-600 dark:text-emerald-500' : 'text-rose-600 dark:text-rose-500' }}">{{ $stats['win_rate'] }}%</h3>
                
                {{-- Progress Bar za Win Rate --}}
                <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mt-4 mb-3 overflow-hidden">
                    <div class="h-full {{ $stats['win_rate'] >= 50 ? 'bg-emerald-500' : 'bg-rose-500' }} rounded-full" style="width: {{ $stats['win_rate'] }}%"></div>
                </div>
                <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-800/50 px-2.5 py-1 rounded border border-slate-100 dark:border-slate-800 text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <span>Izgubljeno: <span class="text-rose-600 dark:text-rose-500">{{ $stats['izgubljeni'] }}</span></span>
                </div>
            </div>
        </div>

        {{-- TABELA ZADNJIH AKTIVNOSTI --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            
            {{-- Header tabele --}}
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 dark:text-white flex items-center gap-3">
                    <span class="bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 w-8 h-8 rounded-lg flex items-center justify-center text-sm border border-indigo-200 dark:border-indigo-500/20">
                        <i class="fa-solid fa-list-check"></i>
                    </span> 
                    Registar Tendera
                </h3>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead class="bg-white dark:bg-slate-900 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 w-2/5">Ugovorni Organ / Predmet</th>
                            <th class="px-6 py-4 text-center">Korisnik</th>
                            <th class="px-6 py-4 text-center">Zadnja izmjena</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Akcija</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentTenders as $tender)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-5 max-w-md">
                                <div class="mb-2">
                                    <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-1 leading-none">
                                        {{ $tender->procedure->contracting_authority_name ?? 'Nepoznat Ugovorni Organ' }}
                                    </p>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white leading-snug group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        {{ str($tender->procedure->name ?? 'Nepoznat predmet nabavke')->limit(75) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        ID: {{ $tender->procedure_id }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                        <i class="fa-solid fa-coins mr-1.5 opacity-70"></i> {{ number_format($tender->ukupna_vrijednost ?? 0, 2, ',', '.') }} KM
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-lg">
                                    <div class="w-5 h-5 rounded bg-indigo-600 text-white flex items-center justify-center text-[10px] font-black">
                                        {{ substr($tender->user->first_name ?? 'N', 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $tender->user->first_name  ?? 'Korisnik' }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-2 text-slate-500 dark:text-slate-400">
                                    <i class="fa-regular fa-calendar-days text-xs"></i>
                                    <p class="text-xs font-mono font-bold">{{ $tender->updated_at->format('d.m.Y') }}</p>
                                </div>
                            </td>

                            <td class="px-6 py-5 text-center whitespace-nowrap">
                                @php
                                    $statusStyles = match($tender->status) {
                                        'accepted' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/30',
                                        'documentation_uploaded' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/30 animate-pulse',
                                        'offer_submitted', 'completed' => 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/30',
                                        'won' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30',
                                        'lost' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/30',
                                        'rejected' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                                        default => 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700'
                                    };
                                    $statusLabels = [
                                        'accepted' => 'Prihvaćen', 'documentation_uploaded' => 'U Procesu',
                                        'offer_submitted' => 'Predat', 'completed' => 'Predat',
                                        'won' => 'Dobijen', 'lost' => 'Izgubljen', 'rejected' => 'Odbijen'
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $statusStyles }} border rounded-lg text-[10px] font-black uppercase tracking-widest">
                                    {{ $statusLabels[$tender->status] ?? 'Novo' }}
                                </span>
                            </td>

                            <td class="px-6 py-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="toggleExpand({{ $tender->id }})" class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest px-3 py-2 rounded-lg border transition-colors {{ $expandedTender === $tender->id ? 'bg-indigo-600 text-white border-indigo-600' : 'text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-900 border-indigo-200 dark:border-indigo-500/30 hover:bg-indigo-50 dark:hover:bg-indigo-500/10' }}">
                                        <i class="fa-solid {{ $expandedTender === $tender->id ? 'fa-chevron-up' : 'fa-circle-info' }}"></i> Detalji
                                    </button>
                                    <button wire:click="openModal({{ $tender->id }})" class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 dark:hover:border-indigo-600 transition-colors">
                                        <i class="fa-solid fa-expand"></i> Pregled
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        {{-- EXPAND RED SA FINANSIJSKOM KALKULACIJOM --}}
                        @if($expandedTender === $tender->id)
                        @php
                            $aiData = $tender->ai_parsed_data ?? [];
                            $rizik  = strtoupper($aiData['ai_uprava']['rizik_nivo'] ?? '');
                            $rizikBoja = match($rizik) {
                                'VISOK' => 'text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30',
                                'SREDNJI' => 'text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30',
                                'NIZAK' => 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30',
                                default => 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700'
                            };

                            // Kalkulacija Finansija
                            $ukupnoNabavna = 0;
                            $ukupnoPonudbena = 0;
                            
                            foreach ($aiData['artikli_generalno'] ?? [] as $art) {
                                if (isset($art['ai_match']['selected'])) {
                                    $kol = max(1, floatval($art['kolicina'] ?? 1));
                                    $nab = floatval($art['ai_match']['selected']['anPrice'] ?? 0); 
                                    
                                    if (isset($art['ai_match']['ponudbena_cijena']) && $art['ai_match']['ponudbena_cijena'] > 0) {
                                        $ukupnoPonudbena += floatval($art['ai_match']['ponudbena_cijena']); 
                                    } else {
                                        $pon = floatval($art['ai_match']['selected']['anRTPrice'] ?? 0);
                                        $ukupnoPonudbena += ($pon * $kol);
                                    }
                                    $ukupnoNabavna += ($nab * $kol);
                                }
                            }

                            $mtPrihvaceni = is_array($tender->accepted_lots) ? $tender->accepted_lots : (json_decode($tender->accepted_lots, true) ?? []);
                            foreach ($aiData['lotovi'] ?? [] as $idx => $lot) {
                                if (empty($mtPrihvaceni) || in_array($idx, $mtPrihvaceni)) {
                                    foreach ($lot['artikli'] ?? [] as $art) {
                                        if (isset($art['ai_match']['selected'])) {
                                            $kol = max(1, floatval($art['kolicina'] ?? 1));
                                            $nab = floatval($art['ai_match']['selected']['anPrice'] ?? 0);
                                            
                                            if (isset($art['ai_match']['ponudbena_cijena']) && $art['ai_match']['ponudbena_cijena'] > 0) {
                                                $ukupnoPonudbena += floatval($art['ai_match']['ponudbena_cijena']);
                                            } else {
                                                $pon = floatval($art['ai_match']['selected']['anRTPrice'] ?? 0);
                                                $ukupnoPonudbena += ($pon * $kol);
                                            }
                                            $ukupnoNabavna += ($nab * $kol);
                                        }
                                    }
                                }
                            }

                            $zarada = $ukupnoPonudbena - $ukupnoNabavna;
                            $marzaPosto = $ukupnoPonudbena > 0 ? ($zarada / $ukupnoPonudbena) * 100 : 0;
                        @endphp

                        <tr class="bg-slate-50 dark:bg-slate-900 border-y border-slate-200 dark:border-slate-800">
                            <td colspan="5" class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    
                                    {{-- Info Kartica --}}
                                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 shadow-sm">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-2"><i class="fa-solid fa-circle-info text-indigo-500"></i> Informacije</p>
                                        <div class="space-y-3 text-sm">
                                            <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-700">
                                                <span class="text-slate-600 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">Vrijednost</span>
                                                <span class="font-mono font-black text-slate-800 dark:text-slate-200">{{ number_format($tender->ukupna_vrijednost ?? 0, 2, ',', '.') }} KM</span>
                                            </div>
                                            <div class="flex justify-between items-center pt-1">
                                                <span class="text-slate-600 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">Nivo rizika</span>
                                                <span class="font-black text-[10px] px-2 py-0.5 rounded border {{ $rizikBoja }}">{{ $rizik ?: 'NIJE ANALIZIRANO' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- AI Zapažanja Kartica --}}
                                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 shadow-sm flex flex-col">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-2"><i class="fa-solid fa-robot text-indigo-500"></i> AI Zapažanja</p>
                                        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-900 p-3 rounded-lg border border-slate-100 dark:border-slate-800 flex-1">{{ $aiData['ai_uprava']['rizik_razlog'] ?? 'Nema zabilježene AI analize.' }}</p>
                                    </div>
                                    
                                    {{-- Finansije Kartica --}}
                                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 shadow-sm flex flex-col justify-between">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4 flex items-center justify-between">
                                            <span class="flex items-center gap-2"><i class="fa-solid fa-calculator text-indigo-500"></i> Interna Marža</span>
                                            @if($ukupnoPonudbena > 0)
                                                <span class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 px-2 py-0.5 rounded border border-indigo-200 dark:border-indigo-500/30 text-[9px] tracking-widest">MAPIRANO</span>
                                            @endif
                                        </p>
                                        
                                        @if($ukupnoPonudbena == 0 && $ukupnoNabavna == 0)
                                            <div class="h-full flex flex-col items-center justify-center text-center py-2 opacity-60">
                                                <i class="fa-solid fa-boxes-stacked text-2xl text-slate-400 dark:text-slate-500 mb-2"></i>
                                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Nema artikala</p>
                                            </div>
                                        @else
                                            <div class="space-y-2.5 mb-4">
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-slate-600 dark:text-slate-400 font-bold">Nabavna (VPC)</span>
                                                    <span class="font-mono font-bold text-slate-800 dark:text-slate-300">{{ number_format($ukupnoNabavna, 2, ',', '.') }} KM</span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-slate-600 dark:text-slate-400 font-bold">Ponuda</span>
                                                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ number_format($ukupnoPonudbena, 2, ',', '.') }} KM</span>
                                                </div>
                                                
                                                <div class="w-full h-px bg-slate-100 dark:bg-slate-700 my-2"></div>
                                                
                                                <div class="flex justify-between items-end">
                                                    <div>
                                                        <p class="text-[9px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-widest mb-1">Zarada</p>
                                                        <p class="font-mono text-lg font-black {{ $zarada >= 0 ? 'text-emerald-600 dark:text-emerald-500' : 'text-rose-600 dark:text-rose-500' }} leading-none">
                                                            {{ $zarada > 0 ? '+' : '' }}{{ number_format($zarada, 2, ',', '.') }} <span class="text-[10px] opacity-70 font-sans">KM</span>
                                                        </p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-[9px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-widest mb-1">Marža</p>
                                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[10px] font-black {{ $marzaPosto >= 15 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30' : ($marzaPosto > 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400 border border-rose-200 dark:border-rose-500/30') }}">
                                                            {{ number_format($marzaPosto, 1, ',', '.') }}%
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            @php $progressWidth = min(100, max(0, ($marzaPosto / 50) * 100)); @endphp
                                            <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden mt-auto">
                                                <div class="h-full {{ $marzaPosto >= 15 ? 'bg-emerald-500' : ($marzaPosto > 0 ? 'bg-amber-500' : 'bg-rose-500') }} transition-all duration-1000 ease-out" style="width: {{ $progressWidth }}%"></div>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="inline-flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                    <i class="fa-solid fa-folder-open text-3xl mb-3 opacity-50"></i>
                                    <p class="text-sm font-bold tracking-wide">Trenutno nema pronađenih tendera.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
                    {{ $recentTenders->links() }}
                </div>
            </div>
        </div>

    </div>

   {{-- ═══════════ PREGLED PANEL (MODAL) ═══════════ --}}
    @if($modalTenderId)
    @php
        $mt = \App\Models\TenderWorkflow::with(['procedure','user','tasks'])->withSum('lots as ukupna_vrijednost','estimated_value')->find($modalTenderId);
        $mtAi = $mt->ai_parsed_data ?? [];
        $mtPrihvaceni = is_array($mt->accepted_lots) ? $mt->accepted_lots : (json_decode($mt->accepted_lots, true) ?? []);

        $odabraniLotovi = [];
        foreach ($mtAi['lotovi'] ?? [] as $idx => $lot) {
            if (empty($mtPrihvaceni) || in_array($idx, $mtPrihvaceni)) {
                $odabraniLotovi[] = $lot['naziv'] ?? ('LOT ' . ($lot['broj'] ?? ($idx + 1)));
            }
        }
        if (empty($odabraniLotovi) && !empty($mtAi['artikli_generalno'])) $odabraniLotovi[] = 'Generalna ponuda (bez lotova)';

        $taskiDokumenti = $mt->tasks ?? collect();
        $dtPreuzeto = $mt->created_at;
        $dtDokumentacija = $taskiDokumenti->where('status', 'pribavljeno')->max('completed_at');
        $dtPoslano = in_array($mt->status, ['offer_submitted','won','lost','completed']) ? $mt->updated_at : null;
        $dtZavrseno = in_array($mt->status, ['won','lost','rejected']) ? $mt->updated_at : null;

        $danaDokDo = $dtPreuzeto && $dtDokumentacija ? (int) $dtPreuzeto->diffInDays(\Carbon\Carbon::parse($dtDokumentacija)) : null;
        $danaPredDo = $dtPreuzeto && $dtPoslano ? (int) $dtPreuzeto->diffInDays($dtPoslano) : null;

        $mtPob = $mt->winner_supplier ?: ($mtAi['konkurencija']['ime'] ?? '');
        $mtPobCijena = $mt->final_price ?: ($mtAi['konkurencija']['cijena'] ?? 0);
        $mtRizik = strtoupper($mtAi['ai_uprava']['rizik_nivo'] ?? '');

        $statusInfo = match($mt->status) {
            'won'                    => ['Dobijen',            'emerald', 'fa-trophy'],
            'accepted'               => ['Prihvaćen',          'blue',    'fa-file-circle-check'],
            'documentation_uploaded' => ['Dokumentacija',      'amber',   'fa-folder-open'],
            'offer_submitted'        => ['Ponuda Poslana',     'indigo',  'fa-paper-plane'],
            'lost'                   => ['Izgubljen',          'rose',    'fa-circle-xmark'],
            'rejected'               => ['Odbijen',            'slate',   'fa-ban'],
            default                  => ['Novo',               'slate',   'fa-file'],
        };
    @endphp

    {{-- MODAL CONTAINER --}}
    <div class="fixed inset-0 z-[60] flex justify-end"
         x-data="{ open: false }"
         x-init="setTimeout(() => open = true, 50)"
         @keydown.escape.window="open = false; setTimeout(() => $wire.closeModal(), 300)">

        {{-- BACKDROP --}}
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
             x-show="open"
             x-transition.opacity.duration.300ms
             x-on:click="open = false; setTimeout(() => $wire.closeModal(), 300)"></div>

        {{-- MODAL PANEL --}}
        <div class="relative z-10 w-full max-w-2xl h-full bg-white dark:bg-slate-900 flex flex-col shadow-2xl overflow-hidden border-l border-slate-200 dark:border-slate-800"
             x-show="open"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">

            {{-- HEADER --}}
            <div class="shrink-0 relative bg-slate-900 dark:bg-slate-950 px-8 py-6 border-b border-slate-800">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 mb-1">{{ $mt->procedure->contracting_authority_name ?? '—' }}</p>
                        <h2 class="text-base font-black text-white leading-snug">{{ str($mt->procedure->name ?? '—')->limit(90) }}</h2>
                    </div>
                    <button x-on:click="open = false; setTimeout(() => $wire.closeModal(), 300)" class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-rose-500/80 text-white/70 hover:text-white transition-colors text-sm group border border-white/10">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-white/10 text-white/90 border border-white/10 text-[10px] font-black uppercase tracking-widest">
                        <i class="fa-solid {{ $statusInfo[2] }}"></i> {{ $statusInfo[0] }}
                    </span>
                    @if($mtRizik)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-white/10 text-white/90 border border-white/10 text-[10px] font-black uppercase tracking-widest">
                        <i class="fa-solid fa-shield-halved text-amber-400"></i> Rizik: {{ $mtRizik }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- BODY --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-6 bg-slate-50 dark:bg-slate-900">

                {{-- TIMELINE --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-timeline text-indigo-500"></i> Vremenski tok obrade
                    </h3>
                    
                    <div class="relative pl-2">
                        <div class="absolute left-[15px] top-2 bottom-2 w-0.5 bg-slate-200 dark:bg-slate-700"></div>
                        
                        <div class="space-y-6 relative z-10">
                            
                            {{-- Preuzeto --}}
                            <div class="flex items-start gap-4">
                                <div class="shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs ring-4 ring-white dark:ring-slate-800 shadow-sm border border-blue-200 dark:border-blue-500/30">
                                    <i class="fa-solid fa-file-signature"></i>
                                </div>
                                <div class="flex-1 pt-1.5">
                                    <p class="text-sm font-black text-slate-800 dark:text-white leading-none">Tender preuzet u sistem</p>
                                    <p class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400 mt-1.5">{{ $dtPreuzeto->format('d.m.Y. \u H:i') }}</p>
                                </div>
                            </div>
                            
                            {{-- Dokumentacija --}}
                            @if($dtDokumentacija)
                            <div class="flex items-start gap-4">
                                <div class="shrink-0 w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs ring-4 ring-white dark:ring-slate-800 shadow-sm border border-amber-200 dark:border-amber-500/30">
                                    <i class="fa-solid fa-folder-check"></i>
                                </div>
                                <div class="flex-1 pt-1.5">
                                    <p class="text-sm font-black text-slate-800 dark:text-white leading-none">Dokumentacija kompletirana</p>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <p class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($dtDokumentacija)->format('d.m.Y. \u H:i') }}</p>
                                        @if($danaDokDo !== null)
                                        <span class="text-[9px] font-black px-1.5 py-0.5 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 rounded border border-amber-200 dark:border-amber-500/30">+{{ $danaDokDo }} dana</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Poslano --}}
                            @if($dtPoslano)
                            <div class="flex items-start gap-4">
                                <div class="shrink-0 w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs ring-4 ring-white dark:ring-slate-800 shadow-sm border border-indigo-200 dark:border-indigo-500/30">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </div>
                                <div class="flex-1 pt-1.5">
                                    <p class="text-sm font-black text-slate-800 dark:text-white leading-none">Ponuda uspješno predana</p>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <p class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400">{{ $dtPoslano->format('d.m.Y. \u H:i') }}</p>
                                        @if($danaPredDo !== null)
                                        <span class="text-[9px] font-black px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 rounded border border-indigo-200 dark:border-indigo-500/30">+{{ $danaPredDo }} dana</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Završeno --}}
                            @if($dtZavrseno)
                            <div class="flex items-start gap-4">
                                <div class="shrink-0 w-8 h-8 rounded-full {{ $mt->status === 'won' ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30' : 'bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-500/30' }} flex items-center justify-center text-xs ring-4 ring-white dark:ring-slate-800 shadow-sm border">
                                    <i class="fa-solid {{ $mt->status === 'won' ? 'fa-trophy' : 'fa-circle-xmark' }}"></i>
                                </div>
                                <div class="flex-1 pt-1.5">
                                    <p class="text-sm font-black text-slate-800 dark:text-white leading-none">{{ $mt->status === 'won' ? 'Tender zvanično dobijen' : 'Tender nažalost izgubljen' }}</p>
                                    <p class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400 mt-1.5">{{ $dtZavrseno->format('d.m.Y. \u H:i') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- POBJEDNIK / REZULTAT --}}
                @if(in_array($mt->status, ['won','lost','rejected']) && !empty($mtPob))
                @php $isWon = $mt->status === 'won'; @endphp
                <div class="rounded-xl p-5 border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 {{ $isWon ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-500/30' : 'bg-rose-50 dark:bg-rose-900/20 border-rose-200 dark:border-rose-500/30' }}">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] mb-1.5 {{ $isWon ? 'text-emerald-700 dark:text-emerald-500' : 'text-rose-700 dark:text-rose-500' }}">
                            <i class="fa-solid {{ $isWon ? 'fa-trophy' : 'fa-flag-checkered' }}"></i> {{ $isWon ? 'Pobjednik — Naša Kompanija' : 'Pobjednik Tendera' }}
                        </p>
                        <p class="text-lg font-black text-slate-900 dark:text-white">{{ $mtPob }}</p>
                    </div>
                    @if($mtPobCijena)
                    <div class="sm:text-right shrink-0 bg-white dark:bg-slate-900 p-2.5 rounded-lg border {{ $isWon ? 'border-emerald-200 dark:border-emerald-500/30' : 'border-rose-200 dark:border-rose-500/30' }}">
                        <p class="text-[9px] font-bold text-slate-500 dark:text-slate-400 mb-0.5 uppercase tracking-widest">Ugovorena Cijena</p>
                        <p class="text-xl font-black font-mono {{ $isWon ? 'text-emerald-600 dark:text-emerald-500' : 'text-rose-600 dark:text-rose-500' }} leading-none">
                            {{ number_format($mtPobCijena, 2, ',', '.') }}<span class="text-xs ml-1 opacity-60 font-sans">KM</span>
                        </p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- KOMENTAR --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-indigo-500"></i> Dodatna Bilješka
                    </h3>
                    <div class="relative">
                        <textarea wire:model="modalComment" rows="3"
                            placeholder="Zapažanja, smjernice, napomene za ovaj tender..."
                            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-800 dark:text-slate-200 p-4 rounded-lg outline-none focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all resize-none"></textarea>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button wire:click="saveModalComment" class="bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-black uppercase tracking-widest px-5 py-2.5 rounded-lg transition-colors shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk"></i> Spasi bilješku
                        </button>
                    </div>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="shrink-0 px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between gap-4">
                <button x-on:click="open = false; setTimeout(() => $wire.closeModal(), 300)" class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white px-4 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Zatvori Pregled
                </button>
                <a href="{{ route('tender.progress', $mt->id) }}" wire:navigate class="px-5 py-2.5 bg-slate-900 dark:bg-white hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-lg transition-colors shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-screwdriver-wrench"></i> Otvori radni proces
                </a>
            </div>

        </div>
    </div>
    @endif

</div>