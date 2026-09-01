<x-layouts.admin>
    <x-slot:title>PKBM IBADURRAHMAN - Admin Dashboard</x-slot:title>

    <!-- Greeting Section -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h2 class="font-display-lg text-display-lg text-on-background mb-1 tracking-tight">Selamat datang kembali, Admin</h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Berikut adalah ringkasan kehadiran hari ini.</p>
        </div>
    </div>

    <!-- Bento Grid Layout -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter mb-6">
        <!-- Card 1: Total Guru -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[135px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2.5 text-on-surface-variant">
                <div class="w-9 h-9 rounded-full bg-surface-container flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-sm font-semibold" style="font-variation-settings: 'FILL' 1;">groups</span>
                </div>
                <span class="font-label-sm text-label-sm">Total Guru</span>
            </div>
            <div class="flex flex-col mt-2">
                <span class="font-headline-sm text-headline-sm text-on-surface font-bold leading-none">{{ $totalTeachers }}</span>
                <span class="text-[10px] text-on-surface-variant mt-1.5">Semua Pendidik</span>
            </div>
        </div>

        <!-- Card 2: Hadir Hari Ini -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[135px] hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-primary/5 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant relative z-10">
                <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-700 border border-emerald-100">
                    <span class="material-symbols-outlined text-sm font-semibold" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <span class="font-label-sm text-label-sm">Hadir Hari Ini</span>
            </div>
            <div class="flex items-end justify-between relative z-10 mt-2">
                <span class="font-headline-sm text-headline-sm text-on-surface font-bold leading-none">{{ $totalPresentToday }}</span>
                <span class="text-[10px] badge-success px-2 py-0.5 rounded-full font-bold">{{ $presentRate }}% Rate</span>
            </div>
        </div>

        <!-- Card 3: Guru Terlambat -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[135px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2.5 text-on-surface-variant">
                <div class="w-9 h-9 rounded-full bg-orange-50 flex items-center justify-center text-orange-600 border border-orange-100">
                    <span class="material-symbols-outlined text-sm font-semibold" style="font-variation-settings: 'FILL' 1;">warning</span>
                </div>
                <span class="font-label-sm text-label-sm">Terlambat</span>
            </div>
            <div class="flex flex-col mt-2">
                <span class="font-headline-sm text-headline-sm text-orange-600 font-bold leading-none">{{ $lateToday }}</span>
                <span class="text-[10px] text-orange-600 mt-1.5 font-medium">Terlambat Hari Ini</span>
            </div>
        </div>

        <!-- Card 4: Belum Presensi -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[135px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2.5 text-on-surface-variant">
                <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 border border-slate-200">
                    <span class="material-symbols-outlined text-sm font-semibold" style="font-variation-settings: 'FILL' 1;">schedule</span>
                </div>
                <span class="font-label-sm text-label-sm">Belum Absen</span>
            </div>
            <div class="flex flex-col mt-2">
                <span class="font-headline-sm text-headline-sm text-slate-700 font-bold leading-none">{{ $notCheckedInToday }}</span>
                <span class="text-[10px] text-slate-600 mt-1.5 font-medium">Belum Presensi Hari Ini</span>
            </div>
        </div>

        <!-- Card 5: Izin Hari Ini -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[135px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2.5 text-on-surface-variant">
                <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-700 border border-blue-100">
                    <span class="material-symbols-outlined text-sm font-semibold" style="font-variation-settings: 'FILL' 1;">event_busy</span>
                </div>
                <span class="font-label-sm text-label-sm">Izin Hari Ini</span>
            </div>
            <div class="flex flex-col mt-2">
                <span class="font-headline-sm text-headline-sm text-blue-700 font-bold leading-none">{{ $izinToday ?? 0 }}</span>
                <span class="text-[10px] text-blue-600 mt-1.5 font-medium">Izin Berketerangan / Tanpa Ket.</span>
            </div>
        </div>

        <!-- Card 6: Sakit Hari Ini -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[135px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2.5 text-on-surface-variant">
                <div class="w-9 h-9 rounded-full bg-purple-50 flex items-center justify-center text-purple-700 border border-purple-100">
                    <span class="material-symbols-outlined text-sm font-semibold" style="font-variation-settings: 'FILL' 1;">medical_services</span>
                </div>
                <span class="font-label-sm text-label-sm">Sakit Hari Ini</span>
            </div>
            <div class="flex flex-col mt-2">
                <span class="font-headline-sm text-headline-sm text-purple-700 font-bold leading-none">{{ $sakitToday ?? 0 }}</span>
                <span class="text-[10px] text-purple-600 mt-1.5 font-medium">Izin Sakit Terverifikasi</span>
            </div>
        </div>
    </div>

    <!-- Metode Kehadiran Hari Ini -->
    <div class="mt-8">
        <h3 class="font-headline-sm text-headline-sm text-on-background mb-4">Metode Kehadiran Hari Ini</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
            <!-- QR Card -->
            <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[110px] hover:shadow-md transition-shadow bg-white">
                <div class="flex items-center gap-2.5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-primary text-xl">qr_code_2</span>
                    <span class="font-label-sm text-label-sm">Scan QR Code</span>
                </div>
                <div class="flex justify-between items-baseline mt-2">
                    <span class="font-headline-sm text-2xl text-on-surface font-bold">{{ $qrPresentToday }}</span>
                    <span class="text-[10px] text-outline">Guru</span>
                </div>
            </div>

            <!-- GPS Card -->
            <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[110px] hover:shadow-md transition-shadow bg-white">
                <div class="flex items-center gap-2.5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-[#006e1c] text-xl">location_on</span>
                    <span class="font-label-sm text-label-sm">GPS Geofence</span>
                </div>
                <div class="flex justify-between items-baseline mt-2">
                    <span class="font-headline-sm text-2xl text-on-surface font-bold">{{ $gpsPresentToday }}</span>
                    <span class="text-[10px] text-outline">Guru</span>
                </div>
            </div>

            <!-- Face ID Card -->
            <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[110px] hover:shadow-md transition-shadow bg-white">
                <div class="flex items-center gap-2.5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-primary text-xl">face</span>
                    <span class="font-label-sm text-label-sm">Face ID (Selfie)</span>
                </div>
                <div class="flex justify-between items-baseline mt-2">
                    <span class="font-headline-sm text-2xl text-on-surface font-bold">{{ $faceIdPresentToday }}</span>
                    <span class="text-[10px] text-outline">Guru</span>
                </div>
            </div>

            <!-- Manual Card -->
            <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[110px] hover:shadow-md transition-shadow bg-white">
                <div class="flex items-center gap-2.5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-outline text-xl">edit_square</span>
                    <span class="font-label-sm text-label-sm">Manual (Admin)</span>
                </div>
                <div class="flex justify-between items-baseline mt-2">
                    <span class="font-headline-sm text-2xl text-on-surface font-bold">{{ $manualPresentToday }}</span>
                    <span class="text-[10px] text-outline">Guru</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="mt-8 card-layer-1 rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Diagram Kehadiran Bulanan</h3>
            <select onchange="window.location.href = '{{ route('admin.dashboard') }}?month=' + this.value" class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-1.5 font-label-md text-label-md text-on-surface focus:ring-primary focus:border-primary cursor-pointer">
                @for ($i = 0; $i < 6; $i++)
                    @php
                        $monthVal = \Carbon\Carbon::now()->subMonths($i)->format('Y-m');
                        $monthLabel = \Carbon\Carbon::now()->subMonths($i);
                        $monthNames = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        $monthName = $monthNames[$monthLabel->month];
                        $year = $monthLabel->year;
                    @endphp
                    <option value="{{ $monthVal }}" {{ $selectedMonth === $monthVal ? 'selected' : '' }}>
                        {{ $monthName }} {{ $year }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="relative h-[250px] w-full flex items-end">
            <!-- Grid lines and Y axis -->
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-8">
                <div class="flex items-center w-full border-b border-outline-variant/50 h-0"><span class="w-8 -mt-2 text-xs text-on-surface-variant text-right pr-2">100%</span></div>
                <div class="flex items-center w-full border-b border-outline-variant/50 h-0"><span class="w-8 -mt-2 text-xs text-on-surface-variant text-right pr-2">75%</span></div>
                <div class="flex items-center w-full border-b border-outline-variant/50 h-0"><span class="w-8 -mt-2 text-xs text-on-surface-variant text-right pr-2">50%</span></div>
                <div class="flex items-center w-full border-b border-outline-variant/50 h-0"><span class="w-8 -mt-2 text-xs text-on-surface-variant text-right pr-2">25%</span></div>
                <div class="flex items-center w-full border-b border-outline-variant h-0"><span class="w-8 -mt-2 text-xs text-on-surface-variant text-right pr-2">0%</span></div>
            </div>
            <!-- Bars -->
            <div class="flex-1 flex justify-around items-end h-[calc(100%-32px)] ml-10 z-10">
                @for ($w = 1; $w <= 4; $w++)
                    <div class="flex flex-col items-center gap-2 group w-full h-full justify-end">
                        <div class="flex items-end justify-center w-full gap-1 sm:gap-2 h-[200px]">
                            <!-- Terlambat (Light/transparent green) -->
                            <div class="w-full max-w-[24px] bg-primary/30 rounded-t-sm transition-colors group-hover:bg-primary/50" 
                                 style="height: {{ $weeksData[$w]['terlambat_percent'] }}%;"
                                 title="Terlambat: {{ $weeksData[$w]['terlambat_percent'] }}% ({{ $weeksData[$w]['terlambat_count'] }} kali)"></div>
                            
                            <!-- Tepat Waktu (Dark green) -->
                            <div class="w-full max-w-[24px] bg-primary rounded-t-sm transition-colors group-hover:bg-primary/90" 
                                 style="height: {{ $weeksData[$w]['hadir_percent'] }}%;"
                                 title="Tepat Waktu: {{ $weeksData[$w]['hadir_percent'] }}% ({{ $weeksData[$w]['hadir_count'] }} kali)"></div>
                        </div>
                        <span class="text-xs text-on-surface-variant font-label-sm">Minggu {{ $w }}</span>
                    </div>
                @endfor
            </div>
        </div>
        <!-- Legend -->
        <div class="flex justify-center items-center gap-6 mt-6">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-primary"></div>
                <span class="text-xs text-on-surface-variant font-label-sm">Tepat Waktu</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-primary/30"></div>
                <span class="text-xs text-on-surface-variant font-label-sm">Terlambat</span>
            </div>
        </div>
    </div>
</x-layouts.admin>
