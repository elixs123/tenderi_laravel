<div class="relative min-h-screen bg-[#050b14] text-slate-200 p-6 lg:p-12">
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-indigo-600/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="relative z-10 max-w-5xl mx-auto">
        <header class="mb-10">
            <h1 class="text-3xl font-black uppercase text-white tracking-tight">Postavke <span class="text-indigo-500">Filtera</span></h1>
            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mt-1">Konfiguracija EJN izvora i kontakta</p>
        </header>

        <div class="space-y-6">

            {{-- REGIJE --}}
            <div class="bg-slate-900/40 border border-white/5 backdrop-blur-md rounded-3xl p-8">
                <h3 class="text-xs font-black uppercase text-indigo-400 mb-6">Aktivne regije ({{ count($availableRegions) }})</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($availableRegions as $region)
                        <label class="cursor-pointer">
                            <input type="checkbox" wire:model="selectedRegions" value="{{ $region }}" class="hidden peer">
                            <div class="p-3 rounded-xl border border-slate-800 bg-slate-950/40 peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 transition-all text-center">
                                <span class="text-[10px] font-black uppercase text-slate-400 peer-checked:text-indigo-400">{{ $region }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- TIPOVI --}}
            <div class="bg-slate-900/40 border border-white/5 backdrop-blur-md rounded-3xl p-8">
                <h3 class="text-xs font-black uppercase text-emerald-400 mb-6">Tipovi ugovora</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($availableTypes as $type)
                        <label class="cursor-pointer">
                            <input type="checkbox" wire:model="selectedTypes" value="{{ $type }}" class="hidden peer">
                            <div class="px-5 py-2.5 rounded-xl border border-slate-800 bg-slate-950/40 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 transition-all flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-700 peer-checked:bg-emerald-500"></div>
                                <span class="text-[10px] font-black uppercase text-slate-400 peer-checked:text-emerald-400">{{ $type }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <button wire:click="saveSettings" class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-black uppercase text-[10px] tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-900/20">
                Sačuvaj moje postavke
            </button>
        </div>
    </div>
</div>