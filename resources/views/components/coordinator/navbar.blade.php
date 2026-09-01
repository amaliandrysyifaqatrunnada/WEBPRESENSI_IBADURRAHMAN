<header class="flex justify-between items-center w-full h-16 px-4 sm:px-6 lg:px-8 border-b border-slate-200/80 bg-white/90 backdrop-blur-md sticky top-0 z-40">
    <!-- Brand Title -->
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-sm">
            PKBM
        </div>
        <div class="flex flex-col">
            <span class="text-xs sm:text-sm font-extrabold text-slate-900 tracking-tight leading-none">PORTAL KOORDINATOR</span>
            <span class="text-[10px] font-bold text-emerald-700 mt-0.5 leading-none">{{ auth()->user()->unit ? strtoupper(auth()->user()->unit->name) : 'GLOBAL' }}</span>
        </div>
    </div>

    <!-- User Profile Dropdown Badge -->
    <div class="flex items-center gap-3 ml-auto">
        <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-full bg-slate-100/80 border border-slate-200/60 shadow-2xs">
            <div class="w-7 h-7 rounded-full overflow-hidden border border-emerald-500/50 bg-emerald-100 flex items-center justify-center shrink-0">
                <img class="w-full h-full object-cover" alt="Avatar" src="{{ auth()->user()->avatar_url }}"/>
            </div>
            <div class="flex flex-col text-left select-none max-w-[160px]">
                <span class="text-xs font-bold text-slate-800 truncate leading-tight">{{ auth()->user()->name }}</span>
                <span class="text-[9.5px] text-emerald-700 font-extrabold truncate leading-none">Koordinator {{ auth()->user()->unit ? auth()->user()->unit->name : '-' }}</span>
            </div>
        </div>
    </div>
</header>
