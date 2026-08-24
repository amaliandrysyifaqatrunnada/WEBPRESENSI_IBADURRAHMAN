<x-layouts.admin>
    <x-slot:title>Rekap Presensi Semua Unit - PKBM IBADURRAHMAN</x-slot:title>

    <!-- Header & Filter Form -->
    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Rekap Presensi Semua Unit</h2>
        <p class="text-sm text-on-surface-variant mt-1">Laporan dan riwayat kehadiran guru lintas unit pendidikan.</p>
    </div>

    <!-- Filter Card -->
    <div class="card-layer-1 rounded-xl p-6 mb-8 bg-white">
        <form action="{{ route('admin.superadmin.recap') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Filter Unit -->
            <div class="flex flex-col">
                <label for="unit_id" class="text-xs font-bold text-on-surface-variant mb-1 uppercase">Unit</label>
                <select name="unit_id" id="unit_id" class="bg-white border border-outline-variant rounded-xl py-2 px-3 text-sm focus:ring-primary focus:border-primary">
                    <option value="All" {{ $selectedUnitId === 'All' ? 'selected' : '' }}>Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ (string)$selectedUnitId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div class="flex flex-col">
                <label for="status" class="text-xs font-bold text-on-surface-variant mb-1 uppercase">Status Kehadiran</label>
                <select name="status" id="status" class="bg-white border border-outline-variant rounded-xl py-2 px-3 text-sm focus:ring-primary focus:border-primary">
                    <option value="All" {{ $selectedStatus === 'All' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Hadir" {{ $selectedStatus === 'Hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="Terlambat" {{ $selectedStatus === 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="Pulang Awal" {{ $selectedStatus === 'Pulang Awal' ? 'selected' : '' }}>Pulang Awal</option>
                    <option value="Izin" {{ $selectedStatus === 'Izin' ? 'selected' : '' }}>Izin</option>
                    <option value="Sakit" {{ $selectedStatus === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="Alpa" {{ $selectedStatus === 'Alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>

            <!-- Filter Metode -->
            <div class="flex flex-col">
                <label for="method" class="text-xs font-bold text-on-surface-variant mb-1 uppercase">Metode Presensi</label>
                <select name="method" id="method" class="bg-white border border-outline-variant rounded-xl py-2 px-3 text-sm focus:ring-primary focus:border-primary">
                    <option value="All" {{ $selectedMethod === 'All' ? 'selected' : '' }}>Semua Metode</option>
                    <option value="GPS" {{ $selectedMethod === 'GPS' ? 'selected' : '' }}>GPS Geofence</option>
                    <option value="QR" {{ $selectedMethod === 'QR' ? 'selected' : '' }}>Scan QR Code</option>
                    <option value="Face ID" {{ $selectedMethod === 'Face ID' ? 'selected' : '' }}>Face ID (Selfie)</option>
                    <option value="Manual" {{ $selectedMethod === 'Manual' ? 'selected' : '' }}>Manual (Admin)</option>
                </select>
            </div>

            <!-- Rentang Tanggal: Start -->
            <div class="flex flex-col">
                <label for="start_date" class="text-xs font-bold text-on-surface-variant mb-1 uppercase">Mulai Tanggal</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="bg-white border border-outline-variant rounded-xl py-2 px-3 text-sm focus:ring-primary focus:border-primary"/>
            </div>

            <!-- Rentang Tanggal: End -->
            <div class="flex flex-col">
                <label for="end_date" class="text-xs font-bold text-on-surface-variant mb-1 uppercase">Hingga Tanggal</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="bg-white border border-outline-variant rounded-xl py-2 px-3 text-sm focus:ring-primary focus:border-primary"/>
            </div>

            <!-- Search Nama / NIP -->
            <div class="flex flex-col lg:col-span-3">
                <label for="search_name" class="text-xs font-bold text-on-surface-variant mb-1 uppercase">Nama / NIP Guru</label>
                <input type="text" name="search_name" id="search_name" value="{{ $searchName }}" placeholder="Masukkan nama atau NIP guru..." class="bg-white border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:ring-primary focus:border-primary"/>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2 lg:col-span-2">
                <button type="submit" class="flex-1 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/95 transition-all shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">filter_alt</span>
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.superadmin.recap') }}" class="py-2.5 px-4 border border-outline-variant rounded-xl text-sm font-semibold hover:bg-surface-container-low transition-colors flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Actions & Table Card -->
    <div class="card-layer-1 rounded-xl p-6 bg-white">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 border-b border-outline-variant/30 pb-4">
            <h4 class="text-sm font-bold text-on-surface uppercase tracking-wider">Hasil Pencarian ({{ $attendances->total() }} Data ditemukan)</h4>
            
            <div class="flex items-center gap-2">
                <!-- Export Excel -->
                <a href="{{ route('admin.superadmin.export.excel', request()->all()) }}" class="py-2 px-4 bg-emerald-700 text-white rounded-xl text-xs font-bold hover:bg-emerald-800 transition-colors flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-xs">download</span>
                    Export Excel
                </a>

                <!-- Export PDF -->
                <a href="{{ route('admin.superadmin.export.pdf', request()->all()) }}" class="py-2 px-4 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition-colors flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-xs">picture_as_pdf</span>
                    Export PDF
                </a>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant text-xs text-outline uppercase font-semibold">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Nama Guru / NIP</th>
                        <th class="py-3 px-4">Unit</th>
                        <th class="py-3 px-4">Jam Masuk</th>
                        <th class="py-3 px-4">Status Masuk</th>
                        <th class="py-3 px-4">Jam Pulang</th>
                        <th class="py-3 px-4">Status Pulang</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4 text-center">Selfie (Face ID)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30 text-sm">
                    @forelse($attendances as $att)
                        @php
                            $inLog = $att->logs->where('type', 'clock_in')->first();
                            $outLog = $att->logs->where('type', 'clock_out')->first();
                        @endphp
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-3.5 px-4 font-medium text-on-surface whitespace-nowrap">
                                {{ Carbon\Carbon::parse($att->date)->isoFormat('DD MMM YYYY') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-on-surface block">{{ $att->teacher->name }}</span>
                                <span class="text-xs text-on-surface-variant block mt-0.5">NIP: {{ $att->teacher->nip ?: '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-on-surface-variant">
                                {{ $att->unit ? $att->unit->name : '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-on-surface whitespace-nowrap">
                                {{ $att->clock_in ? Carbon\Carbon::parse($att->clock_in)->format('H:i') : '-' }} WIB
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($att->status === 'hadir')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">Tepat Waktu</span>
                                @elseif($att->status === 'terlambat')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-600 border border-orange-100">Terlambat</span>
                                @elseif($att->status === 'izin')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">Izin</span>
                                @elseif($att->status === 'sakit')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100">Sakit</span>
                                @elseif($att->status === 'alpa')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">Alpa</span>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-on-surface whitespace-nowrap">
                                {{ $att->clock_out ? Carbon\Carbon::parse($att->clock_out)->format('H:i') : '-' }} WIB
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($att->status_pulang === 'Normal')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">Normal</span>
                                @elseif($att->status_pulang === 'Pulang Awal')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">Pulang Awal</span>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-on-surface-variant font-medium">
                                @php
                                    $methods = [];
                                    if ($inLog) $methods[] = 'Masuk: ' . strtoupper($inLog->method);
                                    if ($outLog) $methods[] = 'Pulang: ' . strtoupper($outLog->method);
                                @endphp
                                {!! count($methods) > 0 ? implode('<br/>', $methods) : '-' !!}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if(($inLog && $inLog->selfie_path) || ($outLog && $outLog->selfie_path))
                                    @php
                                        $selfieLog = ($inLog && $inLog->selfie_path) ? $inLog : $outLog;
                                    @endphp
                                    <a href="{{ route('admin.selfies.show', ['filename' => basename($selfieLog->selfie_path)]) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-primary font-bold hover:underline">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                        Lihat Foto
                                    </a>
                                @else
                                    <span class="text-xs text-outline">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl text-outline mb-2">info</span>
                                <p class="text-sm font-medium">Tidak ada data kehadiran yang sesuai dengan filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 border-t border-outline-variant/30 pt-4">
            {{ $attendances->links() }}
        </div>
    </div>
</x-layouts.admin>
