<div class="bg-white dark:bg-slate-950/80 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col gap-3 transition-colors">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800/50 pb-4 transition-colors">
        <div class="flex-1 flex items-center gap-3 text-[14px] text-slate-800 dark:text-slate-200 font-bold transition-colors">
            <span class="text-[10px] font-black text-slate-500 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-7 h-7 flex items-center justify-center rounded-lg shadow-sm dark:shadow-inner">{{ (int)$index + 1 }}..</span>
            <span class="leading-tight">{{ $art['opis'] ?? '' }}</span>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex flex-col gap-2 items-center">
                <div class="bg-indigo-50 dark:bg-indigo-900/20 px-3 py-1.5 rounded-lg border border-indigo-100 dark:border-indigo-500/20 text-center min-w-[90px] w-full transition-colors">
                    <span class="text-sm font-mono text-indigo-700 dark:text-indigo-400 font-black">{{ $art['kolicina'] ?? ''}}</span>
                    <span class="text-[9px] uppercase font-black text-indigo-500/70 ml-1">{{ $art['jm'] ?? '' }}</span>
                </div>
                @php
                    $match = $art['ai_match']['selected'] ?? null;
                    $stockTotal = floatval($match['stock_total'] ?? 0);
                    $trazeno = floatval($art['kolicina'] ?? 0);
                    $stockColor = $stockTotal >= $trazeno ? 'text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' : 'text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-500/30 hover:bg-rose-50 dark:hover:bg-rose-500/10';
                @endphp
                @if($match)
                <div x-data="{ showStock: false }" class="relative w-full">
                    <button @click="showStock = !showStock" class="w-full flex justify-between items-center px-2 py-1 border rounded text-[9px] font-black uppercase {{ $stockColor }} transition-all shadow-sm">
                        <span><i class="fa-solid fa-boxes-stacked"></i> Stanje: {{ $stockTotal }}</span>
                        <i class="fa-solid fa-chevron-down text-[8px] ml-1"></i>
                    </button>
                    <div x-show="showStock" @click.away="showStock = false" x-transition.opacity class="absolute top-full mt-1 right-0 w-56 bg-white dark:bg-[#020617] border border-slate-200 dark:border-slate-700 rounded-lg shadow-2xl z-50 overflow-hidden" style="display: none;">
                        <div class="p-1.5 bg-slate-100 dark:bg-slate-900 text-[8px] text-slate-600 dark:text-slate-400 font-black uppercase text-center border-b border-slate-200 dark:border-slate-800">Detalji skladišta</div>
                        <div class="max-h-40 overflow-y-auto custom-scrollbar">
                            @forelse($match['stock_details'] ?? [] as $wh)
                                <div class="flex justify-between items-center px-3 py-2 border-b border-slate-100 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                    <span class="text-[9px] text-slate-700 dark:text-slate-300 font-bold uppercase truncate pr-2">{{ $wh['acWarehouse'] ?? 'Skladiste' }}</span>
                                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-mono font-black">{{ $wh['anStock'] ?? 0 }}</span>
                                </div>
                            @empty <div class="p-3 text-center text-[9px] text-slate-500 italic">Nema robe.</div> @endforelse
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="flex flex-col items-end gap-2 mt-2">
                <div class="flex gap-2">
                    <div class="relative">
                        <span class="absolute -top-4 left-1 text-[9px] text-slate-500 dark:text-slate-400 uppercase font-black tracking-widest">NABAVNA</span>
                        <input wire:model.live="{{ $type === 'lot' ? "lotPurchasePrices.$parentIndex.$index" : "purchasePrices.$index" }}" type="number" step="0.01" readonly class="w-24 bg-slate-100 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 text-xs font-mono p-2 rounded-lg border border-slate-200 dark:border-slate-800 cursor-not-allowed">
                    </div>
                    <div class="relative">
                        <span class="absolute -top-4 left-1 text-[9px] text-indigo-600 dark:text-indigo-400 uppercase font-black tracking-widest">PONUDA</span>
                        <input wire:model.live="{{ $type === 'lot' ? "lotOfferPrices.$parentIndex.$index" : "offerPrices.$index" }}" type="number" step="0.01" class="w-24 bg-white dark:bg-slate-900 focus:bg-slate-50 dark:focus:bg-slate-800 focus:ring-1 focus:ring-indigo-400 dark:focus:ring-indigo-500 text-slate-900 dark:text-white text-xs font-mono p-2 rounded-lg border border-slate-300 dark:border-slate-700 outline-none transition-all">
                    </div>
                </div>
                @php
                    $pArr = $type === 'lot' ? ($lotPurchasePrices[$parentIndex][$index] ?? 0) : ($purchasePrices[$index] ?? 0);
                    $oArr = $type === 'lot' ? ($lotOfferPrices[$parentIndex][$index] ?? 0) : ($offerPrices[$index] ?? 0);
                    $anPrice = floatval($pArr);
                    $anRTPrice = floatval($oArr);
                    $zarada = $anRTPrice - $anPrice;
                    $marza = $anPrice > 0 ? ($zarada / $anPrice) * 100 : 0;
                    $bojaZarade = $zarada > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($zarada < 0 ? 'text-rose-600 dark:text-rose-500' : 'text-slate-500');
                    $bgZarade = $zarada > 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20' : ($zarada < 0 ? 'bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/20' : 'bg-slate-100 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700');
                @endphp
                <div class="flex items-center gap-3 px-3 py-1.5 rounded-lg border {{ $bgZarade }} transition-colors">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Marža:</span>
                    <span class="text-xs font-mono font-black {{ $bojaZarade }}">{{ number_format($marza, 1) }}%</span>
                    <div class="w-px h-3 bg-slate-300 dark:bg-slate-700"></div>
                    <span class="text-xs font-mono font-black {{ $bojaZarade }}">{{ number_format($zarada, 2) }} KM</span>
                </div>
            </div>
        </div>
    </div>

    @if(isset($art['ai_match']))
    <div class="{{ $type === 'lot' ? 'ml-10' : '' }}">
        @php
            $match = $art['ai_match']['selected'] ?? null;
            $isManual = $art['ai_match']['is_manual'] ?? false;
            $pct = $match['percent'] ?? 0;
            $borderColor = $pct >= 80 ? 'border-emerald-300 dark:border-emerald-500/30' : ($pct >= 50 ? 'border-amber-300 dark:border-amber-500/30' : 'border-rose-300 dark:border-rose-500/20');
            $textColor = $pct >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($pct >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400');
            $badgeStyle = $pct >= 80 ? 'bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-500 border-emerald-200 dark:border-emerald-500/20' : ($pct >= 50 ? 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-500 border-amber-200 dark:border-amber-500/20' : 'bg-rose-100 dark:bg-rose-500/10 text-rose-700 dark:text-rose-500 border-rose-200 dark:border-rose-500/20');
        @endphp
        <div x-data="{ editMode: false, searchQuery: '', isSearching: false, searchResults: [], doSearch() { if(this.searchQuery.length < 3) return; this.isSearching = true; $wire.searchManual(this.searchQuery).then(res => { this.searchResults = res; this.isSearching = false; }); } }">
            <div @click="editMode = !editMode" class="flex items-center justify-between bg-slate-50 dark:bg-slate-900/60 p-2.5 rounded-lg border {{ $borderColor }} cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                <div class="flex items-center gap-3 flex-1 overflow-hidden">
                    <i class="fa-solid fa-link text-slate-400 dark:text-slate-500 text-[10px]"></i>
                    @if($match)
                        <span class="text-xs font-mono {{ $textColor }} truncate"><span class="font-black">{{ $match['acIdent'] }}</span> - {{ $match['acName'] }}</span>
                        <span class="px-2 py-0.5 border rounded text-[9px] font-black tracking-wider {{ $badgeStyle }}">{{ $pct }}% MATCH @if($isManual) <i class="fa-solid fa-user-check"></i> @endif</span>
                    @else <span class="text-xs font-mono text-rose-500/70 italic">Nema mapiranog artikla</span> @endif
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 dark:text-slate-500 transition-transform duration-300" :class="{'rotate-180': editMode}"></i>
            </div>
            <div x-show="editMode" x-collapse class="mt-2 bg-white dark:bg-[#020617] border border-indigo-200 dark:border-indigo-500/30 rounded-xl p-3 shadow-xl z-40 relative transition-colors" style="display: none;">
                <div class="relative mb-3">
                    <input type="text" x-model="searchQuery" @input.debounce.500ms="doSearch" placeholder="Pretraži bazu artikala ručno..." class="w-full bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs rounded-lg border border-slate-300 dark:border-slate-700 pl-8 pr-10 py-2 focus:ring-1 focus:ring-indigo-400 dark:focus:ring-indigo-500 outline-none transition-all">
                    <i class="fa-solid fa-search absolute left-3 top-2.5 text-slate-400 dark:text-slate-500 text-[11px]" x-show="!isSearching"></i>
                    <i class="fa-solid fa-circle-notch fa-spin absolute left-3 top-2.5 text-indigo-500 dark:text-indigo-400 text-[12px]" x-show="isSearching" style="display: none;"></i>
                </div>
                <div class="space-y-1 max-h-48 overflow-y-auto custom-scrollbar">
                    <template x-if="searchQuery.length < 3">
                        <div>
                            @forelse($art['ai_match']['suggestions'] ?? [] as $sug)
                                @php $sStyle = $sug['percent'] >= 80 ? 'text-emerald-700 dark:text-emerald-500 bg-emerald-100 dark:bg-emerald-500/10' : ($sug['percent'] >= 50 ? 'text-amber-700 dark:text-amber-500 bg-amber-100 dark:bg-amber-500/10' : 'text-rose-700 dark:text-rose-500 bg-rose-100 dark:bg-rose-500/10'); @endphp
                                <button type="button" wire:click="updateArticleMatch('{{ $type }}', {{ $parentIndex ?? 'null' }}, {{ $index }}, '{{ $sug['acIdent'] }}', '{{ addslashes($sug['acName']) }}', {{ $sug['percent'] }}, '{{ addslashes($art['opis']) }}', {{ floatval($sug['anRTPrice'] ?? 0) }}, {{ floatval($sug['stock_total'] ?? 0) }}, '{{ json_encode($sug['stock_details'] ?? []) }}', {{ floatval($sug['anPrice'] ?? 0) }})" @click="editMode = false" class="w-full text-left p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent flex justify-between items-center transition-all group">
                                    <span class="text-[11px] text-slate-700 dark:text-slate-300 font-mono transition-colors group-hover:text-black dark:group-hover:text-white"><span class="font-bold">{{ $sug['acIdent'] }}</span> - {{ $sug['acName'] }}</span>
                                    <span class="text-[10px] font-black px-2 rounded {{ $sStyle }} transition-colors">{{ $sug['percent'] }}%</span>
                                </button>
                            @empty <p class="text-[10px] text-slate-500 italic p-2">Nema preporuka.</p> @endforelse
                        </div>
                    </template>
                    <template x-if="searchQuery.length >= 3">
                        <div>
                            <template x-for="result in searchResults" :key="result.acIdent">
                                <button type="button" x-on:click="$wire.updateArticleMatch('{{ $type }}', {{ $parentIndex ?? 'null' }}, {{ $index }}, result.acIdent, result.acName, 100, '{{ addslashes($art['opis'] ?? '') }}', result.anRTPrice || 0, result.stock_total || 0, JSON.stringify(result.stock_details || []), result.anPrice || 0); editMode = false;" class="w-full text-left p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 border border-transparent flex justify-between items-center transition-all group">
                                    <span class="text-[11px] text-slate-700 dark:text-slate-300 font-mono group-hover:text-black dark:group-hover:text-white">
                                        <span class="font-bold" x-text="result.acIdent"></span> - <span x-text="result.acName"></span>
                                    </span>
                                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-black"><i class="fa-solid fa-plus"></i> Odaberi</span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>