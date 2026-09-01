<aside id="coordinator-sidebar" class="w-[280px] h-screen fixed left-0 top-0 bg-white border-r border-slate-200/80 flex flex-col h-full py-6 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-xs">
    <!-- Brand Logo Header -->
    <div class="px-6 pb-6 flex items-center gap-3.5 border-b border-slate-100 mb-6">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0 p-1.5 shadow-xs">
            <img alt="PKBM IBADURRAHMAN Logo" class="w-full h-full object-contain" src="{{ asset('images/logo-pkbm-transparent.png') }}"/>
        </div>
        <div class="flex flex-col min-w-0">
            <h1 class="text-sm font-extrabold text-slate-900 tracking-tight truncate leading-tight">IBADURRAHMAN</h1>
            <span class="text-[11px] font-bold text-emerald-700 truncate">Portal Koordinator</span>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-3 flex flex-col gap-1.5 overflow-y-auto custom-scrollbar">
        <a class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-xs transition-all duration-200 {{ request()->routeIs('coordinator.dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}" href="{{ route('coordinator.dashboard') }}">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' {{ request()->routeIs('coordinator.dashboard') ? '1' : '0' }};">dashboard</span>
            <span>Dashboard</span>
        </a>

        <div class="px-4 py-2 mt-4 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Persetujuan & Perizinan</div>

        <a class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-xs transition-all duration-200 {{ request()->routeIs('coordinator.leaves*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}" href="{{ route('coordinator.leaves.index') }}">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' {{ request()->routeIs('coordinator.leaves*') ? '1' : '0' }};">assignment_turned_in</span>
            <span>Persetujuan Perizinan</span>
        </a>
    </nav>

    <!-- Footer Profile / Logout -->
    <div class="px-3 pt-4 border-t border-slate-100 mt-auto">
        <form action="{{ route('admin.logout') }}" method="POST" id="coordinator-logout-form">
            @csrf
            <button type="submit" class="flex items-center justify-between px-4 py-3 text-slate-600 hover:text-rose-600 hover:bg-rose-50 transition-all duration-200 rounded-xl w-full text-left font-bold text-xs group">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-lg group-hover:rotate-12 transition-transform">logout</span>
                    <span>Keluar Sistem</span>
                </div>
                <span class="material-symbols-outlined text-sm text-slate-400 group-hover:text-rose-600">chevron_right</span>
            </button>
        </form>
    </div>
</aside>
