<aside id="sidebar" class="fixed lg:relative w-72 lg:w-64 glass-card h-full flex flex-col border-r border-slate-200 dark:border-slate-800 z-50 transition-colors duration-300">

    <div class="p-8 text-xl font-extrabold tracking-tighter text-slate-900 dark:text-white flex items-center gap-2">
        Penny Plus <span class="text-blue-600 dark:text-blue-500 text-sm italic">Tenderi</span>
    </div>

    <nav class="flex-1 px-4 space-y-1 mt-4 overflow-y-auto custom-scrollbar">

        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase px-4 mb-4 tracking-[0.2em]">
            Navigacija
        </p>

        {{-- DASHBOARD --}}
        <a href="/dashboard"
           wire:navigate
           class="flex items-center gap-3 p-3.5 rounded-2xl transition-all group
           {{ request()->is('dashboard')
                ? 'bg-blue-600/10 text-blue-600 dark:text-blue-400 font-semibold border-r-2 border-blue-500'
                : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            Glavni pregled
        </a>

        {{-- TENDERS MAIN --}}
        <a href="/tenderi"
           wire:navigate
           class="flex items-center gap-3 p-3.5 rounded-2xl transition-all group
           {{ request()->is('tenderi') || (request()->is('tenderi/*') && !request()->is('tenderi/najave'))
                ? 'bg-blue-600/10 text-blue-600 dark:text-blue-400 font-semibold border-r-2 border-blue-500'
                : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
            <i data-lucide="briefcase" class="w-5 h-5"></i>
            Tenderi
        </a>

        {{-- CPV --}}
        <a href="/cpv-kodovi"
           wire:navigate
           class="flex items-center gap-3 p-3.5 rounded-2xl transition-all group
           {{ request()->is('cpv*')
                ? 'bg-blue-600/10 text-blue-600 dark:text-blue-400 font-semibold border-r-2 border-blue-500'
                : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
            <i data-lucide="tags" class="w-5 h-5"></i>
            CPV Kodovi
        </a>

        {{-- ANNOUNCEMENTS --}}
        <a href="/tenderi/najave"
           wire:navigate
           class="flex items-center gap-3 p-3.5 rounded-2xl transition-all group
           {{ request()->is('tenderi/najave')
                ? 'bg-blue-600/10 text-blue-600 dark:text-blue-400 font-semibold border-r-2 border-blue-500'
                : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
            <i data-lucide="megaphone" class="w-5 h-5"></i>
            Tenderi u najavi
        </a>

        {{-- SETTINGS --}}
        <a href="/postavke"
           wire:navigate
           class="flex items-center gap-3 p-3.5 rounded-2xl transition-all group
           {{ request()->is('postavke*')
                ? 'bg-blue-600/10 text-blue-600 dark:text-blue-400 font-semibold border-r-2 border-blue-500'
                : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 dark:hover:text-white' }}">
            <i data-lucide="settings" class="w-5 h-5"></i>
            Postavke
        </a>

        {{-- LOGOUT --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full mt-2">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 p-3.5 rounded-2xl transition-all group
                    text-slate-500 dark:text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-400">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                Odjava
            </button>
        </form>

    </nav>

    {{-- THEME TOGGLE --}}
    <div class="px-6 mb-4">
        <button wire:click="toggleTheme"
                class="w-full flex items-center justify-between p-3 rounded-2xl bg-slate-100 dark:bg-slate-800/50 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-blue-500 transition-all group">

            <span class="text-[10px] font-black uppercase tracking-widest group-hover:text-blue-500">
                Tema sistema
            </span>

            <div class="flex items-center">
                @if(auth()->check() && auth()->user()->theme === 'dark')
                    <i data-lucide="sun" class="w-4 h-4 text-yellow-400"></i>
                @else
                    <i data-lucide="moon" class="w-4 h-4 text-slate-600"></i>
                @endif
            </div>
        </button>
    </div>

    {{-- USER --}}
    <div class="p-6 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/20">
        <div class="flex items-center gap-3">

            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-sm text-white shadow-lg shadow-blue-900/20">
                {{ substr(auth()->user()?->name ?? 'PP', 0, 2) }}
            </div>

            <div class="truncate">
                <p class="text-xs font-bold truncate text-slate-900 dark:text-white">
                    {{ auth()->user()?->first_name ?? 'Ime' }} {{ auth()->user()?->last_name ?? 'Prezime' }}
                </p>
                <p class="text-[10px] text-slate-500 font-medium">
                    Penny Plus d.o.o.
                </p>
            </div>

        </div>
    </div>

</aside>