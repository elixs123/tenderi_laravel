<div class="relative min-h-screen bg-slate-50 dark:bg-[#050b14] text-slate-800 dark:text-slate-200 font-sans overflow-x-hidden pb-20 transition-colors duration-300">
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none transition-opacity duration-300"></div>
    
    <div class="relative z-10 max-w-5xl mx-auto p-6 lg:p-12">
        
        {{-- HEADER --}}
        <header class="mb-12 sm:mb-16 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div class="flex-1 min-w-0">
                <a href="#" wire:navigate class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 dark:hover:text-blue-500 text-[10px] font-black uppercase tracking-widest transition-colors mb-4">
                    <i class="fa-solid fa-arrow-left"></i> Nazad na listu
                </a>
                <h1 class="text-3xl sm:text-4xl font-black uppercase tracking-tight text-slate-900 dark:text-white mb-2 truncate transition-colors">
                    Tender <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500 dark:from-blue-400 dark:to-emerald-400">#{{ $wf->procedure_id }}</span>
                </h1>
                <p class="text-slate-500 dark:text-slate-400 font-bold text-xs uppercase tracking-widest flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-briefcase text-slate-400 dark:text-slate-600"></i> Radni proces i finalizacija
                </p>
            </div>
            
            <div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-white/5 backdrop-blur-md px-6 py-3 rounded-2xl shadow-lg dark:shadow-xl shrink-0 transition-all">
                <p class="text-[9px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-widest mb-1">Trenutni Status</p>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full {{ $wf->status == 'completed' ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }}"></div>
                    <span class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider transition-colors">
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
            <div class="absolute left-[28px] right-[28px] top-7 -translate-y-1/2 h-1 bg-slate-200 dark:bg-slate-800/80 rounded-full z-0 transition-colors">
                <div class="absolute left-0 top-0 bottom-0 bg-gradient-to-r from-blue-500 to-emerald-500 dark:from-blue-600 dark:to-emerald-500 transition-all duration-1000 ease-out rounded-full shadow-[0_0_15px_rgba(59,130,246,0.3)] dark:shadow-[0_0_15px_rgba(59,130,246,0.5)]" style="width: {{ $progressWidth }};"></div>
            </div>

            <div class="relative z-10 flex items-center justify-between">
                <div class="flex flex-col items-center group relative">
                    <div class="w-14 h-14 rounded-2xl bg-blue-600 border border-blue-500 dark:border-blue-400 flex items-center justify-center text-white shadow-[0_0_20px_rgba(37,99,235,0.3)] dark:shadow-[0_0_20px_rgba(37,99,235,0.4)]">
                        <i class="fa-solid fa-file-signature text-xl"></i>
                    </div>
                    <span class="absolute top-[64px] text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400 text-center w-20 leading-tight">1. Preuzeto</span>
                </div>
                
                <div class="flex flex-col items-center group relative">
                    <div class="w-14 h-14 rounded-2xl {{ $step2Done ? 'bg-blue-600 border-blue-500 dark:border-blue-400 text-white shadow-[0_0_20px_rgba(37,99,235,0.3)] dark:shadow-[0_0_20px_rgba(37,99,235,0.4)]' : 'bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-400 dark:text-slate-500' }} border flex items-center justify-center transition-all duration-500">
                        <i class="fa-solid fa-folder-open text-xl"></i>
                    </div>
                    <span class="absolute top-[64px] text-[10px] font-black uppercase tracking-widest {{ $step2Done ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-600' }} text-center w-20 leading-tight transition-colors">2. Dokumenti</span>
                </div>
                
                <div class="flex flex-col items-center group relative">
                    <div class="w-14 h-14 rounded-2xl {{ $step3Done ? 'bg-emerald-500 border-emerald-500 dark:border-emerald-400 text-white shadow-[0_0_20px_rgba(16,185,129,0.3)] dark:shadow-[0_0_20px_rgba(16,185,129,0.4)]' : 'bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-400 dark:text-slate-500' }} border flex items-center justify-center transition-all duration-500">
                        <i class="fa-solid fa-paper-plane text-xl"></i>
                    </div>
                    <span class="absolute top-[64px] text-[10px] font-black uppercase tracking-widest {{ $step3Done ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-600' }} text-center w-20 leading-tight transition-colors">3. Slanje</span>
                </div>
                
                <div class="flex flex-col items-center group relative">
                    <div class="w-14 h-14 rounded-2xl {{ $step4Done ? 'bg-emerald-600 border-emerald-500 dark:border-emerald-400 text-white shadow-[0_0_20px_rgba(5,150,105,0.3)] dark:shadow-[0_0_20px_rgba(5,150,105,0.5)]' : 'bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-400 dark:text-slate-500' }} border flex items-center justify-center transition-all duration-500">
                        <i class="fa-solid fa-flag-checkered text-xl"></i>
                    </div>
                    <span class="absolute top-[64px] text-[10px] font-black uppercase tracking-widest {{ $step4Done ? 'text-emerald-600 dark:text-emerald-500' : 'text-slate-500 dark:text-slate-600' }} text-center w-20 leading-tight transition-colors">4. Kraj</span>
                </div>
            </div>
        </div>

        {{-- RADNE ZONE --}}
        <div class="space-y-6 relative">
            <div class="absolute left-8 top-10 bottom-10 w-0.5 bg-slate-200 dark:bg-slate-800/50 -z-10 hidden md:block transition-colors"></div>

            {{-- FAZA 1.5: AI PARSER I KALKULATOR --}}
            <div class="bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-500/20 backdrop-blur-sm rounded-3xl p-8 relative shadow-sm dark:shadow-[0_0_30px_rgba(79,70,229,0.05)] transition-all">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black uppercase text-slate-800 dark:text-white flex items-center gap-4 transition-colors">
                        <span class="bg-white dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 w-10 h-10 rounded-xl flex items-center justify-center text-sm border border-indigo-200 dark:border-indigo-500/30 shadow-sm dark:shadow-inner">
                            <i class="fa-solid fa-brain"></i>
                        </span> 
                        Centar za AI Odluke
                    </h3>
                </div>

                {{-- FORMA ZA UPLOAD --}}
                @if(!$parsedData)
                <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row gap-6 items-start justify-between shadow-sm dark:shadow-none transition-colors">
                    <div class="flex-1">
                        <p class="text-slate-800 dark:text-slate-300 text-sm font-bold mb-2 transition-colors">Ubacite PDF Tendersku dokumentaciju</p>
                        <p class="text-slate-500 text-xs italic">AI će analizirati artikle i potrebne garancije.</p>
                        
                        @if (session()->has('error'))
                            <div class="mt-3 p-3 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs rounded-xl font-mono transition-colors">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="w-full md:w-auto">
                        {{-- 1. GLAVNI UPLOAD ZA PDF (OPENAI) --}}
                        <form wire:submit.prevent="processPdf" class="flex flex-col sm:flex-row items-center gap-4">
                            <div class="relative w-full">
                                <input type="file" wire:model="pdfFile" accept=".pdf" 
                                    class="block w-full text-[10px] text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-indigo-100 dark:file:bg-indigo-600/20 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-200 dark:hover:file:bg-indigo-600/30 transition-all cursor-pointer">
                                <div wire:loading wire:target="pdfFile" class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2 bg-white dark:bg-slate-900 px-2 py-1 rounded border border-slate-200 dark:border-transparent">
                                    <i class="fa-solid fa-circle-notch fa-spin text-indigo-500 dark:text-indigo-400 text-xs"></i> 
                                    <span class="text-[9px] text-indigo-600 dark:text-indigo-300 font-bold uppercase tracking-widest">Spremam...</span>
                                </div>
                            </div>
                            <div x-data="{ analyzing: false, percent: 0 }">
                                <button @click="if($wire.pdfFile) { analyzing = true; let interval = setInterval(() => { if(percent < 90) percent += Math.random() * 5; }, 400); $wire.processPdf().then(() => { percent = 100; setTimeout(() => { analyzing = false; percent = 0; }, 500); }); }"
                                    wire:loading.attr="disabled" wire:target="pdfFile, processPdf" @if(!$pdfFile) disabled @endif type="button"
                                    class="relative overflow-hidden px-8 py-3 bg-indigo-600 text-white font-black uppercase text-[10px] rounded-xl tracking-widest transition-all hover:bg-indigo-700 dark:hover:bg-indigo-500 active:scale-95 shadow-lg shadow-indigo-600/30 dark:shadow-indigo-900/40 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                                    <span x-show="!analyzing"><i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Pokreni Analizu</span>
                                    <span x-show="analyzing" class="flex items-center gap-2" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i> <span x-text="Math.round(percent) + '%'"></span></span>
                                    <div x-show="analyzing" class="absolute bottom-0 left-0 h-1 bg-emerald-400 transition-all duration-500" :style="'width: ' + percent + '%'" style="display: none;"></div>
                                </button>
                            </div>
                        </form>

                        {{-- 2. BYPASS UPLOAD ZA JSON --}}
                        <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800 transition-colors">
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-black mb-3">Ili učitajte gotov JSON fajl (Zaobilazi AI)</p>
                            <form wire:submit.prevent="processJson" class="flex flex-col sm:flex-row items-center gap-4">
                                <div class="relative w-full">
                                    <input type="file" wire:model="jsonFile" accept=".json" 
                                        class="block w-full text-[10px] text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-emerald-50 dark:file:bg-emerald-500/20 file:text-emerald-600 dark:file:text-emerald-400 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-500/30 transition-all cursor-pointer">
                                    <div wire:loading wire:target="jsonFile" class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2 bg-white dark:bg-slate-900 px-2 py-1 rounded border border-slate-200 dark:border-transparent">
                                        <i class="fa-solid fa-circle-notch fa-spin text-emerald-500 dark:text-emerald-400 text-xs"></i> 
                                        <span class="text-[9px] text-emerald-600 dark:text-emerald-300 font-bold uppercase tracking-widest">Spremam...</span>
                                    </div>
                                </div>
                                <button wire:loading.attr="disabled" wire:target="jsonFile, processJson" @if(!$jsonFile) disabled @endif type="submit"
                                    class="relative overflow-hidden px-8 py-3 bg-emerald-500 text-white font-black uppercase text-[10px] rounded-xl tracking-widest transition-all hover:bg-emerald-600 active:scale-95 shadow-lg shadow-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                                    <span wire:loading.remove wire:target="processJson"><i class="fa-solid fa-file-code mr-2"></i> Obradi JSON</span>
                                    <span wire:loading wire:target="processJson"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Obrađujem...</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @if($parsedData)
                <div wire:transition.opacity class="space-y-8 mt-6">
                    {{-- AI SAŽETAK I RIZIK --}}
                    @if(isset($parsedData['ai_uprava']))
                    <div class="flex flex-col md:flex-row gap-4 mb-6">
                        <div class="flex-1 bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-900/40 dark:to-slate-900/40 border border-indigo-200 dark:border-indigo-500/30 rounded-2xl p-6 relative overflow-hidden transition-all">
                            <div class="absolute top-0 right-0 p-4 opacity-10 dark:opacity-5 text-indigo-600 dark:text-indigo-500"><i class="fa-solid fa-robot text-6xl"></i></div>
                            <h4 class="text-[9px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-2">AI Strategija</h4>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300 leading-relaxed transition-colors">{{ $parsedData['ai_uprava']['sazetak'] ?? 'Analiza nije generisala sažetak.' }}</p>
                        </div>
                        @php
                            $rizik = strtoupper($parsedData['ai_uprava']['rizik_nivo'] ?? 'NIZAK');
                            $rizikColor = $rizik == 'VISOK' ? 'text-rose-600 dark:text-rose-500 bg-rose-100 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/20' : ($rizik == 'SREDNJI' ? 'text-amber-600 dark:text-amber-500 bg-amber-100 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/20' : 'text-emerald-600 dark:text-emerald-500 bg-emerald-100 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20');
                            $rizikBorder = $rizik == 'VISOK' ? 'border-rose-300 dark:border-rose-500/30' : ($rizik == 'SREDNJI' ? 'border-amber-300 dark:border-amber-500/30' : 'border-emerald-300 dark:border-emerald-500/30');
                        @endphp
                        <div class="w-full md:w-72 shrink-0 bg-white dark:bg-slate-900/60 border {{ $rizikBorder }} rounded-2xl p-6 shadow-sm transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Rizik</h4>
                                <span class="px-2 py-0.5 border rounded text-[10px] font-black uppercase {{ $rizikColor }}">{{ $rizik }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 font-bold leading-tight">{{ $parsedData['ai_uprava']['rizik_razlog'] ?? '-' }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- SEKCIJA: DOKUMENTACIJA (Taskovi) --}}
                    @if(count($wf->tasks) > 0)
                    <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700/50 rounded-2xl p-6 shadow-lg transition-all">
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-800 dark:text-slate-300 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-md bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center"><i class="fa-solid fa-file-contract"></i></div>
                                Lista obaveza iz dokumentacije
                            </h4>
                            <span class="text-[10px] font-mono text-slate-500">{{ $wf->tasks->where('status', 'pribavljeno')->count() }} / {{ $wf->tasks->count() }} kompletirano</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($wf->tasks as $task)
                                <div class="flex items-center justify-between p-3 rounded-xl border {{ $task->status == 'pribavljeno' ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/5' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50' }} transition-all group">
                                    <div class="flex items-center gap-4 w-full">
                                        @if($task->status == 'pribavljeno')
                                            <i class="fa-solid fa-circle-check text-emerald-500 text-xl shrink-0"></i>
                                            <div class="flex-1 overflow-hidden">
                                                <span class="text-xs font-bold text-slate-500 line-through">{{ $task->naziv }}</span>
                                                <div class="flex items-center gap-3 mt-1 text-[9px] text-slate-400">
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-mono truncate max-w-[120px]"><i class="fa-solid fa-file-pdf"></i> {{ $task->file_name }}</span>
                                                    <span><i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($task->completed_at)->format('d.m.Y H:i') }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button wire:click="removeUploadedFile({{ $task->id }})" class="p-1.5 bg-slate-200 dark:bg-slate-800 hover:bg-amber-100 text-slate-500 dark:text-slate-400 hover:text-amber-600 rounded-md"><i class="fa-solid fa-file-circle-xmark"></i></button>
                                                <button wire:click="deleteTask({{ $task->id }})" class="p-1.5 bg-slate-200 dark:bg-slate-800 hover:bg-rose-100 text-slate-500 dark:text-slate-400 hover:text-rose-600 rounded-md"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        @else
                                            <div class="relative shrink-0 cursor-pointer">
                                                <label for="task_file_{{ $task->id }}" class="cursor-pointer block">
                                                    <i wire:loading.remove wire:target="taskFiles.{{ $task->id }}" class="fa-regular fa-circle text-slate-400 dark:text-slate-600 text-xl hover:text-indigo-500"></i>
                                                    <i wire:loading wire:target="taskFiles.{{ $task->id }}" class="fa-solid fa-circle-notch fa-spin text-indigo-500 text-xl"></i>
                                                </label>
                                                <input type="file" id="task_file_{{ $task->id }}" wire:model="taskFiles.{{ $task->id }}" accept=".pdf" class="hidden">
                                            </div>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 flex-1">{{ $task->naziv }}</span>
                                            <button wire:click="deleteTask({{ $task->id }})" class="p-1.5 bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-rose-600 opacity-0 group-hover:opacity-100 transition-all"><i class="fa-solid fa-trash"></i></button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <div class="md:col-span-2 mt-2 flex items-center gap-3 bg-slate-50 dark:bg-slate-900/50 p-2 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
                                <input type="text" wire:model="newTaskName" wire:keydown.enter="addCustomTask" placeholder="Upišite naziv dokumenta i pritisni Enter..." class="flex-1 bg-transparent text-xs text-slate-800 dark:text-white px-3 py-2 outline-none">
                                <button wire:click="addCustomTask" class="bg-indigo-100 dark:bg-indigo-600/20 text-indigo-700 dark:text-indigo-400 px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all"><i class="fa-solid fa-plus mr-1"></i> Dodaj</button>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- SEKCIJA: ARTIKLI GENERALNO (Vraćeno i Popravljeno) --}}
                    @if(isset($parsedData['artikli_generalno']) && !empty($parsedData['artikli_generalno']))
                    <div class="space-y-4 mb-8">
                        <h3 class="text-md font-black uppercase text-slate-800 dark:text-white flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs border border-indigo-200 dark:border-indigo-500/20"><i class="fa-solid fa-list-ol"></i></span>
                            Specifikacija & AI Mapiranje ({{ count($parsedData['artikli_generalno']) }} stavki)
                        </h3>
                        <div class="bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700/50 rounded-2xl p-6 shadow-lg transition-all">
                            <div class="max-h-[700px] overflow-y-auto pr-2 space-y-4 custom-scrollbar">
                                @foreach($parsedData['artikli_generalno'] as $index => $artikal)
                                    @include('livewire.user.partials.tender-artikal-row', ['art' => $artikal, 'index' => $index, 'type' => 'general', 'parentIndex' => null])
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- SEKCIJA: LOTOVI (Sa Paginacijom) --}}
                    @if(!empty($parsedData['lotovi']) && isset($parsedData['is_lotovi']) && $parsedData['is_lotovi'])
                    <div class="space-y-4">
                        <h3 class="text-md font-black uppercase text-slate-800 dark:text-white flex items-center gap-3">
                            <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center text-xs border border-indigo-200 dark:border-indigo-500/20"><i class="fa-solid fa-boxes-stacked"></i></span> 
                            Analiza po LOT-ovima
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($parsedData['lotovi'] as $index => $lot)
                            @php $isAccepted = !empty($sudjelujem[$index]); @endphp
                            <div class="bg-white dark:bg-[#0b1120] border {{ $isAccepted ? 'border-emerald-400 dark:border-emerald-500/50 shadow-sm dark:shadow-[0_0_15px_rgba(16,185,129,0.05)]' : 'border-slate-200 dark:border-slate-800' }} rounded-2xl overflow-hidden transition-all duration-300">
                                <div wire:click="toggleLot({{ $index }}, '{{ $lot['broj'] }}')" class="p-5 flex justify-between items-center {{ $isAccepted ? 'bg-emerald-50 dark:bg-emerald-500/5' : 'bg-slate-50 dark:bg-slate-900/40 hover:bg-slate-100 dark:hover:bg-slate-900/80' }} transition-colors cursor-pointer group">
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black uppercase tracking-widest {{ $isAccepted ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-600' }}">LOT {{ $lot['broj'] }}</span>
                                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $lot['naziv'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        @if($isAccepted)<span class="text-[9px] font-black uppercase text-emerald-600 bg-emerald-100 px-3 py-1 rounded border border-emerald-200 flex items-center gap-2"><i class="fa-solid fa-check"></i> Prihvaćeno</span>@endif
                                        <div class="w-6 h-6 rounded-md border flex items-center justify-center transition-colors {{ $isAccepted ? 'bg-emerald-500 border-emerald-400 text-white' : 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-transparent' }}"><i class="fa-solid fa-check text-xs"></i></div>
                                    </div>
                                </div>

                                @if($isAccepted)
                                <div class="p-5 border-t border-slate-200 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-900/20" wire:transition.slide.down>
                                    {{-- PAGINACIJA LOGIKA --}}
                                    @php
                                        $perPage = 10;
                                        $currentPage = $lotPages[$index] ?? 1;
                                        $allArtikli = collect($lot['artikli'] ?? []);
                                        $totalArtikala = $allArtikli->count();
                                        $totalPages = ceil($totalArtikala / $perPage);
                                        $prikaziArtikle = $allArtikli->forPage($currentPage, $perPage);
                                    @endphp

                                    <div class="max-h-[1000px] overflow-y-auto pr-2 space-y-4 custom-scrollbar mb-6">
                                        @foreach($prikaziArtikle as $artInternalIndex => $art)
                                            @php $originalArtIndex = (($currentPage - 1) * $perPage) + $loop->index; @endphp
                                            {{-- Uključujemo identičan red kao za generalne, samo šaljemo parametre za lot --}}
                                            @include('livewire.user.partials.tender-artikal-row', ['art' => $art, 'index' => $originalArtIndex, 'type' => 'lot', 'parentIndex' => $index])
                                        @endforeach
                                    </div>

                                    {{-- PAGINACIJA NAVIGACIJA --}}
                                    @if($totalPages > 1)
                                    <div class="flex items-center justify-between py-4 border-t border-slate-200 dark:border-slate-800">
                                        <div class="flex gap-2">
                                            <button wire:click="setLotPage({{ $index }}, {{ max(1, $currentPage - 1) }})" class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-blue-500" @if($currentPage == 1) disabled @endif><i class="fa-solid fa-chevron-left"></i></button>
                                            <div class="flex items-center gap-1">
                                                @for($p = 1; $p <= $totalPages; $p++)
                                                    @if($p == 1 || $p == $totalPages || abs($p - $currentPage) <= 1)
                                                        <button wire:click="setLotPage({{ $index }}, {{ $p }})" class="w-8 h-8 rounded-lg text-xs font-black {{ $currentPage == $p ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' }}">{{ $p }}</button>
                                                    @elseif($p == 2 || $p == $totalPages - 1) <span class="text-slate-400">...</span> @endif
                                                @endfor
                                            </div>
                                            <button wire:click="setLotPage({{ $index }}, {{ min($totalPages, $currentPage + 1) }})" class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-blue-500" @if($currentPage == $totalPages) disabled @endif><i class="fa-solid fa-chevron-right"></i></button>
                                        </div>
                                        <span class="text-[10px] font-black uppercase text-slate-400">Stranica {{ $currentPage }} od {{ $totalPages }} (Ukupno {{ $totalArtikala }} stavki)</span>
                                    </div>
                                    @endif

                                    {{-- SUMARNI DIO LOTA --}}
                                    @php 
                                        $nab = floatval($nabavne[$index] ?? 0);
                                        $pon = array_sum($lotOfferPrices[$index] ?? []);
                                        $zarada = $pon - $nab;
                                        $marza = $nab > 0 ? ($zarada / $nab) * 100 : 0;
                                        $profitColor = $zarada > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($zarada < 0 ? 'text-rose-600 dark:text-rose-500' : 'text-slate-500');
                                        $profitBorder = $zarada > 0 ? 'border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/5' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50';
                                    @endphp
                                    <div class="bg-white dark:bg-[#050b14] rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-inner">
                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                                            <div class="lg:col-span-2 grid grid-cols-2 gap-4">
                                                <div class="space-y-1"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Ukupna Nabavna (LOT)</label><input value="{{ number_format($nab, 2) }} KM" readonly class="w-full bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-mono text-sm p-2.5 rounded-lg border border-slate-200 dark:border-slate-700"></div>
                                                <div class="space-y-1"><label class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Ponuda (LOT)</label><input value="{{ number_format($pon, 2) }} KM" readonly class="w-full bg-indigo-50 dark:bg-indigo-900/20 text-indigo-900 dark:text-white font-mono text-sm p-2.5 rounded-lg border border-indigo-200 dark:border-indigo-500/30"></div>
                                            </div>
                                            <div class="rounded-xl p-4 border {{ $profitBorder }} flex flex-col justify-center h-full">
                                                <div class="flex justify-between items-end mb-1"><span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Zarada</span><span class="text-[10px] font-black">Marža: {{ number_format($marza, 1) }}%</span></div>
                                                <p class="text-2xl font-black font-mono {{ $profitColor }}">{{ $zarada > 0 ? '+' : '' }}{{ number_format($zarada, 2) }} <span class="text-sm">KM</span></p>
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

                    {{-- FOOTER AKCIJE --}}
                    <div class="pt-6">
                        @if(empty($wf->erp_document_id))
                            <div class="flex flex-wrap justify-end gap-4">
                                <button wire:click="generisiKatalogPdf" wire:loading.attr="disabled" class="px-8 py-4 bg-gradient-to-r from-blue-500 to-indigo-500 dark:from-blue-600 dark:to-indigo-600 text-white font-black uppercase text-xs rounded-2xl shadow-lg hover:active:scale-95 disabled:opacity-50 transition-all flex items-center gap-3"><i class="fa-solid fa-file-pdf text-lg"></i> <span wire:loading.remove wire:target="generisiKatalogPdf">Katalog PDF</span><span wire:loading wire:target="generisiKatalogPdf">Generišem...</span></button>
                                <button wire:click="spasiUBazu" wire:loading.attr="disabled" class="px-8 py-4 bg-emerald-500 dark:bg-emerald-600 text-white font-black uppercase text-xs rounded-2xl shadow-lg hover:bg-emerald-600 active:scale-95 transition-all flex items-center gap-3"><i class="fa-solid fa-database text-lg"></i> <span wire:loading.remove wire:target="spasiUBazu">Spasi podatke</span><span wire:loading wire:target="spasiUBazu">Spašavam...</span></button>
                                <button wire:click="posaljiUErp" wire:loading.attr="disabled" class="px-8 py-4 bg-gradient-to-r from-amber-400 to-orange-500 dark:from-amber-500 dark:to-orange-600 text-white font-black uppercase text-xs rounded-2xl shadow-lg hover:active:scale-95 transition-all flex items-center gap-3 group"><i class="fa-solid fa-server text-lg group-hover:animate-pulse"></i> <span wire:loading.remove wire:target="posaljiUErp">ERP Sync</span><span wire:loading wire:target="posaljiUErp">Sinhronizacija...</span></button>
                            </div>
                        @else
                            <div class="relative overflow-hidden w-full bg-emerald-50 dark:bg-[#061411] border border-emerald-200 dark:border-emerald-500/30 rounded-3xl p-8 text-center transition-colors">
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-200 rounded-2xl flex items-center justify-center mb-6 text-emerald-600 dark:text-emerald-400 shadow-sm"><i class="fa-solid fa-lock text-4xl"></i></div>
                                    <h2 class="text-2xl font-black uppercase text-emerald-700 dark:text-emerald-400 mb-2">Tender je zaključen</h2>
                                    <p class="text-slate-600 dark:text-slate-400 font-bold max-w-lg mx-auto mb-8">Sinhronizovano sa Pantheon ERP sistemom pod ključem: <strong>{{ $wf->erp_document_id }}</strong></p>
                                    <button wire:click="generisiKatalogPdf" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-emerald-700 dark:text-emerald-400 font-black uppercase text-[10px] rounded-lg transition-all flex items-center gap-2"><i class="fa-solid fa-download"></i> Preuzmi PDF Katalog</button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div> 
                @endif 
            </div>

            {{-- ZAVRŠNA FAZA: SLANJE --}}
            @if(in_array($wf->status, ['documentation_uploaded', 'offer_submitted', 'completed']))
            <div wire:transition.slide.down class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-white/5 backdrop-blur-sm rounded-3xl p-8 mt-6 relative overflow-hidden shadow-lg dark:shadow-none">
                <div class="absolute top-0 right-0 p-8 opacity-10 dark:opacity-5 text-emerald-500"><i class="fa-solid fa-paper-plane text-8xl"></i></div>
                <h3 class="text-lg font-black uppercase text-slate-800 dark:text-white flex items-center gap-4 mb-6 relative z-10">
                    <span class="bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 w-10 h-10 rounded-xl flex items-center justify-center text-sm border border-emerald-200 dark:border-emerald-500/20 shadow-sm">2</span> 
                    Slanje ponude na e-Nabavke
                </h3>
                @if(!in_array($wf->status, ['offer_submitted', 'completed']))
                    @php $sviDokumentiSpremni = $wf->tasks->count() > 0 && $wf->tasks->count() === $wf->tasks->where('status', 'pribavljeno')->count(); @endphp
                    <button wire:click="submitOffer" class="w-full relative z-10 font-black py-4 rounded-xl transition-all uppercase text-sm flex items-center justify-center gap-3 {{ $sviDokumentiSpremni ? 'bg-emerald-500 dark:bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-300 dark:border-rose-500/30 cursor-not-allowed' }}"><i class="fa-solid {{ $sviDokumentiSpremni ? 'fa-paper-plane' : 'fa-lock' }}"></i> {{ $sviDokumentiSpremni ? 'Potvrdi slanje' : 'Pribavite sve dokumente za otključavanje' }}</button>
                @else
                <div class="relative z-10 bg-emerald-50 dark:bg-emerald-500/5 border border-emerald-200 dark:border-emerald-500/20 rounded-xl p-5 text-[10px] font-black text-emerald-700 dark:text-emerald-500 uppercase tracking-widest flex items-center transition-colors"><i class="fa-solid fa-circle-check text-lg mr-3"></i> Ponuda uspješno zavedena i poslana.</div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(226, 232, 240, 0.5); }
    html.dark .custom-scrollbar::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.5); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.4); border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.6); }
    html.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.3); }
</style>