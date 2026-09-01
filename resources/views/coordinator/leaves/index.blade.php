<x-layouts.coordinator>
    <x-slot:title>Persetujuan Perizinan - Koordinator {{ $unit ? $unit->name : 'Paket' }}</x-slot:title>

    <div class="flex flex-col gap-6 max-w-6xl mx-auto">
        <!-- Page Title Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-800 text-[11px] font-extrabold rounded-full mb-2 border border-emerald-200/60">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>KOORDINATOR PAKET: {{ $unit ? strtoupper($unit->name) : '-' }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600">assignment_turned_in</span>
                    Persetujuan Perizinan Unit
                </h1>
                <p class="text-xs text-slate-500 mt-1">Kelola dan periksa pengajuan izin tenaga pendidik khusus {{ $unit ? $unit->name : '-' }}</p>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('coordinator.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs rounded-xl transition-all inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">dashboard</span>
                    <span>Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar Card -->
        <form method="GET" action="{{ route('coordinator.leaves.index') }}" class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="font-extrabold text-slate-600 mb-1.5 block uppercase tracking-wider text-[10.5px]">Status Pengajuan</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none" onchange="this.form.submit()">
                    <option value="All" {{ $selectedStatus === 'All' ? 'selected' : '' }}>Semua Status</option>
                    <option value="MENUNGGU_PERSETUJUAN_KOORDINATOR" {{ $selectedStatus === 'MENUNGGU_PERSETUJUAN_KOORDINATOR' ? 'selected' : '' }}>Menunggu Koordinator</option>
                    <option value="DISETUJUI_KOORDINATOR" {{ $selectedStatus === 'DISETUJUI_KOORDINATOR' ? 'selected' : '' }}>Disetujui Koordinator</option>
                    <option value="DITOLAK_KOORDINATOR" {{ $selectedStatus === 'DITOLAK_KOORDINATOR' ? 'selected' : '' }}>Ditolak Koordinator</option>
                    <option value="DISETUJUI" {{ $selectedStatus === 'DISETUJUI' ? 'selected' : '' }}>Disetujui Final</option>
                </select>
            </div>

            <div>
                <label class="font-extrabold text-slate-600 mb-1.5 block uppercase tracking-wider text-[10.5px]">Jenis Ketidakhadiran</label>
                <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none" onchange="this.form.submit()">
                    <option value="All" {{ $selectedType === 'All' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="izin" {{ $selectedType === 'izin' ? 'selected' : '' }}>Izin Berketerangan</option>
                    <option value="sakit" {{ $selectedType === 'sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>

            <div>
                <label class="font-extrabold text-slate-600 mb-1.5 block uppercase tracking-wider text-[10.5px]">Cari Nama Guru</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIP..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 font-bold text-slate-800 placeholder:font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none"/>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-emerald-600 text-white font-extrabold py-2.5 px-4 rounded-xl hover:bg-emerald-700 transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                    <span class="material-symbols-outlined text-base">filter_alt</span>
                    <span>Filter</span>
                </button>
                <a href="{{ route('coordinator.leaves.index') }}" class="bg-slate-100 text-slate-700 font-extrabold py-2.5 px-4 rounded-xl hover:bg-slate-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>

        <!-- Leave Requests Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-600 font-extrabold border-b border-slate-200/80 uppercase tracking-wider text-[10.5px]">
                        <tr>
                            <th class="py-4 px-4">No</th>
                            <th class="py-4 px-4">Nama Guru</th>
                            <th class="py-4 px-4">Jenis</th>
                            <th class="py-4 px-4">Periode Tanggal</th>
                            <th class="py-4 px-4">Keterangan</th>
                            <th class="py-4 px-4">Lampiran Dokumen</th>
                            <th class="py-4 px-4">Status Workflow</th>
                            <th class="py-4 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($leaveRequests as $idx => $req)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-900">{{ $leaveRequests->firstItem() + $idx }}</td>
                                <td class="py-4 px-4">
                                    <div class="font-extrabold text-slate-900 text-xs">{{ $req->teacher ? $req->teacher->name : '-' }}</div>
                                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5">NIP: {{ $req->teacher ? ($req->teacher->nip ?? '-') : '-' }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if($req->type === 'sakit')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-extrabold bg-purple-50 text-purple-700 border border-purple-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            Sakit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Izin Berketerangan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap font-bold text-slate-800">
                                    {{ \Carbon\Carbon::parse($req->start_date)->format('d/m/Y') }}
                                    @if($req->start_date !== $req->end_date)
                                        <br><span class="text-[10.5px] text-slate-400 font-normal">s.d {{ \Carbon\Carbon::parse($req->end_date)->format('d/m/Y') }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 max-w-[200px]">
                                    <div class="truncate" title="{{ $req->description }}">{{ $req->description }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if($req->attachment_path)
                                        <a href="{{ route('coordinator.leaves.attachment', $req->id) }}" target="_blank" class="px-3 py-1 bg-blue-50 text-blue-700 font-extrabold rounded-lg hover:bg-blue-100 transition-colors inline-flex items-center gap-1 text-[11px] border border-blue-200/60">
                                            <span class="material-symbols-outlined text-xs">attach_file</span>
                                            <span>Buka Berkas</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-[11px] font-semibold">- Tidak ada -</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if($req->status === 'MENUNGGU_PERSETUJUAN_KOORDINATOR')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10.5px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                            Menunggu Persetujuan Anda
                                        </span>
                                    @elseif(in_array($req->status, ['DISETUJUI_KOORDINATOR', 'MENUNGGU_PERSETUJUAN_ADMIN']))
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10.5px] font-extrabold bg-blue-50 text-blue-800 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Disetujui Koordinator (Antrean Admin)
                                        </span>
                                    @elseif($req->status === 'DISETUJUI')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10.5px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Disetujui Final
                                        </span>
                                    @elseif($req->status === 'DITOLAK_KOORDINATOR')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10.5px] font-extrabold bg-rose-50 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Ditolak Koordinator
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10.5px] font-extrabold bg-rose-50 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            {{ str_replace('_', ' ', $req->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('coordinator.leaves.show', $req->id) }}" class="px-3.5 py-1.5 bg-emerald-600 text-white font-extrabold text-[11px] rounded-xl hover:bg-emerald-700 transition-all shadow-xs inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">visibility</span>
                                        <span>Periksa</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 font-semibold">Tidak ada pengajuan izin ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $leaveRequests->links() }}
            </div>
        </div>
    </div>
</x-layouts.coordinator>
