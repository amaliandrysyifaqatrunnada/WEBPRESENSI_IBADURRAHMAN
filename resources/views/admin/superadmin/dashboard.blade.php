<x-layouts.admin>
    <x-slot:title>Dasbor Superadmin - PKBM IBADURRAHMAN</x-slot:title>

    <!-- Header & Filters -->
    <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Dasbor Superadmin</h2>
            <p class="text-sm text-on-surface-variant mt-1">Rekapitulasi kehadiran dan pendidik seluruh unit pendidikan.</p>
        </div>
        
        <form action="{{ route('admin.superadmin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <!-- Filter Unit -->
            <div class="flex flex-col">
                <label for="filter-unit" class="text-xs font-bold text-on-surface-variant mb-1 uppercase">Unit Pendidikan</label>
                <select name="unit_id" id="filter-unit" onchange="this.form.submit()" class="bg-white border border-outline-variant rounded-xl py-2 px-4 text-sm font-medium focus:ring-primary focus:border-primary">
                    <option value="All" {{ $selectedUnitId === 'All' ? 'selected' : '' }}>Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ (string)$selectedUnitId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tanggal -->
            <div class="flex flex-col">
                <label for="filter-date" class="text-xs font-bold text-on-surface-variant mb-1 uppercase">Tanggal</label>
                <input type="date" name="date" id="filter-date" value="{{ $today }}" onchange="this.form.submit()" class="bg-white border border-outline-variant rounded-xl py-2 px-4 text-sm font-medium focus:ring-primary focus:border-primary"/>
            </div>
        </form>
    </div>

    <!-- Pendidik Stats Bento Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-gutter mb-8">
        <!-- Total Guru -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[130px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-primary text-xl">groups</span>
                <span class="text-xs font-bold uppercase tracking-wider">Total Pendidik</span>
            </div>
            <div class="mt-2">
                <h3 class="text-3xl font-extrabold text-on-surface">{{ $totalTeachers }}</h3>
                <p class="text-[10px] text-on-surface-variant mt-1">Gabungan seluruh unit</p>
            </div>
        </div>

        <!-- Total TK -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[130px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-emerald-700 text-xl">child_care</span>
                <span class="text-xs font-bold uppercase tracking-wider">Guru TK</span>
            </div>
            <div class="mt-2">
                <h3 class="text-3xl font-extrabold text-emerald-700">{{ $totalTK }}</h3>
                <p class="text-[10px] text-emerald-600 mt-1">Unit Taman Kanak-kanak</p>
            </div>
        </div>

        <!-- Total Paket A -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[130px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-primary text-xl">menu_book</span>
                <span class="text-xs font-bold uppercase tracking-wider">Guru Paket A</span>
            </div>
            <div class="mt-2">
                <h3 class="text-3xl font-extrabold text-on-surface">{{ $totalPaketA }}</h3>
                <p class="text-[10px] text-on-surface-variant mt-1">Setara SD</p>
            </div>
        </div>

        <!-- Total Paket B -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[130px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-primary text-xl">menu_book</span>
                <span class="text-xs font-bold uppercase tracking-wider">Guru Paket B</span>
            </div>
            <div class="mt-2">
                <h3 class="text-3xl font-extrabold text-on-surface">{{ $totalPaketB }}</h3>
                <p class="text-[10px] text-on-surface-variant mt-1">Setara SMP</p>
            </div>
        </div>

        <!-- Total Paket C -->
        <div class="card-layer-1 rounded-xl p-5 flex flex-col justify-between h-[130px] hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-primary text-xl">school</span>
                <span class="text-xs font-bold uppercase tracking-wider">Guru Paket C</span>
            </div>
            <div class="mt-2">
                <h3 class="text-3xl font-extrabold text-on-surface">{{ $totalPaketC }}</h3>
                <p class="text-[10px] text-on-surface-variant mt-1">Setara SMA</p>
            </div>
        </div>
    </div>

    <!-- Attendance Status Bento Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-gutter mb-8">
        <!-- Hadir -->
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between h-[110px] hover:shadow-md transition-shadow">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Sudah Hadir</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-emerald-700">{{ $totalPresentToday }}</span>
                <span class="material-symbols-outlined text-emerald-700 text-lg">check_circle</span>
            </div>
        </div>

        <!-- Belum Hadir -->
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between h-[110px] hover:shadow-md transition-shadow">
            <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Belum Hadir</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-slate-700">{{ $notCheckedInToday }}</span>
                <span class="material-symbols-outlined text-slate-500 text-lg">schedule</span>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between h-[110px] hover:shadow-md transition-shadow">
            <span class="text-[10px] font-bold text-orange-600 uppercase tracking-wider">Terlambat</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-orange-600">{{ $lateToday }}</span>
                <span class="material-symbols-outlined text-orange-600 text-lg">warning</span>
            </div>
        </div>

        <!-- Pulang Awal -->
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between h-[110px] hover:shadow-md transition-shadow">
            <span class="text-[10px] font-bold text-red-600 uppercase tracking-wider">Pulang Awal</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-red-600">{{ $earlyCheckoutToday }}</span>
                <span class="material-symbols-outlined text-red-600 text-lg">exit_to_app</span>
            </div>
        </div>
    </div>

    <!-- Charts & Methods Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-8">
        <!-- 1. Column Chart: Metode Kehadiran -->
        <div class="card-layer-1 rounded-xl p-6 flex flex-col justify-between">
            <h4 class="text-sm font-bold text-on-surface mb-4 uppercase tracking-wider border-b border-outline-variant/30 pb-2">Metode Kehadiran Hari Ini</h4>
            
            <div class="space-y-4">
                <!-- GPS -->
                <div class="flex items-center justify-between border-b border-outline-variant/20 pb-2">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[#006e1c] text-xl">location_on</span>
                        <span class="text-sm font-medium">GPS Geofence</span>
                    </div>
                    <span class="text-lg font-bold text-on-surface">{{ $gpsPresentToday }} <span class="text-xs text-outline font-normal">Guru</span></span>
                </div>

                <!-- QR -->
                <div class="flex items-center justify-between border-b border-outline-variant/20 pb-2">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-xl">qr_code_2</span>
                        <span class="text-sm font-medium">Scan QR Code</span>
                    </div>
                    <span class="text-lg font-bold text-on-surface">{{ $qrPresentToday }} <span class="text-xs text-outline font-normal">Guru</span></span>
                </div>

                <!-- Face ID -->
                <div class="flex items-center justify-between border-b border-outline-variant/20 pb-2">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-xl">face</span>
                        <span class="text-sm font-medium">Face ID (Selfie)</span>
                    </div>
                    <span class="text-lg font-bold text-on-surface">{{ $faceIdPresentToday }} <span class="text-xs text-outline font-normal">Guru</span></span>
                </div>
            </div>
            
            <p class="text-[10px] text-outline mt-4">Angka di atas dihitung berdasarkan log masuk (clock in) yang valid pada hari ini.</p>
        </div>

        <!-- 2. Column Chart: Tren Grafik Kehadiran Mingguan -->
        <div class="card-layer-1 rounded-xl p-6 lg:col-span-2">
            <h4 class="text-sm font-bold text-on-surface mb-4 uppercase tracking-wider border-b border-outline-variant/30 pb-2">Tren Kehadiran (7 Hari Terakhir)</h4>
            <div class="h-[220px] w-full">
                <canvas id="superadminWeeklyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Rekap per Unit Table Section -->
    <div class="card-layer-1 rounded-xl p-6">
        <h4 class="text-sm font-bold text-on-surface mb-4 uppercase tracking-wider border-b border-outline-variant/30 pb-2">Ringkasan Kehadiran Setiap Unit Pendidikan</h4>
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant text-xs text-outline uppercase font-semibold">
                        <th class="py-3 px-4">Nama Unit</th>
                        <th class="py-3 px-4">Total Guru</th>
                        <th class="py-3 px-4">Hadir</th>
                        <th class="py-3 px-4">Terlambat</th>
                        <th class="py-3 px-4">Pulang Awal</th>
                        <th class="py-3 px-4">Belum Absen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30 text-sm">
                    @foreach($unitSummary as $row)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-on-surface">{{ $row['unit']->name }}</td>
                            <td class="py-3.5 px-4 font-bold text-on-surface">{{ $row['total_guru'] }}</td>
                            <td class="py-3.5 px-4 text-emerald-700 font-bold">{{ $row['hadir'] }}</td>
                            <td class="py-3.5 px-4 text-orange-600 font-bold">{{ $row['terlambat'] }}</td>
                            <td class="py-3.5 px-4 text-red-600 font-semibold">{{ $row['pulang_awal'] }}</td>
                            <td class="py-3.5 px-4 text-slate-500 font-medium">{{ $row['belum_hadir'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart Configuration Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('superadminWeeklyChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [
                        {
                            label: 'Hadir',
                            data: {!! json_encode($chartPresent) !!},
                            borderColor: '#2E7D32',
                            backgroundColor: 'rgba(46, 125, 50, 0.1)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35
                        },
                        {
                            label: 'Terlambat',
                            data: {!! json_encode($chartLate) !!},
                            borderColor: '#F57C00',
                            backgroundColor: 'rgba(245, 124, 0, 0.05)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    family: 'Plus Jakarta Sans',
                                    size: 11
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-layouts.admin>
