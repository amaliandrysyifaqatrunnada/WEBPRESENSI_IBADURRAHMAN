<x-layouts.coordinator>
    <x-slot:title>Dashboard Koordinator - {{ $unit ? $unit->name : 'Paket' }}</x-slot:title>

    <div class="flex flex-col gap-6 max-w-6xl mx-auto">
        <!-- Hero Welcome Card -->
        <div class="relative overflow-hidden bg-gradient-to-r from-emerald-800 via-emerald-700 to-green-800 text-white rounded-3xl p-6 sm:p-8 shadow-lg shadow-emerald-900/10 border border-emerald-600/30">
            <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-white/5 skew-x-12 pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full text-[11px] font-extrabold text-emerald-200 backdrop-blur-md mb-3 border border-white/10">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>PORTAL PERSETUJUAN IZIN UNIT</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Selamat Datang, {{ $user->name }}</h1>
                    <p class="text-xs sm:text-sm text-emerald-100/90 mt-1 max-w-xl">
                        Koordinator Penanggung Jawab {{ $unit ? $unit->name : 'Unit' }} — PKBM Ibadurrahman Sidoarjo
                    </p>
                </div>
                
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('coordinator.leaves.index') }}" class="px-5 py-3 bg-white text-emerald-900 font-extrabold text-xs rounded-2xl hover:bg-emerald-50 transition-all duration-200 flex items-center gap-2 shadow-md shadow-black/10 active:scale-95">
                        <span class="material-symbols-outlined text-lg">assignment_turned_in</span>
                        <span>Buka Persetujuan Izin</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bento Grid Statistics Widgets -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pending Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-extrabold text-amber-700 uppercase tracking-wider mb-1">Menunggu Koordinator</div>
                    <div class="text-3xl font-extrabold text-slate-900">{{ $stats['pending_koordinator'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-1 font-semibold">Butuh persetujuan Anda</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">pending_actions</span>
                </div>
            </div>

            <!-- Approved Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-extrabold text-emerald-700 uppercase tracking-wider mb-1">Disetujui Koordinator</div>
                    <div class="text-3xl font-extrabold text-slate-900">{{ $stats['approved_koordinator'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-1 font-semibold">Telah disetujui & diteruskan</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">task_alt</span>
                </div>
            </div>

            <!-- Rejected Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-extrabold text-rose-700 uppercase tracking-wider mb-1">Ditolak Koordinator</div>
                    <div class="text-3xl font-extrabold text-slate-900">{{ $stats['rejected_koordinator'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-1 font-semibold">Pengajuan ditolak</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">cancel</span>
                </div>
            </div>

            <!-- Teachers Count Card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-extrabold text-indigo-700 uppercase tracking-wider mb-1">Guru {{ $unit ? $unit->name : '' }}</div>
                    <div class="text-3xl font-extrabold text-slate-900">{{ $teacherCount }}</div>
                    <div class="text-[10px] text-slate-400 mt-1 font-semibold">Total pendidik di unit</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">group</span>
                </div>
            </div>
        </div>

        <!-- Quick Access Table Section -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600">assignment_turned_in</span>
                        Daftar Pengajuan Izin Terbaru ({{ $unit ? $unit->name : '-' }})
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pengajuan izin terbaru dari tenaga pendidik di unit Anda</p>
                </div>
                
                <a href="{{ route('coordinator.leaves.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-800 text-slate-700 font-extrabold text-xs rounded-xl transition-all duration-200 flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Lihat Semua Pengajuan</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-600 font-extrabold border-b border-slate-200/80 uppercase tracking-wider text-[10.5px]">
                        <tr>
                            <th class="py-3.5 px-4">Nama Guru</th>
                            <th class="py-3.5 px-4">Jenis Ketidakhadiran</th>
                            <th class="py-3.5 px-4">Periode Tanggal</th>
                            <th class="py-3.5 px-4">Keterangan</th>
                            <th class="py-3.5 px-4">Status Workflow</th>
                            <th class="py-3.5 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse($recentRequests as $req)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-slate-900 text-xs">{{ $req->teacher ? $req->teacher->name : '-' }}</div>
                                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5">NIP: {{ $req->teacher ? ($req->teacher->nip ?? '-') : '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
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
                                <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($req->start_date)->format('d/m/Y') }}
                                    @if($req->start_date !== $req->end_date)
                                        s.d {{ \Carbon\Carbon::parse($req->end_date)->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 truncate max-w-[200px]" title="{{ $req->description }}">
                                    {{ $req->description }}
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($req->status === 'MENUNGGU_PERSETUJUAN_KOORDINATOR')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10.5px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                            Menunggu Koordinator
                                        </span>
                                    @elseif(in_array($req->status, ['DISETUJUI_KOORDINATOR', 'MENUNGGU_PERSETUJUAN_ADMIN']))
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10.5px] font-extrabold bg-blue-50 text-blue-800 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Disetujui Koordinator
                                        </span>
                                    @elseif($req->status === 'DISETUJUI')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10.5px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Disetujui Final
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10.5px] font-extrabold bg-rose-50 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            {{ str_replace('_', ' ', $req->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('coordinator.leaves.show', $req->id) }}" class="px-3.5 py-1.5 bg-emerald-600 text-white font-extrabold text-[11px] rounded-xl hover:bg-emerald-700 transition-all shadow-xs inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">visibility</span>
                                        <span>Periksa</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 font-semibold">Belum ada pengajuan izin terbaru dari unit ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.coordinator>
