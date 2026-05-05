@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        {{-- Mobile View --}}
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 text-[10px] font-bold text-slate-600 bg-slate-900/50 border border-slate-800 rounded-xl cursor-default uppercase">Prethodna</span>
            @else
                <button wire:click="previousPage" class="px-4 py-2 text-[10px] font-bold text-white bg-slate-900 border border-slate-800 rounded-xl hover:bg-rose-600 hover:border-rose-600 transition-all uppercase">Prethodna</button>
            @endif

            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" class="px-4 py-2 text-[10px] font-bold text-white bg-slate-900 border border-slate-800 rounded-xl hover:bg-rose-600 hover:border-rose-600 transition-all uppercase">Sljedeća</button>
            @else
                <span class="px-4 py-2 text-[10px] font-bold text-slate-600 bg-slate-900/50 border border-slate-800 rounded-xl cursor-default uppercase">Sljedeća</span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                    Prikazano <span class="text-slate-300">{{ $paginator->firstItem() }}</span> - <span class="text-slate-300">{{ $paginator->lastItem() }}</span> od <span class="text-rose-500">{{ $paginator->total() }}</span> tendera
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm bg-slate-900 p-1.5 rounded-2xl border border-slate-800">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="p-2 text-slate-700 cursor-default">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        </span>
                    @else
                        <button wire:click="previousPage" class="p-2 text-slate-400 hover:text-white hover:bg-rose-600 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="px-4 py-2 text-slate-600 cursor-default">...</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="px-4 py-2 bg-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-900/20 cursor-default">{{ $page }}</span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 text-slate-400 hover:text-white text-xs font-bold rounded-xl transition-all">{{ $page }}</button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" class="p-2 text-slate-400 hover:text-white hover:bg-rose-600 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </button>
                    @else
                        <span class="p-2 text-slate-700 cursor-default">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif