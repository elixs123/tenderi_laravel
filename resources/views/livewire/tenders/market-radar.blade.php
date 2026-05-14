<div class="min-h-screen bg-slate-50 dark:bg-[#020617] p-4 lg:p-8 transition-colors duration-300">
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[900px] h-[600px] bg-blue-600/5 dark:bg-blue-600/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        {{-- HEADER --}}
        <header class="mb-10 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
            <div>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter text-slate-900 dark:text-white">
                    Tender <span class="text-blue-600 dark:text-blue-500">Radar</span>
                </h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.3em] mt-1">
                    eJN Live — Agencija za javne nabavke BiH
                </p>
            </div>

            <div class="flex flex-wrap gap-3 w-full lg:w-auto">
                <div class="relative flex-1 lg:flex-none">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input wire:model.live.debounce.400ms="search"
                           type="text"
                           placeholder="Pretraži..."
                           class="w-full lg:w-64 bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl py-3 pl-10 pr-4 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <select wire:model.live="selectedUser"
                        class="flex-1 lg:flex-none lg:w-56 bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl py-3 px-4 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    <option value="">Svi referenti</option>
                    @foreach($referents as $ref)
                        <option value="{{ $ref->id }}">{{ $ref->first_name }} {{ $ref->last_name }}</option>
                    @endforeach
                </select>
                <button wire:click="$refresh"
                        class="p-3 bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-500 hover:text-blue-500 transition"
                        title="Osvježi podatke">
                    <i class="fa-solid fa-arrows-rotate text-xs" wire:loading.class="animate-spin" wire:target="$refresh"></i>
                </button>
            </div>
        </header>

        {{-- TABS --}}
        @php
            $tabs = [
                'procedures' => ['label' => 'Aktivni tenderi',      'icon' => 'fa-circle-dot',    'color' => 'blue',   'desc' => 'Trenutno otvoreni'],
                'awards'     => ['label' => 'Dodjele ugovora',      'icon' => 'fa-trophy',        'color' => 'emerald','desc' => 'Ko je dobio i po kojim cijenama'],
                'planned'    => ['label' => 'Planovi nabavki',      'icon' => 'fa-calendar-days', 'color' => 'violet', 'desc' => 'Šta se sprema'],
                'pi'         => ['label' => 'Najave (PI)',          'icon' => 'fa-bell',          'color' => 'amber',  'desc' => 'Uskoro na tržištu'],
                'notices'    => ['label' => 'Obavijesti o dodjeli', 'icon' => 'fa-file-contract', 'color' => 'rose',   'desc' => 'Formalne odluke'],
            ];
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-8">
            @foreach($tabs as $key => $tab)
                <button wire:click="setTab('{{ $key }}')"
                        class="text-left p-4 rounded-2xl border transition-all
                            {{ $activeTab === $key
                                ? 'bg-'.$tab['color'].'-600 border-'.$tab['color'].'-600 text-white shadow-lg shadow-'.$tab['color'].'-500/20'
                                : 'bg-white dark:bg-slate-900/40 border-slate-200 dark:border-slate-800 hover:border-'.$tab['color'].'-300 dark:hover:border-'.$tab['color'].'-500/40' }}">
                    <i class="fa-solid {{ $tab['icon'] }} mb-2 block
                        {{ $activeTab === $key ? 'text-white/80' : 'text-'.$tab['color'].'-500' }}"></i>
                    <p class="text-[10px] font-black uppercase leading-tight
                        {{ $activeTab === $key ? 'text-white' : 'text-slate-700 dark:text-slate-300' }}">
                        {{ $tab['label'] }}
                    </p>
                    <p class="text-[9px] mt-0.5
                        {{ $activeTab === $key ? 'text-white/60' : 'text-slate-400' }}">
                        {{ $tab['desc'] }}
                    </p>
                </button>
            @endforeach
        </div>

        {{-- CPV filter info --}}
        @if($selectedUser && count($cpvCodes) > 0)
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-500/5 border border-blue-200 dark:border-blue-500/20 rounded-2xl flex flex-wrap items-center gap-3">
                <i class="fa-solid fa-filter text-blue-500"></i>
                <span class="text-xs font-bold text-blue-700 dark:text-blue-400">
                    Filtrira se po <b>{{ count($cpvCodes) }} CPV kodova</b> odabranog referenta
                </span>
                @foreach(array_slice($cpvCodes, 0, 5) as $code)
                    <span class="text-[9px] font-mono font-black px-2 py-0.5 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-lg">{{ $code }}</span>
                @endforeach
                @if(count($cpvCodes) > 5)
                    <span class="text-[9px] text-blue-400 font-bold">+{{ count($cpvCodes) - 5 }} više</span>
                @endif
            </div>
        @endif

        {{-- LOADING --}}
        <div wire:loading wire:target="setTab,selectedUser,search,nextPage,prevPage,$refresh"
             class="flex justify-center py-20">
            <div class="flex flex-col items-center gap-3 text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-blue-500 text-2xl"></i>
                <span class="text-xs font-black uppercase tracking-widest">Učitavam sa eJN...</span>
            </div>
        </div>

        {{-- FEED --}}
        @php
            $activeConfig = $tabs[$activeTab] ?? ['color' => 'blue', 'icon' => 'fa-file'];
            $color = $activeConfig['color'];
            $icon  = $activeConfig['icon'];

            $expiresSoon = '';
        @endphp

        <div wire:loading.remove wire:target="setTab,selectedUser,search,nextPage,prevPage,$refresh"
             class="space-y-4">
            @forelse($items as $item)
                @php
                    // Datum objave
                    $announced  = !empty($item['Announced'])                  ? \Carbon\Carbon::parse($item['Announced'])                  : null;
                    $contractDate = !empty($item['ContractDate'])              ? \Carbon\Carbon::parse($item['ContractDate'])              : null;
                    $startDate  = !empty($item['EstimatedProcedureStartDate']) ? \Carbon\Carbon::parse($item['EstimatedProcedureStartDate']) : null;
                    $piStart    = !empty($item['EstimatedProcurementStartDate']) ? \Carbon\Carbon::parse($item['EstimatedProcurementStartDate']) : null;
                    $masterEnd  = !empty($item['MasterAgreementEndDate'])      ? \Carbon\Carbon::parse($item['MasterAgreementEndDate'])      : null;

                    // Naziv
                    $title = $item['Name'] ?? $item['ProcedureName'] ?? $item['LotName'] ?? '—';

                    // Vrijednost
                    $value = $item['Value'] ?? $item['EstimatedValue'] ?? $item['AnnualValue'] ?? 0;

                    // Status
                    $status = $item['Status'] ?? null;

                    // Je li okvirni sporazum
                    $isFramework = $item['IsMasterAgreement'] ?? false;

                    // Tip procedure
                    $procType = match($item['Type'] ?? $item['ProcedureType'] ?? '') {
                        'OpenProcedure'       => 'Otvoreni postupak',
                        'CompetitiveRequest'  => 'Konkurentski zahtjev',
                        'DirectAgreement'     => 'Direktni sporazum',
                        'NegotiatedProcedure' => 'Pregovarački postupak',
                        'RestrictedProcedure' => 'Ograničeni postupak',
                        default               => $item['Type'] ?? $item['ProcedureType'] ?? null,
                    };

                    // Tip ugovora
                    $contractType = match($item['ContractType'] ?? '') {
                        'Works'    => 'Radovi',
                        'Goods'    => 'Robe',
                        'Services' => 'Usluge',
                        default    => $item['ContractType'] ?? null,
                    };

                    // Uskoro ističe okvirni
                    $expiresSoon = $masterEnd && !$masterEnd->isPast() && $masterEnd->diffInMonths(now()) <= 3;
                @endphp

                <div class="group relative overflow-hidden bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800/60 rounded-[2rem] p-6 transition-all hover:shadow-xl hover:shadow-{{ $color }}-500/5 hover:-translate-y-0.5">

                    {{-- Accent bar --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-{{ $color }}-500 rounded-l-[2rem]"></div>

                    {{-- Expires soon badge --}}
                    @if(isset($expiresSoon) && $expiresSoon)
                        <div class="absolute top-4 right-4">
                            <span class="text-[9px] font-black px-3 py-1.5 bg-rose-500 text-white rounded-xl animate-pulse">
                                <i class="fa-solid fa-clock mr-1"></i>Okvirni uskoro ističe
                            </span>
                        </div>
                    @endif

                    <div class="flex flex-col lg:flex-row justify-between gap-6">

                        {{-- Lijevo --}}
                        <div class="flex gap-5 flex-1 min-w-0">
                            <div class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center text-xl
                                bg-{{ $color }}-50 dark:bg-{{ $color }}-500/10
                                text-{{ $color }}-600 dark:text-{{ $color }}-500
                                border border-{{ $color }}-100 dark:border-{{ $color }}-500/20">
                                <i class="fa-solid {{ $icon }}"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                {{-- Badges --}}
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    @if($announced ?? $contractDate)
                                        <span class="text-[10px] font-bold text-slate-400">
                                            <i class="fa-regular fa-clock mr-1"></i>
                                            {{ ($announced ?? $contractDate)->format('d.m.Y') }}
                                        </span>
                                    @endif

                                    @if($procType)
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded-lg uppercase
                                            bg-{{ $color }}-50 dark:bg-{{ $color }}-500/5
                                            text-{{ $color }}-700 dark:text-{{ $color }}-400
                                            border border-{{ $color }}-200 dark:border-{{ $color }}-500/20">
                                            {{ $procType }}
                                        </span>
                                    @endif

                                    @if($contractType)
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700">
                                            {{ $contractType }}
                                        </span>
                                    @endif

                                    @if($isFramework)
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                            <i class="fa-solid fa-layer-group mr-1"></i>Okvirni sporazum
                                        </span>
                                    @endif

                                    @if(!empty($item['HasLots']) && $item['HasLots'])
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded-lg bg-violet-500/10 text-violet-600 border border-violet-500/20">
                                            <i class="fa-solid fa-list mr-1"></i>Lotovi
                                        </span>
                                    @endif
                                </div>

                                {{-- Naslov --}}
                                <h3 class="text-base font-black text-slate-900 dark:text-white uppercase leading-tight line-clamp-2 group-hover:text-{{ $color }}-600 dark:group-hover:text-{{ $color }}-400 transition-colors">
                                    {{ $title }}
                                </h3>

                                {{-- Institucija --}}
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase mt-1.5 truncate">
                                    <i class="fa-solid fa-building mr-1.5 text-slate-400"></i>
                                    {{ $item['ContractingAuthorityName'] ?? '—' }}
                                </p>

                                {{-- Lokacija --}}
                                @if(!empty($item['ContractingAuthorityCityName']))
                                    <p class="text-[10px] text-slate-400 font-bold mt-0.5">
                                        <i class="fa-solid fa-location-dot mr-1"></i>
                                        {{ $item['ContractingAuthorityCityName'] }}
                                        @if(!empty($item['ContractingAuthorityAdministrativeUnitName']))
                                            · {{ $item['ContractingAuthorityAdministrativeUnitName'] }}
                                        @endif
                                    </p>
                                @endif

                                {{-- CPV (samo PlannedProcurements ima) --}}
                                @if(!empty($item['MainCpvCodeName']))
                                    <div class="mt-2">
                                        <span class="text-[9px] font-mono font-black px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg border border-slate-200 dark:border-slate-700">
                                            {{ $item['MainCpvCodeName'] }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Kratki opis (PI Notices) --}}
                                @if(!empty($item['ShortDescription']))
                                    <p class="text-[10px] text-slate-400 mt-2 line-clamp-2 italic">
                                        {{ $item['ShortDescription'] }}
                                    </p>
                                @endif

                                {{-- Awards specifično --}}
                                @if($activeTab === 'awards')
                                    <div class="mt-3 flex flex-wrap gap-3">
                                        @if(!empty($item['NumberOfReceivedOffers']) && $item['NumberOfReceivedOffers'] > 0)
                                            <span class="text-[9px] font-black px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-lg border border-slate-200 dark:border-slate-700">
                                                <i class="fa-solid fa-users mr-1"></i>{{ $item['NumberOfReceivedOffers'] }} ponuda primljeno
                                            </span>
                                        @endif
                                        @if(!empty($item['AwardCriterion']))
                                            <span class="text-[9px] font-black px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-lg border border-slate-200 dark:border-slate-700">
                                                {{ $item['AwardCriterion'] === 'LowestPrice' ? 'Najniža cijena' : 'Ekonomski najpovoljnija' }}
                                            </span>
                                        @endif
                                        @if(!empty($item['EuFundsUsed']) && $item['EuFundsUsed'])
                                            <span class="text-[9px] font-black px-2 py-0.5 bg-blue-100 dark:bg-blue-500/10 text-blue-600 rounded-lg border border-blue-200 dark:border-blue-500/20">
                                                <i class="fa-solid fa-star-of-life mr-1"></i>EU fondovi
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Broj nabavke --}}
                                @if(!empty($item['Number']) || !empty($item['ProcedureNumber']))
                                    <p class="text-[9px] font-mono text-slate-400 mt-2">
                                        <i class="fa-solid fa-hashtag mr-1"></i>
                                        {{ $item['Number'] ?? $item['ProcedureNumber'] }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Desno: vrijednost i datumi --}}
                        <div class="shrink-0 lg:text-right lg:min-w-[180px] flex flex-row lg:flex-col justify-between lg:justify-start gap-4 lg:gap-4">

                            {{-- Vrijednost --}}
                            @if($value > 0)
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5">
                                        {{ $activeTab === 'awards' ? 'Ugovorena vrijednost' : 'Procijenjena vrijednost' }}
                                    </p>
                                    <p class="text-xl font-black text-{{ $color }}-600 dark:text-{{ $color }}-400 font-mono tracking-tighter">
                                        {{ number_format($value, 2, ',', '.') }}
                                        <span class="text-xs font-bold text-slate-400 ml-0.5">KM</span>
                                    </p>
                                    @if(!empty($item['HighestAcceptableOfferValue']) && $item['HighestAcceptableOfferValue'] != $value)
                                        <p class="text-[9px] text-slate-400 font-bold mt-0.5">
                                            Raspon: {{ number_format($item['LowestAcceptableOfferValue'], 0, ',', '.') }} – {{ number_format($item['HighestAcceptableOfferValue'], 0, ',', '.') }} KM
                                        </p>
                                    @endif
                                </div>
                            @endif

                            {{-- Datum ugovora (awards) --}}
                            @if($contractDate)
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5">Datum ugovora</p>
                                    <p class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                        <i class="fa-solid fa-file-signature mr-1"></i>
                                        {{ $contractDate->format('d.m.Y') }}
                                    </p>
                                </div>
                            @endif

                            {{-- Okvirni ističe --}}
                            @if($masterEnd)
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5">Okvirni ističe</p>
                                    <p class="text-xs font-black {{ $expiresSoon ? 'text-rose-500' : 'text-slate-600 dark:text-slate-300' }}">
                                        <i class="fa-solid fa-hourglass-half mr-1"></i>
                                        {{ $masterEnd->format('d.m.Y') }}
                                    </p>
                                    <p class="text-[9px] text-slate-400">{{ $masterEnd->diffForHumans() }}</p>
                                </div>
                            @endif

                            {{-- Planirani početak --}}
                            @if($startDate || $piStart)
                                @php $planDate = $startDate ?? $piStart; @endphp
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5">Planirani početak</p>
                                    <p class="text-xs font-black text-violet-600 dark:text-violet-400">
                                        <i class="fa-solid fa-calendar-check mr-1"></i>
                                        {{ $planDate->format('d.m.Y') }}
                                    </p>
                                </div>
                            @endif

                            {{-- Tip institucije --}}
                            @if(!empty($item['ContractingAuthorityType']))
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5">Institucija</p>
                                    <p class="text-[10px] font-black text-slate-600 dark:text-slate-300">
                                        {{ match($item['ContractingAuthorityType']) {
                                            'GovernmentInstitution'        => 'Vladina institucija',
                                            'PublicEntity'                 => 'Javno preduzeće',
                                            'SectoralContractingAuthority' => 'Sektorski ugovarač',
                                            default                        => $item['ContractingAuthorityType']
                                        } }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-32 text-center bg-white dark:bg-slate-900/20 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[3rem]">
                    <i class="fa-solid fa-satellite-dish text-5xl text-slate-200 dark:text-slate-700 mb-6 block animate-pulse"></i>
                    <p class="text-slate-400 dark:text-slate-600 font-black uppercase text-xs tracking-[0.3em]">
                        Nema podataka
                    </p>
                </div>
            @endforelse
        </div>

        {{-- PAGINACIJA --}}
        @if(count($items) > 0 || $currentPage > 1)
            <div class="mt-10 flex items-center justify-between">
                <button wire:click="prevPage"
                        @disabled(!$hasPrev)
                        class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs font-black uppercase text-slate-500 hover:text-blue-500 hover:border-blue-300 transition disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-arrow-left"></i> Prethodna
                </button>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Stranica {{ $currentPage }}
                </span>
                <button wire:click="nextPage"
                        @disabled(!$hasNext)
                        class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs font-black uppercase text-slate-500 hover:text-blue-500 hover:border-blue-300 transition disabled:opacity-30 disabled:cursor-not-allowed">
                    Sljedeća <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        @endif

    </div>
</div>