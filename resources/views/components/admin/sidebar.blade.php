<aside id="sidebar" class="w-[280px] h-screen fixed left-0 top-0 bg-surface dark:bg-inverse-surface border-r border-outline-variant dark:border-outline flex flex-col h-full py-6 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <!-- Brand Logo Header -->
    <div class="px-6 pb-6 flex items-center gap-4 border-b border-outline-variant dark:border-outline mb-6">
        <div class="w-10 h-10 flex items-center justify-center shrink-0">
            <img alt="PKBM IBADURRAHMAN Logo" class="w-full h-full object-contain" src="{{ asset('images/logo-pkbm-transparent.png') }}"/>
        </div>
        <div>
            <h1 class="font-headline-md text-headline-md font-bold text-primary dark:text-inverse-primary truncate">IBADURRAHMAN</h1>
            <p class="font-label-sm text-label-sm text-on-surface-variant truncate">Manajemen Guru</p>
        </div>
    </div>
    <!-- Navigation Links -->
    <nav class="flex-1 px-2 flex flex-col gap-2 overflow-y-auto custom-scrollbar">
        @if(auth()->user()->hasRole('superadmin'))
            <!-- Superadmin Menu -->
            <a class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.superadmin.dashboard') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.superadmin.dashboard') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.superadmin.dashboard') ? '1' : '0' }};">dashboard</span>
                <span class="font-label-md text-label-md">Dasbor</span>
            </a>

            <div class="px-4 py-2 mt-2 text-xs font-bold text-outline uppercase tracking-wider">Pengaturan Global</div>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/holidays*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.holidays.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('admin/holidays*') ? '1' : '0' }};">event_note</span>
                <span class="font-label-md text-label-md">Hari Libur</span>
            </a>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/coordinators*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.coordinators.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('admin/coordinators*') ? '1' : '0' }};">manage_accounts</span>
                <span class="font-label-md text-label-md">Koordinator Paket</span>
            </a>

            <div class="px-4 py-2 mt-2 text-xs font-bold text-outline uppercase tracking-wider">Rekapitulasi</div>
            
            <a class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.superadmin.recap') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.superadmin.recap') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.superadmin.recap') ? '1' : '0' }};">summarize</span>
                <span class="font-label-md text-label-md">Semua Presensi</span>
            </a>

            <div class="px-4 py-2 mt-2 text-xs font-bold text-outline uppercase tracking-wider">Data & Jadwal</div>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.teachers.index') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.teachers.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.teachers.index') ? '1' : '0' }};">group</span>
                <span class="font-label-md text-label-md">Semua Tenaga Pendidik</span>
            </a>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/teachers-schedule*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.teachers.schedule.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('admin/teachers-schedule*') ? '1' : '0' }};">edit_calendar</span>
                <span class="font-label-md text-label-md">Jadwal Tenaga Pendidik</span>
            </a>

            <div class="px-4 py-2 mt-2 text-xs font-bold text-outline uppercase tracking-wider">Izin & Laporan</div>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/leaves*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.leaves.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('admin/leaves*') ? '1' : '0' }};">assignment_turned_in</span>
                <span class="font-label-md text-label-md">Persetujuan Izin</span>
            </a>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/reports*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.reports.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('admin/reports*') ? '1' : '0' }};">assessment</span>
                <span class="font-label-md text-label-md">Laporan Presensi</span>
            </a>
        @else
            <!-- Admin Unit Menu -->
            <a class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.dashboard') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.dashboard') ? '1' : '0' }};">dashboard</span>
                <span class="font-label-md text-label-md">Dasbor</span>
            </a>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('admin.teachers.index') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.teachers.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.teachers.index') ? '1' : '0' }};">group</span>
                <span class="font-label-md text-label-md">Data Guru</span>
            </a>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/teachers-schedule*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.teachers.schedule.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('admin/teachers-schedule*') ? '1' : '0' }};">edit_calendar</span>
                <span class="font-label-md text-label-md">Jadwal Tenaga Pendidik</span>
            </a>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/leaves*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.leaves.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('admin/leaves*') ? '1' : '0' }};">assignment_turned_in</span>
                <span class="font-label-md text-label-md">Persetujuan Izin</span>
            </a>

            <a class="flex items-center gap-3 px-4 py-3 {{ (request()->is('admin/settings*') || request()->is('admin/devices*')) ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.settings.attendance') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ (request()->is('admin/settings*') || request()->is('admin/devices*')) ? '1' : '0' }};">settings</span>
                <span class="font-label-md text-label-md">Pengaturan Kehadiran</span>
            </a>

            <a class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/reports*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-primary rounded-r-lg font-bold' : 'text-on-surface-variant hover:bg-surface-container-high rounded-r-lg' }} transition-all duration-200 ease-in-out" href="{{ route('admin.reports.index') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('admin/reports*') ? '1' : '0' }};">assessment</span>
                <span class="font-label-md text-label-md">Laporan</span>
            </a>
        @endif

    </nav>
    <!-- Footer Actions -->
    <div class="px-2 pt-4 border-t border-outline-variant dark:border-outline mt-auto">
        <form action="{{ route('admin.logout') }}" method="POST" id="admin-logout-form">
            @csrf
            <button type="submit" class="flex items-center gap-3 px-4 py-3 text-on-surface-variant hover:text-error hover:bg-error-container/20 transition-all duration-200 ease-in-out rounded-lg w-full text-left">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-label-md text-label-md">Keluar</span>
            </button>
        </form>
    </div>
</aside>
