<div class="relative min-h-screen bg-[#050b14] text-slate-200 font-sans overflow-x-hidden pb-20">
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none"></div>
    
    <div class="relative z-10 max-w-5xl mx-auto p-6 lg:p-12">
        
        {{-- HEADER --}}
        <header class="mb-12 sm:mb-16 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div class="flex-1 min-w-0">
                <a href="#" wire:navigate class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-500 text-[10px] font-black uppercase tracking-widest transition-colors mb-4">
                    <i class="fa-solid fa-arrow-left"></i> Nazad na listu
                </a>
                <h1 class="text-3xl sm:text-4xl font-black uppercase tracking-tight text-white mb-2 truncate">
                    Tender <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">#{{ $wf->procedure_id }}</span>
                </h1>
                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-briefcase text-slate-600"></i> Radni proces i finalizacija
                </p>
            </div>
            
            <div class="bg-slate-900/50 border border-white/5 backdrop-blur-md px-6 py-3 rounded-2xl shadow-xl shrink-0">
                <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest mb-1">Trenutni Status</p>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full {{ $wf->status == 'completed' ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }}"></div>
                    <span class="text-sm font-black text-white uppercase tracking-wider">
                        @if($wf->status == 'accepted') Prihvaćeno
                        @elseif($wf->status == 'documentation_uploaded') Dokumentacija Spremna
                        @elseif($wf->status == 'offer_submitted') Ponuda Poslana
                        @elseif($wf->status == 'completed') Završeno
                        @else {{ $wf->status }} @endif
                    </span>
                </div>
            </div>
        </header>

        {{-- PROGRESS BAR --}}
        @php
            $progressWidth = '0%';
            if(in_array($wf->status, ['documentation_uploaded'])) $progressWidth = '33.33%';
            if(in_array($wf->status, ['offer_submitted'])) $progressWidth = '66.66%';
            if(in_array($wf->status, ['completed'])) $progressWidth = '100%';

            $step2Done = in_array($wf->status, ['documentation_uploaded', 'offer_submitted', 'completed']);
            $step3Done = in_array($wf->status, ['offer_submitted', 'completed']);
            $step4Done = $wf->status === 'completed';
        @endphp

        <div class="relative mb-24 mt-4">
            <div class="absolute left-[28px] right-[28px] top-7 -translate-y-1/2 h-1 bg-slate-800/80 rounded-full z-0">
                <div class="absolute left-0 top-0 bottom-0 bg-gradient-to-r from-blue-600 to-emerald-500 transition-all duration-1000 ease-out rounded-full shadow-[0_0_15px_rgba(59,130,246,0.5)]" style="width: {{ $progressWidth }};"></div>
            </div>

            <div class="relative z-10 flex items-center justify-between">
                <div class="flex flex-col items-center group relative">
                    <div class="w-14 h-14 rounded-2xl bg-blue-600 border border-blue-400 flex items-center justify-center text-white shadow-[0_0_20px_rgba(37,99,235,0.4)]">
                        <i class="fa-solid fa-file-signature text-xl"></i>
                    </div>
                    <span class="absolute top-[64px] text-[10px] font-black uppercase tracking-widest text-blue-400 text-center w-20 leading-tight">1. Preuzeto</span>
                </div>
                
                <div class="flex flex-col items-center group relative">
                    <div class="w-14 h-14 rounded-2xl {{ $step2Done ? 'bg-blue-600 border border-blue-400 text-white shadow-[0_0_20px_rgba(37,99,235,0.4)]' : 'bg-slate-900 border border-slate-700 text-slate-500' }} flex items-center justify-center transition-all duration-500">
                        <i class="fa-solid fa-folder-open text-xl"></i>
                    </div>
                    <span class="absolute top-[64px] text-[10px] font-black uppercase tracking-widest {{ $step2Done ? 'text-blue-400' : 'text-slate-600' }} text-center w-20 leading-tight">2. Dokumenti</span>
                </div>
                
                <div class="flex flex-col items-center group relative">
                    <div class="w-14 h-14 rounded-2xl {{ $step3Done ? 'bg-emerald-500 border border-emerald-400 text-white shadow-[0_0_20px_rgba(16,185,129,0.4)]' : 'bg-slate-900 border border-slate-700 text-slate-500' }} flex items-center justify-center transition-all duration-500">
                        <i class="fa-solid fa-paper-plane text-xl"></i>
                    </div>
                    <span class="absolute top-[64px] text-[10px] font-black uppercase tracking-widest {{ $step3Done ? 'text-emerald-400' : 'text-slate-600' }} text-center w-20 leading-tight">3. Slanje</span>
                </div>
                
                <div class="flex flex-col items-center group relative">
                    <div class="w-14 h-14 rounded-2xl {{ $step4Done ? 'bg-emerald-600 border border-emerald-400 text-white shadow-[0_0_20px_rgba(5,150,105,0.5)]' : 'bg-slate-900 border border-slate-700 text-slate-500' }} flex items-center justify-center transition-all duration-500">
                        <i class="fa-solid fa-flag-checkered text-xl"></i>
                    </div>
                    <span class="absolute top-[64px] text-[10px] font-black uppercase tracking-widest {{ $step4Done ? 'text-emerald-500' : 'text-slate-600' }} text-center w-20 leading-tight">4. Kraj</span>
                </div>
            </div>
        </div>

        {{-- RADNE ZONE --}}
        <div class="space-y-6 relative">
            <div class="absolute left-8 top-10 bottom-10 w-0.5 bg-slate-800/50 -z-10 hidden md:block"></div>

            {{-- FAZA 1.5: AI PARSER I KALKULATOR --}}
            <div class="bg-indigo-950/30 border border-indigo-500/20 backdrop-blur-sm rounded-3xl p-8 relative shadow-[0_0_30px_rgba(79,70,229,0.05)]">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black uppercase text-white flex items-center gap-4">
                        <span class="bg-indigo-500/20 text-indigo-400 w-10 h-10 rounded-xl flex items-center justify-center text-sm border border-indigo-500/30 shadow-inner">
                            <i class="fa-solid fa-brain"></i>
                        </span> 
                        Centar za AI Odluke
                    </h3>
                </div>

                {{-- FORMA ZA UPLOAD --}}
                @if(!$parsedData)
                <div class="bg-slate-900/50 rounded-2xl p-6 border border-slate-800 flex flex-col md:flex-row gap-6 items-start justify-between">
                    <div class="flex-1">
                        <p class="text-slate-300 text-sm font-bold mb-2">Ubacite PDF Tendersku dokumentaciju</p>
                        <p class="text-slate-500 text-xs italic">AI će analizirati artikle i potrebne garancije.</p>
                        
                        @if (session()->has('error'))
                            <div class="mt-3 p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-xl font-mono">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="w-full md:w-auto">
                        <form wire:submit.prevent="processPdf" class="flex flex-col sm:flex-row items-center gap-4">
                            <input type="file" wire:model="pdfFile" accept=".pdf" 
                                class="block w-full text-[10px] text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-indigo-600/20 file:text-indigo-400 hover:file:bg-indigo-600/30 transition-all cursor-pointer">
                            
                            <div x-data="{ analyzing: false, percent: 0 }">
                                <button 
                                    @click="analyzing = true; 
                                            let interval = setInterval(() => { 
                                                if(percent < 90) percent += Math.random() * 5; 
                                            }, 400);
                                            $wire.processPdf().then(() => { 
                                                percent = 100; 
                                                setTimeout(() => { analyzing = false; percent = 0; }, 500); 
                                            });"
                                    wire:loading.attr="disabled"
                                    class="relative overflow-hidden px-8 py-3 bg-indigo-600 text-white font-black uppercase text-[10px] rounded-xl tracking-widest transition-all hover:bg-indigo-500 active:scale-95 shadow-lg shadow-indigo-900/40">
                                    <span x-show="!analyzing"><i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Pokreni Analizu</span>
                                    <span x-show="analyzing" class="flex items-center gap-2" style="display: none;">
                                        <i class="fa-solid fa-circle-notch fa-spin"></i> <span x-text="Math.round(percent) + '%'"></span>
                                    </span>
                                    <div x-show="analyzing" class="absolute bottom-0 left-0 h-1 bg-emerald-400 transition-all duration-500" :style="'width: ' + percent + '%'" style="display: none;"></div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @if($parsedData)
                <div wire:transition.opacity class="space-y-8 mt-6">
                    
                    {{-- AI SAŽETAK I RIZIK --}}
                   @if(isset($parsedData['ai_uprava']))
                    <div class="flex flex-col md:flex-row gap-4 mb-6">
                        <div class="flex-1 min-w-0 bg-gradient-to-br from-indigo-900/40 to-slate-900/40 border border-indigo-500/30 rounded-2xl p-6 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-5 text-indigo-500"><i class="fa-solid fa-robot text-6xl"></i></div>
                            <h4 class="text-[9px] font-black uppercase tracking-widest text-indigo-400 mb-2">AI Strategija</h4>
                            <p class="text-sm font-bold text-slate-300 leading-relaxed">{{ $parsedData['ai_uprava']['sazetak'] ?? 'Analiza nije generisala sažetak.' }}</p>
                        </div>

                        @php
                            $rizik = strtoupper($parsedData['ai_uprava']['rizik_nivo'] ?? 'NIZAK');
                            $rizikColor = $rizik == 'VISOK' ? 'text-rose-500 bg-rose-500/10 border-rose-500/20' : ($rizik == 'SREDNJI' ? 'text-amber-500 bg-amber-500/10 border-amber-500/20' : 'text-emerald-500 bg-emerald-500/10 border-emerald-500/20');
                            $rizikBorder = $rizik == 'VISOK' ? 'border-rose-500/30' : ($rizik == 'SREDNJI' ? 'border-amber-500/30' : 'border-emerald-500/30');
                        @endphp
                        
                        <div class="w-full md:w-72 shrink-0 bg-slate-900/60 border {{ $rizikBorder }} rounded-2xl p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-[9px] font-black uppercase tracking-widest text-slate-400">Rizik</h4>
                                <span class="px-2 py-0.5 border rounded text-[10px] font-black uppercase {{ $rizikColor }}">
                                    {{ $rizik }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 font-bold leading-tight">{{ $parsedData['ai_uprava']['rizik_razlog'] ?? '-' }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- SEKCIJA: DOKUMENTACIJA (Taskovi) --}}
                    @if(count($wf->tasks) > 0)
                    <div class="bg-slate-900/40 border border-slate-700/50 rounded-2xl p-6 shadow-xl">
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-300 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-md bg-indigo-500/20 text-indigo-400 flex items-center justify-center"><i class="fa-solid fa-file-contract"></i></div>
                                Lista obaveza iz dokumentacije
                            </h4>
                            <span class="text-[10px] font-mono text-slate-500">{{ $wf->tasks->where('status', 'pribavljeno')->count() }} / {{ $wf->tasks->count() }} kompletirano</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($wf->tasks as $task)
                                <div class="flex items-center justify-between p-3 rounded-xl border {{ $task->status == 'pribavljeno' ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-slate-800 bg-slate-950/50' }} transition-all group">
                                    
                                    <div class="flex items-center gap-4 w-full">
                                        @if($task->status == 'pribavljeno')
                                            <div class="flex items-center gap-2 shrink-0">
                                                <i class="fa-solid fa-circle-check text-emerald-500 text-xl"></i>
                                            </div>
                                            <div class="flex-1 overflow-hidden">
                                                <span class="text-xs font-bold text-slate-500 line-through">{{ $task->naziv }}</span>
                                                <div class="flex items-center gap-3 mt-1">
                                                    <span class="text-[9px] text-emerald-400 font-mono truncate max-w-[150px] block" title="{{ $task->file_name }}">
                                                        <i class="fa-solid fa-file-pdf"></i> {{ $task->file_name }}
                                                    </span>
                                                    <span class="text-[9px] text-slate-500 font-bold">
                                                        <i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($task->completed_at)->format('d.m.Y H:i') }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button wire:click="removeUploadedFile({{ $task->id }})" class="p-1.5 bg-slate-800 hover:bg-amber-500/20 text-slate-400 hover:text-amber-500 rounded-md transition-colors" title="Ukloni samo PDF">
                                                    <i class="fa-solid fa-file-circle-xmark"></i>
                                                </button>
                                                <button wire:click="deleteTask({{ $task->id }})" class="p-1.5 bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-500 rounded-md transition-colors" title="Obriši skroz ovaj zahtjev">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        @else
                                            <div class="relative shrink-0 cursor-pointer">
                                                <label for="task_file_{{ $task->id }}" class="cursor-pointer block">
                                                    <div wire:loading.remove wire:target="taskFiles.{{ $task->id }}">
                                                        <i class="fa-regular fa-circle text-slate-600 text-xl hover:text-indigo-400 transition-colors" title="Klikni za dodavanje PDF-a"></i>
                                                    </div>
                                                    <div wire:loading wire:target="taskFiles.{{ $task->id }}">
                                                        <i class="fa-solid fa-circle-notch fa-spin text-indigo-400 text-xl"></i>
                                                    </div>
                                                </label>
                                                <input type="file" id="task_file_{{ $task->id }}" wire:model="taskFiles.{{ $task->id }}" accept=".pdf" class="hidden">
                                            </div>
                                            <span class="text-xs font-bold text-slate-200 flex-1">{{ $task->naziv }}</span>
                                            
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button wire:click="deleteTask({{ $task->id }})" class="p-1.5 bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-500 rounded-md transition-colors" title="AI je pogriješio, obriši zahtjev">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="md:col-span-2 mt-2 flex items-center gap-3 bg-slate-900/50 p-2 rounded-xl border border-dashed border-slate-700 focus-within:border-indigo-500/50 transition-colors">
                                <input type="text" wire:model="newTaskName" wire:keydown.enter="addCustomTask" placeholder="Fali dokument? Upiši naziv (npr. Izjava o neosuđivanosti) i pritisni Enter..." 
                                       class="flex-1 bg-transparent text-xs text-white px-3 py-2 outline-none">
                                <button wire:click="addCustomTask" class="shrink-0 bg-indigo-600/20 hover:bg-indigo-600 border border-indigo-500/30 text-indigo-400 hover:text-white px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">
                                    <i class="fa-solid fa-plus mr-1"></i> Dodaj polje
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ARTIKLI GENERALNO --}}
                    @if(isset($parsedData['artikli_generalno']) && !empty($parsedData['artikli_generalno']))
                    <div class="space-y-4 mb-8">
                        <h3 class="text-md font-black uppercase text-white flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs border border-indigo-500/20"><i class="fa-solid fa-list-ol"></i></span>
                            Specifikacija & AI Mapiranje ({{ count($parsedData['artikli_generalno']) }} stavki)
                        </h3>
                        <div class="bg-slate-900/40 border border-slate-700/50 rounded-2xl p-6 shadow-xl">
                            <div class="max-h-[700px] overflow-y-auto pr-2 space-y-4 custom-scrollbar">
                                @foreach($parsedData['artikli_generalno'] as $index => $artikal)
                                    <div class="bg-slate-950/80 p-4 rounded-xl border border-slate-800 flex flex-col gap-3">
                                        
                                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-800/50 pb-4">
                                            
                                            <div class="flex-1 flex items-center gap-3 text-[14px] text-slate-200 font-bold">
                                                <span class="text-[10px] font-black text-slate-500 bg-slate-900 border border-slate-800 w-7 h-7 flex items-center justify-center rounded-lg shadow-inner">{{ $index + 1 }}.</span>
                                                <span class="leading-tight">{{ $artikal['opis'] }}</span>
                                            </div>
                                            
                                            <div class="flex items-center gap-4">
                                                <div class="flex flex-col gap-2 items-center">
                                                    
                                                    <div class="bg-indigo-900/20 px-3 py-1.5 rounded-lg border border-indigo-500/20 text-center min-w-[90px] w-full">
                                                        <span class="text-sm font-mono text-indigo-400 font-black">{{ $artikal['kolicina'] }}</span>
                                                        <span class="text-[9px] uppercase font-black text-indigo-500/70 ml-1">{{ $artikal['jm'] }}</span>
                                                    </div>

                                                    @php
                                                        $match = $artikal['ai_match']['selected'] ?? null;
                                                        $stockTotal = floatval($match['stock_total'] ?? 0);
                                                        $trazeno = floatval($artikal['kolicina']);
                                                        $stockColor = $stockTotal >= $trazeno ? 'text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10' : 'text-rose-400 border-rose-500/30 hover:bg-rose-500/10';
                                                    @endphp

                                                    @if($match)
                                                    <div x-data="{ showStock: false }" class="relative w-full">
                                                        <button @click="showStock = !showStock" class="w-full flex justify-between items-center px-2 py-1 border rounded text-[9px] font-black uppercase {{ $stockColor }} transition-all shadow-sm">
                                                            <span><i class="fa-solid fa-boxes-stacked"></i> Na stanju: {{ $stockTotal }}</span>
                                                            <i class="fa-solid fa-chevron-down text-[8px] ml-1"></i>
                                                        </button>
                                                        
                                                        <div x-show="showStock" @click.away="showStock = false" x-transition.opacity class="absolute top-full mt-1 right-0 w-56 bg-[#020617] border border-slate-700 rounded-lg shadow-2xl z-50 overflow-hidden" style="display: none;">
                                                            <div class="p-1.5 bg-slate-900 text-[8px] text-slate-400 font-black uppercase text-center border-b border-slate-800">
                                                                Detalji po skladištima
                                                            </div>
                                                            <div class="max-h-40 overflow-y-auto custom-scrollbar">
                                                                @forelse($match['stock_details'] ?? [] as $wh)
                                                                    <div class="flex justify-between items-center px-3 py-2 border-b border-slate-800/50 hover:bg-slate-800 transition-colors">
                                                                        <span class="text-[9px] text-slate-300 font-bold uppercase truncate pr-2">{{ $wh['acWarehouse'] ?? 'Skladiste' }}</span>
                                                                        <span class="text-[10px] text-indigo-400 font-mono font-black">{{ $wh['anStock'] ?? 0 }}</span>
                                                                    </div>
                                                                @empty
                                                                    <div class="p-3 text-center text-[9px] text-slate-500 italic">Nema robe na stanju.</div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="flex flex-col items-end gap-2 mt-2">
                                                    <div class="flex gap-2">
                                                        <div class="relative">
                                                            <span class="absolute -top-4 left-1 text-[9px] text-slate-500 uppercase font-black tracking-widest">Nabavna cijena</span>
                                                            <input wire:model.live="purchasePrices.{{ $index }}" type="number" step="0.01" readonly class="w-24 bg-slate-900/50 text-slate-400 text-xs font-mono p-2 rounded-lg border border-slate-800 outline-none cursor-not-allowed" placeholder="0.00">
                                                        </div>
                                                        <div class="relative">
                                                            <span class="absolute -top-4 left-1 text-[9px] text-indigo-400 uppercase font-black tracking-widest">Sistemska VPC</span>
                                                            <input wire:model.live="offerPrices.{{ $index }}" type="number" step="0.01" class="w-24 bg-slate-900 focus:bg-slate-800 focus:ring-1 focus:ring-indigo-500 text-white text-xs font-mono p-2 rounded-lg border border-slate-700 outline-none transition-all" placeholder="0.00">
                                                        </div>
                                                    </div>
                                                    
                                                    @php
                                                        $anPrice = floatval($purchasePrices[$index] ?? 0);
                                                        $anRTPrice = floatval($offerPrices[$index] ?? 0);
                                                        $zarada = $anRTPrice - $anPrice;
                                                        $marza = $anPrice > 0 ? ($zarada / $anPrice) * 100 : 0;
                                                        $bojaZarade = $zarada > 0 ? 'text-emerald-400' : ($zarada < 0 ? 'text-rose-500' : 'text-slate-500');
                                                        $bgZarade = $zarada > 0 ? 'bg-emerald-500/10 border-emerald-500/20' : ($zarada < 0 ? 'bg-rose-500/10 border-rose-500/20' : 'bg-slate-800/50 border-slate-700');
                                                    @endphp
                                                    
                                                    <div class="flex items-center gap-3 px-3 py-1.5 rounded-lg border {{ $bgZarade }}">
                                                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Zarada:</span>
                                                        <span class="text-xs font-mono font-black {{ $bojaZarade }}">{{ $zarada > 0 ? '+' : '' }}{{ number_format($zarada, 2) }} KM</span>
                                                        <div class="w-px h-3 bg-slate-700"></div>
                                                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Marža:</span>
                                                        <span class="text-xs font-mono font-black {{ $bojaZarade }}">{{ number_format($marza, 2) }}%</span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- LOT SEKCIJA --}}
                    @if(!empty($parsedData['lotovi']) && isset($parsedData['is_lotovi']) && $parsedData['is_lotovi'])
                    <div class="space-y-4">
                        <h3 class="text-md font-black uppercase text-white flex items-center gap-3">
                            <span class="w-8 h-8 bg-indigo-500/20 text-indigo-400 rounded-lg flex items-center justify-center text-xs border border-indigo-500/20"><i class="fa-solid fa-boxes-stacked"></i></span> 
                            Analiza po LOT-ovima
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($parsedData['lotovi'] as $index => $lot)
                            @php 
                                $isAccepted = !empty($sudjelujem[$index]); 
                            @endphp
                            
                            <div class="bg-[#0b1120] border {{ $isAccepted ? 'border-emerald-500/50 shadow-[0_0_15px_rgba(16,185,129,0.05)]' : 'border-slate-800' }} rounded-2xl overflow-hidden transition-all duration-300">
                                
                                <div wire:click="toggleLot({{ $index }}, '{{ $lot['broj'] }}')" class="p-5 flex justify-between items-center {{ $isAccepted ? 'bg-emerald-500/5' : 'bg-slate-900/40 hover:bg-slate-900/80' }} transition-colors cursor-pointer group">
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black uppercase tracking-widest {{ $isAccepted ? 'text-emerald-400' : 'text-slate-500 group-hover:text-slate-400' }} transition-colors">
                                                LOT {{ $lot['broj'] }}
                                            </span>
                                            <h4 class="text-sm font-bold text-slate-200">{{ $lot['naziv'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        @if($isAccepted)
                                            <span class="text-[9px] font-black uppercase tracking-widest text-emerald-500 bg-emerald-500/10 px-3 py-1 rounded border border-emerald-500/20 flex items-center gap-2">
                                                <i class="fa-solid fa-check"></i> Prihvaćeno
                                            </span>
                                        @endif
                                        <div class="w-6 h-6 rounded-md border flex items-center justify-center transition-colors {{ $isAccepted ? 'bg-emerald-500 border-emerald-400 text-white' : 'bg-slate-800 border-slate-600 text-transparent' }}">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                @if($isAccepted)
                                <div class="p-5 border-t border-slate-800/50 bg-slate-900/20" wire:transition.slide.down>
                                    
                                    <div class="max-h-[600px] overflow-y-auto pr-2 space-y-4 custom-scrollbar mb-6">
                                        @foreach($lot['artikli'] ?? [] as $artIndex => $art)
                                        
                                        <div class="bg-slate-950/80 p-4 rounded-xl border border-slate-800 flex flex-col gap-3">
                                            
                                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-800/50 pb-4">
                                                
                                                <div class="flex-1 flex items-center gap-3 text-[14px] text-slate-200 font-bold">
                                                    <span class="text-[10px] font-black text-slate-500 bg-slate-900 border border-slate-800 w-7 h-7 flex items-center justify-center rounded-lg shadow-inner">{{ $artIndex + 1 }}.</span>
                                                    <span class="leading-tight">{{ $art['opis'] }}</span>
                                                </div>
                                                
                                                <div class="flex items-center gap-4">
                                                    <div class="flex flex-col gap-2 items-center">
                                                        <div class="bg-indigo-900/20 px-3 py-1.5 rounded-lg border border-indigo-500/20 text-center min-w-[90px] w-full">
                                                            <span class="text-sm font-mono text-indigo-400 font-black">{{ $art['kolicina'] }}</span>
                                                            <span class="text-[9px] uppercase font-black text-indigo-500/70 ml-1">{{ $art['jm'] }}</span>
                                                        </div>

                                                        @php
                                                            $match = $art['ai_match']['selected'] ?? null;
                                                            $stockTotal = floatval($match['stock_total'] ?? 0);
                                                            $trazeno = floatval($art['kolicina']);
                                                            $stockColor = $stockTotal >= $trazeno ? 'text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10' : 'text-rose-400 border-rose-500/30 hover:bg-rose-500/10';
                                                        @endphp

                                                        @if($match)
                                                        <div x-data="{ showStock: false }" class="relative w-full">
                                                            <button @click="showStock = !showStock" class="w-full flex justify-between items-center px-2 py-1 border rounded text-[9px] font-black uppercase {{ $stockColor }} transition-all shadow-sm">
                                                                <span><i class="fa-solid fa-boxes-stacked"></i> Na stanju: {{ $stockTotal }}</span>
                                                                <i class="fa-solid fa-chevron-down text-[8px] ml-1"></i>
                                                            </button>
                                                            
                                                            <div x-show="showStock" @click.away="showStock = false" x-transition.opacity class="absolute top-full mt-1 right-0 w-56 bg-[#020617] border border-slate-700 rounded-lg shadow-2xl z-50 overflow-hidden" style="display: none;">
                                                                <div class="p-1.5 bg-slate-900 text-[8px] text-slate-400 font-black uppercase text-center border-b border-slate-800">
                                                                    Detalji po skladištima
                                                                </div>
                                                                <div class="max-h-40 overflow-y-auto custom-scrollbar">
                                                                    @forelse($match['stock_details'] ?? [] as $wh)
                                                                        <div class="flex justify-between items-center px-3 py-2 border-b border-slate-800/50 hover:bg-slate-800 transition-colors">
                                                                            <span class="text-[9px] text-slate-300 font-bold uppercase truncate pr-2">{{ $wh['acWarehouse'] ?? 'Skladiste' }}</span>
                                                                            <span class="text-[10px] text-indigo-400 font-mono font-black">{{ $wh['anStock'] ?? 0 }}</span>
                                                                        </div>
                                                                    @empty
                                                                        <div class="p-3 text-center text-[9px] text-slate-500 italic">Nema robe na stanju.</div>
                                                                    @endforelse
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="flex flex-col items-end gap-2 mt-2">
                                                        <div class="flex gap-2">
                                                            <div class="relative">
                                                                <span class="absolute -top-4 left-1 text-[9px] text-slate-500 uppercase font-black tracking-widest">NABAVNA CIJENA</span>
                                                                <input wire:model.live="lotPurchasePrices.{{ $index }}.{{ $artIndex }}" type="number" step="0.01" readonly class="w-24 bg-slate-900/50 text-slate-400 text-xs font-mono p-2 rounded-lg border border-slate-800 outline-none cursor-not-allowed" placeholder="0.00">
                                                            </div>
                                                            <div class="relative">
                                                                <span class="absolute -top-4 left-1 text-[9px] text-indigo-400 uppercase font-black tracking-widest">Sistemska VPC</span>
                                                                <input wire:model.live="lotOfferPrices.{{ $index }}.{{ $artIndex }}" type="number" step="0.01" class="w-24 bg-slate-900 focus:bg-slate-800 focus:ring-1 focus:ring-indigo-500 text-white text-xs font-mono p-2 rounded-lg border border-slate-700 outline-none transition-all" placeholder="0.00">
                                                            </div>
                                                        </div>
                                                        
                                                        @php
                                                            $anPrice = floatval($lotPurchasePrices[$index][$artIndex] ?? 0);
                                                            $anRTPrice = floatval($lotOfferPrices[$index][$artIndex] ?? 0);
                                                            $zarada = $anRTPrice - $anPrice;
                                                            $marza = $anPrice > 0 ? ($zarada / $anPrice) * 100 : 0;
                                                            $bojaZarade = $zarada > 0 ? 'text-emerald-400' : ($zarada < 0 ? 'text-rose-500' : 'text-slate-500');
                                                            $bgZarade = $zarada > 0 ? 'bg-emerald-500/10 border-emerald-500/20' : ($zarada < 0 ? 'bg-rose-500/10 border-rose-500/20' : 'bg-slate-800/50 border-slate-700');
                                                        @endphp
                                                        
                                                        <div class="flex items-center gap-3 px-3 py-1.5 rounded-lg border {{ $bgZarade }}">
                                                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Zarada LOTOVI:</span>
                                                            <span class="text-xs font-mono font-black {{ $bojaZarade }}">{{ $zarada > 0 ? '+' : '' }}{{ number_format($zarada, 2) }} KM</span>
                                                            <div class="w-px h-3 bg-slate-700"></div>
                                                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Marža:</span>
                                                            <span class="text-xs font-mono font-black {{ $bojaZarade }}">{{ number_format($marza, 2) }}%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(isset($art['ai_match']))
                                                <div class="ml-10">
                                                    @php
                                                        $match = $art['ai_match']['selected'] ?? null;
                                                        $isManual = $art['ai_match']['is_manual'] ?? false;
                                                        
                                                        if(!$match) { 
                                                            $pct = 0; $borderColor = 'border-rose-500/20'; $textColor = 'text-rose-400'; $badgeStyle = 'bg-rose-500/10 text-rose-500 border-rose-500/20';
                                                        } else {
                                                            $pct = $match['percent'];
                                                            if($pct >= 80) { $borderColor = 'border-emerald-500/30'; $textColor = 'text-emerald-400'; $badgeStyle = 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20'; } 
                                                            elseif($pct >= 50) { $borderColor = 'border-amber-500/30'; $textColor = 'text-amber-400'; $badgeStyle = 'bg-amber-500/10 text-amber-500 border-amber-500/20'; } 
                                                            else { $borderColor = 'border-rose-500/30'; $textColor = 'text-rose-400'; $badgeStyle = 'bg-rose-500/10 text-rose-500 border-rose-500/20'; }
                                                        }
                                                    @endphp

                                                    <div x-data="{ 
                                                            editModeLotArt: false, 
                                                            searchQuery: '', 
                                                            isSearching: false, 
                                                            searchResults: [],
                                                            doSearch() {
                                                                if(this.searchQuery.length < 3) {
                                                                    this.searchResults = [];
                                                                    return;
                                                                }
                                                                this.isSearching = true;
                                                                $wire.searchManual(this.searchQuery).then(res => {
                                                                    this.searchResults = res;
                                                                    this.isSearching = false;
                                                                });
                                                            }
                                                        }">
                                                        
                                                        <div class="flex items-center justify-between bg-slate-900/60 p-2.5 rounded-lg border {{ $borderColor }}">
                                                            <div class="flex items-center gap-3 flex-1 overflow-hidden">
                                                                <i class="fa-solid fa-link text-slate-500 text-[10px]"></i>
                                                                @if($match)
                                                                    <span class="text-xs font-mono {{ $textColor }} truncate">{{ $match['acName'] }}</span>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="px-2 py-0.5 border rounded text-[9px] font-black tracking-wider flex items-center gap-1 {{ $badgeStyle }}">
                                                                            {{ $pct }}% MATCH 
                                                                            @if($isManual) <i class="fa-solid fa-user-check" title="Korisnik potvrdio"></i> @endif
                                                                        </span>
                                                                    </div>
                                                                @else
                                                                    <span class="text-xs font-mono text-rose-500/70 italic">Nema mapiranog artikla u bazi</span>
                                                                @endif
                                                            </div>
                                                            <button @click="editModeLotArt = !editModeLotArt" type="button" class="text-[9px] text-slate-400 hover:text-white uppercase font-black px-3 py-1 bg-slate-800 rounded-md transition-colors whitespace-nowrap">
                                                                <i class="fa-solid fa-pen mr-1"></i> Edit
                                                            </button>
                                                        </div>

                                                        <div x-show="editModeLotArt" x-collapse class="mt-2 bg-[#020617] border border-indigo-500/30 rounded-xl p-3 shadow-2xl z-40 relative" style="display: none;">
                                                            <div class="relative mb-3">
                                                                <input type="text" x-model="searchQuery" @input.debounce.500ms="doSearch" placeholder="Pretraži bazu artikala ručno..." class="w-full bg-slate-900 text-slate-200 text-xs rounded-lg border border-slate-700 pl-8 pr-10 py-2 focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                                                                <i class="fa-solid fa-search absolute left-3 top-2.5 text-slate-500 text-[11px]" x-show="!isSearching"></i>
                                                                <i class="fa-solid fa-circle-notch fa-spin absolute left-3 top-2.5 text-indigo-400 text-[12px]" x-show="isSearching" style="display: none;"></i>
                                                            </div>
                                                            
                                                            <div class="space-y-1 max-h-48 overflow-y-auto custom-scrollbar">
                                                                
                                                                {{-- 1. AI PREPORUKE (Kada korisnik nije ništa ukucao) --}}
                                                                <template x-if="searchQuery.length < 3">
                                                                    <div>
                                                                        @forelse($art['ai_match']['suggestions'] ?? [] as $sug)
                                                                            @php $sugStyle = $sug['percent'] >= 80 ? 'text-emerald-500 bg-emerald-500/10' : ($sug['percent'] >= 50 ? 'text-amber-500 bg-amber-500/10' : 'text-rose-500 bg-rose-500/10'); @endphp
                                                                            <button type="button" 
                                                                                    wire:click="updateArticleMatch('lot', {{ $index }}, {{ $artIndex }}, '{{ $sug['acIdent'] }}', '{{ addslashes($sug['acName']) }}', 100, '{{ addslashes($art['opis']) }}', {{ floatval($sug['anRTPrice'] ?? 0) }}, {{ floatval($sug['stock_total'] ?? 0) }}, '{{ json_encode($sug['stock_details'] ?? []) }}')"
                                                                                    @click="editModeLotArt = false"
                                                                                    class="w-full text-left p-2 rounded-lg hover:bg-slate-800 border border-transparent hover:border-slate-700 flex justify-between items-center transition-all group">
                                                                                <span class="text-[11px] text-slate-300 group-hover:text-white font-mono">{{ $sug['acName'] }}</span>
                                                                                <span class="text-[10px] font-black px-2 rounded {{ $sugStyle }}">{{ $sug['percent'] }}%</span>
                                                                            </button>
                                                                        @empty
                                                                            <p class="text-[10px] text-slate-500 italic p-2">Nema sličnih artikala.</p>
                                                                        @endforelse
                                                                    </div>
                                                                </template>

                                                                {{-- 2. RUČNA PRETRAGA (Kada ukuca više od 3 slova) --}}
                                                                <template x-if="searchQuery.length >= 3">
                                                                    <div>
                                                                        <template x-for="result in searchResults" :key="result.acIdent">
                                                                            <button type="button" 
                                                                                    x-on:click="$wire.updateArticleMatch('lot', {{ $index }}, {{ $artIndex }}, result.acIdent, result.acName, 100, '{{ addslashes($art['opis']) }}', result.anRTPrice || 0, result.stock_total || 0, JSON.stringify(result.stock_details || [])); editModeLotArt = false;"
                                                                                    class="w-full text-left p-2 rounded-lg hover:bg-indigo-900/30 border border-transparent hover:border-indigo-500/30 flex justify-between items-center transition-all group">
                                                                                <span class="text-[11px] text-slate-300 group-hover:text-white font-mono" x-text="result.acName"></span>
                                                                                <span class="text-[10px] text-indigo-400 font-black"><i class="fa-solid fa-plus"></i> Odaberi</span>
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
                                        @endforeach
                                    </div>

                                    @php 
                                        $nab = floatval($nabavne[$index] ?? 0);
                                        
                                        $pon = 0;
                                        if(isset($lotOfferPrices[$index]) && is_array($lotOfferPrices[$index])) {
                                            foreach($lotOfferPrices[$index] as $pojedinacnaPonuda) {
                                                $pon += floatval($pojedinacnaPonuda);
                                            }
                                        }

                                        $zarada = $pon - $nab;
                                        $marza = $nab > 0 ? ($zarada / $nab) * 100 : 0;
                                        $profitColor = $zarada > 0 ? 'text-emerald-400' : ($zarada < 0 ? 'text-rose-500' : 'text-slate-400');
                                        $profitBorder = $zarada > 0 ? 'border-emerald-500/30 bg-emerald-500/5' : ($zarada < 0 ? 'border-rose-500/30 bg-rose-500/5' : 'border-slate-700 bg-slate-900/50');
                                    @endphp

                                    <div class="bg-[#050b14] rounded-xl border border-slate-800 p-5 shadow-inner">
                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                                            <div class="lg:col-span-2 grid grid-cols-2 gap-4">
                                                <div class="space-y-1.5">
                                                    <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest pl-1">Ukupna Nabavna (LOT)</label>
                                                    <div class="relative">
                                                        <span class="absolute left-3 top-2.5 text-slate-500 font-mono text-sm">KM</span>
                                                        <input value="{{ number_format($nab, 2, '.', '') }}" type="number" step="0.01" readonly class="w-full bg-slate-900 focus:bg-slate-800 text-slate-300 font-mono text-sm p-2.5 pl-10 rounded-lg border border-slate-700 outline-none cursor-not-allowed">
                                                    </div>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[9px] font-black text-indigo-400 uppercase tracking-widest pl-1">Ponudbena Cijena (LOT)</label>
                                                    <div class="relative">
                                                        <span class="absolute left-3 top-2.5 text-indigo-400 font-mono text-sm">KM</span>
                                                        <input value="{{ number_format($pon, 2, '.', '') }}" type="number" step="0.01" readonly class="w-full bg-indigo-900/20 text-white font-mono text-sm p-2.5 pl-10 rounded-lg border border-indigo-500/30 outline-none cursor-not-allowed shadow-[0_0_10px_rgba(99,102,241,0.1)]">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="rounded-xl p-4 border {{ $profitBorder }} flex flex-col justify-center h-full transition-colors">
                                                <div class="flex justify-between items-end mb-1">
                                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Zarada na LOT-u</span>
                                                    <span class="text-[10px] font-black {{ $zarada > 0 ? 'text-emerald-500' : 'text-slate-500' }}">Marža: {{ number_format($marza, 1) }}%</span>
                                                </div>
                                                <p class="text-2xl font-black font-mono {{ $profitColor }}">
                                                    {{ $zarada > 0 ? '+' : '' }}{{ number_format($zarada, 2) }} <span class="text-sm">KM</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="flex flex-wrap justify-end gap-4 pt-6">
    
                        <button wire:click="generisiKatalogPdf" 
                                wire:loading.attr="disabled"
                                class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-black uppercase text-xs rounded-2xl shadow-xl shadow-blue-900/20 hover:shadow-blue-900/40 hover:from-blue-500 hover:to-indigo-500 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-3">
                            <i class="fa-solid fa-file-pdf text-lg"></i> 
                            <span wire:loading.remove wire:target="generisiKatalogPdf">Generiši PDF Katalog</span>
                            <span wire:loading wire:target="generisiKatalogPdf">Generišem...</span>
                        </button>

                        <button wire:click="spasiUBazu" 
                                wire:loading.attr="disabled"
                                class="px-8 py-4 bg-emerald-600 text-white font-black uppercase text-xs rounded-2xl shadow-xl hover:bg-emerald-500 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-3">
                            <i class="fa-solid fa-database text-lg"></i> 
                            <span wire:loading.remove wire:target="spasiUBazu">Spasi i arhiviraj podatke</span>
                            <span wire:loading wire:target="spasiUBazu">Spašavam...</span>
                        </button>

                        <button wire:click="posaljiUErp" 
                                wire:loading.attr="disabled"
                                class="px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-black uppercase text-xs rounded-2xl shadow-xl shadow-orange-900/20 hover:shadow-orange-900/40 hover:from-amber-400 hover:to-orange-500 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-3">
                            <i class="fa-solid fa-server text-lg"></i> 
                            <span wire:loading.remove wire:target="posaljiUErp">Prebaci artikle u ERP</span>
                            <span wire:loading wire:target="posaljiUErp">Šaljem u ERP... <i class="fa-solid fa-circle-notch fa-spin ml-1"></i></span>
                        </button>

                    </div>

                </div> 
                @endif 
            </div>

            {{-- ZAVRŠNA FAZA: SLANJE --}}
            @if(in_array($wf->status, ['documentation_uploaded', 'offer_submitted', 'completed']))
            <div wire:transition.slide.down class="bg-slate-900/60 border border-white/5 backdrop-blur-sm rounded-3xl p-8 mt-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-5 text-emerald-500"><i class="fa-solid fa-paper-plane text-8xl"></i></div>
                
                <h3 class="text-lg font-black uppercase text-white flex items-center gap-4 mb-6 relative z-10">
                    <span class="bg-emerald-500/10 text-emerald-400 w-10 h-10 rounded-xl flex items-center justify-center text-sm border border-emerald-500/20 shadow-inner">2</span> 
                    Slanje ponude na e-Nabavke
                </h3>
                
                @if(!in_array($wf->status, ['offer_submitted', 'completed']))
                    @php
                        $sviDokumentiSpremni = $wf->tasks->count() > 0 && $wf->tasks->count() === $wf->tasks->where('status', 'pribavljeno')->count();
                    @endphp
                    
                    <button wire:click="submitOffer" 
                        class="w-full relative z-10 text-white font-black py-4 rounded-xl transition-all uppercase text-sm flex items-center justify-center gap-3 shadow-lg active:scale-95 
                        {{ $sviDokumentiSpremni ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-slate-800 hover:bg-slate-700 text-slate-400 border border-rose-500/30 cursor-not-allowed' }}">
                        
                        <i class="fa-solid {{ $sviDokumentiSpremni ? 'fa-paper-plane' : 'fa-lock' }}"></i> 
                        {{ $sviDokumentiSpremni ? 'Potvrdi slanje' : 'Pribavite sve dokumente za otključavanje' }}
                    </button>
                @else
                <div class="relative z-10 bg-emerald-500/5 border border-emerald-500/20 rounded-xl p-5 text-[10px] font-black text-emerald-500 uppercase tracking-widest flex items-center">
                    <i class="fa-solid fa-circle-check text-lg mr-3"></i> Ponuda uspješno zavedena i poslana.
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.5); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.3); border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.5); }
</style>