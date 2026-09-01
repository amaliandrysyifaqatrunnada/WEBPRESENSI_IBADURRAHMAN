<x-layouts.admin>
    <x-slot:title>Laporan Kehadiran - PKBM IBADURRAHMAN</x-slot:title>

    <!-- Page Header & Export Controls -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface mb-2">Laporan Kehadiran</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Analisis rekapitulasi presensi bulanan, mingguan, harian, dan tahunan beserta denda.</p>
        </div>
        <div class="flex gap-3 w-full sm:w-auto">
            <!-- Dynamic Export Links passing current filters -->
            <a href="{{ route('admin.reports.export.excel', request()->all()) }}" class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 bg-surface border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface hover:bg-surface-container-low transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">download</span>
                Ekspor Excel
            </a>
            <a href="{{ route('admin.reports.export.pdf', request()->all()) }}" class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 bg-[#2E7D32] hover:bg-[#1b6d24] text-white rounded-lg font-label-md text-label-md transition-colors shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[20px]">picture_as_pdf</span>
                Unduh PDF
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-surface-container-lowest border border-[#E6ECE7] rounded-xl p-6 shadow-sm mb-8">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="space-y-4" id="filterForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Report Type Selection -->
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2">Periode Laporan</label>
                    <div class="relative">
                        <select name="type" id="report-type" onchange="togglePeriodFields(); this.form.submit();" class="appearance-none w-full bg-white border border-outline-variant rounded-xl py-2 px-4 font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-primary">
                            <option value="harian" {{ $filters['type'] === 'harian' ? 'selected' : '' }}>Harian</option>
                            <option value="mingguan" {{ $filters['type'] === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                            <option value="bulanan" {{ $filters['type'] === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                            <option value="tahunan" {{ $filters['type'] === 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[20px]">expand_more</span>
                    </div>
                </div>

                <!-- Dynamic Period Sub-Fields -->
                <!-- Harian: Date Input -->
                <div class="flex flex-col period-field" id="field-harian">
                    <label class="font-label-md text-label-md text-on-surface mb-2">Pilih Tanggal</label>
                    <input type="date" name="date" value="{{ $filters['date'] }}" onchange="this.form.submit()" class="w-full bg-white border border-outline-variant rounded-xl py-2 px-4 font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-primary"/>
                </div>

                <!-- Mingguan: Start / End Date -->
                <div class="flex flex-col period-field hidden" id="field-mingguan-start">
                    <label class="font-label-md text-label-md text-on-surface mb-2">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}" onchange="this.form.submit()" class="w-full bg-white border border-outline-variant rounded-xl py-2 px-4 font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-primary"/>
                </div>
                <div class="flex flex-col period-field hidden" id="field-mingguan-end">
                    <label class="font-label-md text-label-md text-on-surface mb-2">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}" onchange="this.form.submit()" class="w-full bg-white border border-outline-variant rounded-xl py-2 px-4 font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-primary"/>
                </div>

                <!-- Bulanan: Month & Year -->
                <div class="flex flex-col period-field hidden" id="field-bulanan-month">
                    <label class="font-label-md text-label-md text-on-surface mb-2">Bulan</label>
                    <div class="relative">
                        <select name="month" onchange="this.form.submit()" class="appearance-none w-full bg-white border border-outline-variant rounded-xl py-2 px-4 font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-primary">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $filters['month'] == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(2023, $m, 1)->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[20px]">expand_more</span>
                    </div>
                </div>

                <!-- Tahunan: Year Input -->
                <div class="flex flex-col period-field hidden" id="field-tahunan-year">
                    <label class="font-label-md text-label-md text-on-surface mb-2">Tahun</label>
                    <div class="relative">
                        <select name="year" onchange="this.form.submit()" class="appearance-none w-full bg-white border border-outline-variant rounded-xl py-2 px-4 font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-primary">
                            @for ($y = date('Y') - 5; $y <= date('Y'); $y++)
                                <option value="{{ $y }}" {{ $filters['year'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[20px]">expand_more</span>
                    </div>
                </div>

                <!-- Teacher Select Filter -->
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2">Nama Guru</label>
                    <div class="relative">
                        <select name="teacher_id" onchange="this.form.submit()" class="appearance-none w-full bg-white border border-outline-variant rounded-xl py-2 px-4 font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-primary">
                            <option value="All Teachers" {{ $filters['teacher_id'] === 'All Teachers' ? 'selected' : '' }}>Semua Guru</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ $filters['teacher_id'] == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[20px]">expand_more</span>
                    </div>
                </div>

                <!-- Status Select Filter -->
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2">Status Kehadiran</label>
                    <div class="relative">
                        <select name="status" onchange="this.form.submit()" class="appearance-none w-full bg-white border border-outline-variant rounded-xl py-2 px-4 font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-primary">
                            <option value="All Status" {{ $filters['status'] === 'All Status' ? 'selected' : '' }}>Semua Status</option>
                            <option value="hadir" {{ $filters['status'] === 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="terlambat" {{ $filters['status'] === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[20px]">expand_more</span>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-gutter mb-8">
        <!-- Hadir -->
        <div class="card-layer-1 rounded-xl p-4 flex items-center justify-between bg-surface-container-lowest border border-outline-variant">
            <div class="space-y-1">
                <span class="text-[11px] text-on-surface-variant font-medium">Hadir</span>
                <h4 class="font-headline-sm text-headline-sm text-[#2E7D32] font-bold">{{ $stats['present'] }}</h4>
            </div>
            <span class="material-symbols-outlined text-[24px] text-[#2E7D32] bg-[#E8F5E9] p-2 rounded-full">check_circle</span>
        </div>

        <!-- Terlambat -->
        <div class="card-layer-1 rounded-xl p-4 flex items-center justify-between bg-surface-container-lowest border border-outline-variant">
            <div class="space-y-1">
                <span class="text-[11px] text-on-surface-variant font-medium">Terlambat</span>
                <h4 class="font-headline-sm text-headline-sm text-[#F57F17] font-bold">{{ $stats['late'] }}</h4>
            </div>
            <span class="material-symbols-outlined text-[24px] text-[#F57F17] bg-[#FFF8E1] p-2 rounded-full">warning</span>
        </div>

        <!-- Pulang Lebih Awal -->
        <div class="card-layer-1 rounded-xl p-4 flex items-center justify-between bg-surface-container-lowest border border-outline-variant">
            <div class="space-y-1">
                <span class="text-[11px] text-on-surface-variant font-medium">Pulang Lebih Awal</span>
                <h4 class="font-headline-sm text-headline-sm text-[#F57F17] font-bold">{{ $stats['pulang_awal'] }}</h4>
            </div>
            <span class="material-symbols-outlined text-[24px] text-[#F57F17] bg-[#FFF8E1] p-2 rounded-full">logout</span>
        </div>

        <!-- Izin -->
        <div class="card-layer-1 rounded-xl p-4 flex items-center justify-between bg-surface-container-lowest border border-outline-variant">
            <div class="space-y-1">
                <span class="text-[11px] text-on-surface-variant font-medium">Izin</span>
                <h4 class="font-headline-sm text-headline-sm text-blue-700 font-bold">{{ $stats['izin'] ?? 0 }}</h4>
            </div>
            <span class="material-symbols-outlined text-[24px] text-blue-700 bg-blue-50 p-2 rounded-full">event_busy</span>
        </div>

        <!-- Sakit -->
        <div class="card-layer-1 rounded-xl p-4 flex items-center justify-between bg-surface-container-lowest border border-outline-variant">
            <div class="space-y-1">
                <span class="text-[11px] text-on-surface-variant font-medium">Sakit</span>
                <h4 class="font-headline-sm text-headline-sm text-purple-700 font-bold">{{ $stats['sakit'] ?? 0 }}</h4>
            </div>
            <span class="material-symbols-outlined text-[24px] text-purple-700 bg-purple-50 p-2 rounded-full">medical_services</span>
        </div>
    </div>

    <!-- Presence Trend Chart (ChartJS) -->
    <div class="card-layer-1 rounded-xl p-6 mb-8">
        <h3 class="font-headline-sm text-headline-sm text-on-surface mb-6">Tren Kehadiran</h3>
        <div class="h-[280px] w-full">
            <canvas id="recapTrendChart"></canvas>
        </div>
    </div>

    <!-- Attendance Table Grid -->
    <div class="bg-surface-container-lowest border border-[#E6ECE7] rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-[#F7FAF7] border-b border-[#E6ECE7]">
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Tanggal</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Nama Guru</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Jam Masuk</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Status Masuk</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Reward</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Jam Pulang</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Status Pulang</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Metode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E6ECE7] bg-white text-body-sm">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-[#F7FAF7] transition-colors">
                            <td class="py-4 px-6 font-medium text-on-surface">
                                {{ \Carbon\Carbon::parse($att->date)->isoFormat('dddd, D MMM YYYY') }}
                            </td>
                            <td class="py-4 px-6 font-semibold">
                                <div class="text-on-surface">{{ $att->teacher ? $att->teacher->name : 'Guru Dihapus / Tidak Ditemukan' }}</div>
                                <div class="text-[10px] text-outline">{{ $att->teacher ? $att->teacher->display_id : '-' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                {{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '-' }}
                            </td>
                            <td class="py-4 px-6">
                                @if($att->clock_in)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $att->status_masuk === 'Terlambat' ? 'bg-[#FFEBEE] text-[#D32F2F]' : 'bg-[#E8F5E9] text-[#2E7D32]' }}">
                                        {{ $att->status_masuk ?: 'Tepat Waktu' }}
                                    </span>
                                @elseif(in_array($att->status, ['izin', 'sakit', 'alpa']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-[#FFEBEE] text-[#D32F2F]">
                                        {{ ucfirst($att->status) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($att->reward)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-[#E8F5E9] text-[#2E7D32] border border-[#C8E6C9]">
                                        🏆 Reward
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                {{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '-' }}
                            </td>
                            <td class="py-4 px-6">
                                @if($att->clock_out)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ in_array($att->status_pulang, ['Pulang Awal', 'Pulang Lebih Awal']) ? 'bg-[#FFF8E1] text-[#F57F17]' : 'bg-[#E8F5E9] text-[#2E7D32]' }}">
                                        {{ $att->status_pulang ?: 'Normal' }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-4 px-6 font-medium text-on-surface">
                                <div class="flex flex-col gap-1">
                                    <span>{{ $att->check_in_method }}</span>
                                    @php
                                        $inLog = $att->logs->where('type', 'clock_in')->where('log_status', 'accepted')->first();
                                        $outLog = $att->logs->where('type', 'clock_out')->where('log_status', 'accepted')->first();
                                    @endphp
                                    @if(($inLog && $inLog->selfie_path) || ($outLog && $outLog->selfie_path))
                                        <div class="flex gap-2 mt-1">
                                            @if($inLog && $inLog->selfie_path)
                                                <a href="{{ route('admin.selfies.show', ['filename' => basename($inLog->selfie_path)]) }}" target="_blank" class="inline-flex items-center gap-0.5 text-[10px] text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-1.5 py-0.5 rounded font-semibold transition-colors" title="Lihat Foto Masuk">
                                                    <span class="material-symbols-outlined text-[12px] font-bold">photo_camera</span>Masuk
                                                </a>
                                            @endif
                                            @if($outLog && $outLog->selfie_path)
                                                <a href="{{ route('admin.selfies.show', ['filename' => basename($outLog->selfie_path)]) }}" target="_blank" class="inline-flex items-center gap-0.5 text-[10px] text-orange-700 hover:text-orange-900 bg-orange-50 hover:bg-orange-100 border border-orange-200 px-1.5 py-0.5 rounded font-semibold transition-colors" title="Lihat Foto Pulang">
                                                    <span class="material-symbols-outlined text-[12px] font-bold">photo_camera</span>Pulang
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 px-6 text-center text-on-surface-variant">
                                Tidak ada data rekaman kehadiran pada filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="p-4 border-t border-[#E6ECE7] bg-white flex items-center justify-between text-sm">
            <span class="font-body-sm text-on-surface-variant">
                Menampilkan {{ $attendances->firstItem() ?? 0 }} sampai {{ $attendances->lastItem() ?? 0 }} dari {{ $attendances->total() }} entries
            </span>
            <div>
                {{ $attendances->appends(request()->all())->links() }}
            </div>
        </div>
    </div>

    <!-- Toggle filters javascript & ChartJS integration -->
    <script>
        function togglePeriodFields() {
            const selectedType = document.getElementById('report-type').value;
            
            // Hide all period subfields
            document.querySelectorAll('.period-field').forEach(field => {
                field.classList.add('hidden');
            });

            // Show appropriate fields
            if (selectedType === 'harian') {
                document.getElementById('field-harian').classList.remove('hidden');
            } else if (selectedType === 'mingguan') {
                document.getElementById('field-mingguan-start').classList.remove('hidden');
                document.getElementById('field-mingguan-end').classList.remove('hidden');
            } else if (selectedType === 'bulanan') {
                document.getElementById('field-bulanan-month').classList.remove('hidden');
                document.getElementById('field-tahunan-year').classList.remove('hidden'); // also needs year
            } else if (selectedType === 'tahunan') {
                document.getElementById('field-tahunan-year').classList.remove('hidden');
            }
        }

        // Initialize toggle layout on DOM Ready
        document.addEventListener('DOMContentLoaded', () => {
            togglePeriodFields();

            // Render ChartJS Line Chart for recap trend
            const ctx = document.getElementById('recapTrendChart').getContext('2d');
            const chartData = @json($chartData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Tepat Waktu',
                            data: chartData.present,
                            borderColor: '#2E7D32',
                            backgroundColor: 'rgba(46, 125, 50, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Terlambat',
                            data: chartData.late,
                            borderColor: '#F57F17',
                            backgroundColor: 'rgba(245, 127, 23, 0.1)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
</x-layouts.admin>
