<div class="min-h-screen flex items-center justify-center p-6 relative">
    {{-- Pozadinski glow efekti --}}
    <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-blue-600/20 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-indigo-600/10 blur-[120px] rounded-full"></div>

    <div class="w-full max-w-md relative z-10">
        {{-- LOGO --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-extrabold tracking-tighter text-white">
                Penny Plus <span class="text-blue-500 italic text-xl">Tenderi</span>
            </h1>
            <p class="text-slate-400 text-xs mt-2 uppercase tracking-widest font-bold">Obavještajni Sistem v2.0</p>
        </div>

        {{-- KARTICA --}}
        <div class="bg-slate-900/50 border border-white/5 backdrop-blur-xl rounded-[2rem] p-10 shadow-2xl">
            <form wire:submit.prevent="login" class="space-y-6">
                
                {{-- Email --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2 ml-1">Email Adresa</label>
                    <input wire:model="email" type="email" 
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl py-4 px-5 text-white focus:border-blue-500 outline-none transition-all shadow-inner"
                        placeholder="ime.prezime@pennyplus.com">
                    @error('email') <span class="text-rose-500 text-[10px] font-bold mt-1 ml-1 uppercase">{{ $message }}</span> @enderror
                </div>

                {{-- Lozinka --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2 ml-1">Lozinka</label>
                    <input wire:model="password" type="password" 
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl py-4 px-5 text-white focus:border-blue-500 outline-none transition-all shadow-inner"
                        placeholder="••••••••">
                    @error('password') <span class="text-rose-500 text-[10px] font-bold mt-1 ml-1 uppercase">{{ $message }}</span> @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-0 focus:ring-offset-0 transition-all">
                        <span class="text-[10px] font-bold text-slate-500 group-hover:text-slate-300 uppercase tracking-wide">Zapamti me</span>
                    </label>
                    <a href="#" class="text-[10px] font-bold text-blue-500 hover:text-blue-400 uppercase tracking-wide">Zaboravljena lozinka?</a>
                </div>

                {{-- Dugme --}}
                <button type="submit" wire:loading.attr="disabled" 
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-xl transition-all uppercase tracking-[0.2em] text-xs shadow-lg shadow-blue-900/40 flex items-center justify-center gap-3 group">
                    <span wire:loading.remove wire:target="login">Prijavi se na sistem</span>
                    <span wire:loading wire:target="login" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Autentifikacija...
                    </span>
                    <i wire:loading.remove wire:target="login" class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>
        </div>

        <div class="text-center mt-8">
            <p class="text-slate-600 text-[10px] font-bold uppercase tracking-widest">© 2026 Penny Plus d.o.o. Sarajevo</p>
        </div>
    </div>
</div>