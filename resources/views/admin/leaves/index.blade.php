<x-layouts.admin>
    <x-slot:title>Persetujuan Izin / Ketidakhadiran - PKBM IBADURRAHMAN</x-slot:title>

    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Persetujuan Izin & Ketidakhadiran</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola workflow persetujuan pengajuan izin, sakit, dan ketidakhadiran tenaga pendidik.</p>
    </div>

    <!-- Statistics Bento Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-gutter mb-8">
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between">
            <span class="text-[10px] font-bold text-blue-700 uppercase">Izin Berketerangan</span>
            <span class="text-2xl font-extrabold text-blue-700 mt-1">{{ $stats['total_izin'] }}</span>
        </div>
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between">
            <span class="text-[10px] font-bold text-purple-700 uppercase">Sakit</span>
            <span class="text-2xl font-extrabold text-purple-700 mt-1">{{ $stats['total_sakit'] }}</span>
        </div>
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between">
            <span class="text-[10px] font-bold text-red-700 uppercase">Tanpa Keterangan</span>
            <span class="text-2xl font-extrabold text-red-700 mt-1">{{ $stats['total_tanpa_keterangan'] }}</span>
        </div>
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between bg-orange-50/50 border-orange-200">
            <span class="text-[10px] font-bold text-orange-700 uppercase">Menunggu Atasan</span>
            <span class="text-2xl font-extrabold text-orange-700 mt-1">{{ $stats['pending_atasan'] }}</span>
        </div>
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between bg-amber-50/50 border-amber-200">
            <span class="text-[10px] font-bold text-amber-700 uppercase">Menunggu Admin</span>
            <span class="text-2xl font-extrabold text-amber-700 mt-1">{{ $stats['pending_admin'] }}</span>
        </div>
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between bg-emerald-50/50 border-emerald-200">
            <span class="text-[10px] font-bold text-emerald-700 uppercase">Disetujui</span>
            <span class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $stats['approved'] }}</span>
        </div>
        <div class="card-layer-1 rounded-xl p-4 flex flex-col justify-between bg-rose-50/50 border-rose-200">
            <span class="text-[10px] font-bold text-rose-700 uppercase">Ditolak</span>
            <span class="text-2xl font-extrabold text-rose-700 mt-1">{{ $stats['rejected'] }}</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card-layer-1 rounded-xl p-4 mb-6">
        <form action="{{ route('admin.leaves.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[180px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari Guru..." class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2 text-sm focus:ring-primary focus:border-primary" />
            </div>
            @if(auth()->user()->hasRole('superadmin'))
                <div>
                    <select name="unit_id" onchange="this.form.submit()" class="bg-white border border-outline-variant rounded-xl px-3 py-2 text-sm focus:ring-primary focus:border-primary">
                        <option value="All" {{ $selectedUnitId === 'All' ? 'selected' : '' }}>Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ (string)$selectedUnitId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <select name="type" onchange="this.form.submit()" class="bg-white border border-outline-variant rounded-xl px-3 py-2 text-sm focus:ring-primary focus:border-primary">
                    <option value="All" {{ $selectedType === 'All' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="izin" {{ $selectedType === 'izin' ? 'selected' : '' }}>Izin Berketerangan</option>
                    <option value="sakit" {{ $selectedType === 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="tanpa_keterangan" {{ $selectedType === 'tanpa_keterangan' ? 'selected' : '' }}>Tanpa Keterangan</option>
                </select>
            </div>
            <div>
                <select name="status" onchange="this.form.submit()" class="bg-white border border-outline-variant rounded-xl px-3 py-2 text-sm focus:ring-primary focus:border-primary">
                    <option value="All" {{ $selectedStatus === 'All' ? 'selected' : '' }}>Semua Status</option>
                    <option value="MENUNGGU_PERSETUJUAN_KOORDINATOR" {{ $selectedStatus === 'MENUNGGU_PERSETUJUAN_KOORDINATOR' ? 'selected' : '' }}>Menunggu Koordinator</option>
                    <option value="DISETUJUI_KOORDINATOR" {{ $selectedStatus === 'DISETUJUI_KOORDINATOR' ? 'selected' : '' }}>Disetujui Koordinator</option>
                    <option value="MENUNGGU_PERSETUJUAN_ADMIN" {{ $selectedStatus === 'MENUNGGU_PERSETUJUAN_ADMIN' ? 'selected' : '' }}>Menunggu Admin</option>
                    <option value="DISETUJUI" {{ $selectedStatus === 'DISETUJUI' ? 'selected' : '' }}>Disetujui Final</option>
                    <option value="DITOLAK_KOORDINATOR" {{ $selectedStatus === 'DITOLAK_KOORDINATOR' ? 'selected' : '' }}>Ditolak Koordinator</option>
                    <option value="DITOLAK_ADMIN" {{ $selectedStatus === 'DITOLAK_ADMIN' ? 'selected' : '' }}>Ditolak Admin</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-surface-container-high text-on-surface text-sm font-semibold rounded-xl hover:bg-surface-container-highest transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="card-layer-1 rounded-xl overflow-hidden shadow-sm border border-outline-variant/30">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant/30 text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        <th class="py-3.5 px-4">No</th>
                        <th class="py-3.5 px-4">Nama Guru</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4">Jenis</th>
                        <th class="py-3.5 px-4">Periode Tanggal</th>
                        <th class="py-3.5 px-4">Keterangan</th>
                        <th class="py-3.5 px-4">Lampiran</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20 text-sm">
                    @forelse($leaveRequests as $index => $req)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="py-3.5 px-4 font-medium">{{ $leaveRequests->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4 font-semibold text-on-surface">
                                {{ $req->teacher->name }}<br>
                                <span class="text-xs font-normal text-on-surface-variant">{{ $req->teacher->display_id }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-xs text-on-surface-variant">
                                {{ $req->unit ? $req->unit->name : '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($req->type === 'sakit')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">Sakit</span>
                                @elseif($req->type === 'izin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Izin</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">Tanpa Keterangan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-xs">
                                {{ \Carbon\Carbon::parse($req->start_date)->format('d/m/Y') }}
                                @if($req->start_date != $req->end_date)
                                    s.d {{ \Carbon\Carbon::parse($req->end_date)->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-on-surface-variant max-w-[200px] truncate" title="{{ $req->description }}">
                                {{ $req->description }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($req->attachment_path)
                                    <a href="{{ route('admin.leaves.attachment', $req) }}" target="_blank" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">attachment</span>
                                        Lihat File
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($req->status === 'DISETUJUI')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">✓ Disetujui Final</span>
                                @elseif($req->status === 'MENUNGGU_PERSETUJUAN_KOORDINATOR')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">⏳ Menunggu Koordinator</span>
                                @elseif(in_array($req->status, ['DISETUJUI_KOORDINATOR', 'MENUNGGU_PERSETUJUAN_ADMIN']))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">⏳ Disetujui Koordinator (Menunggu Admin)</span>
                                @elseif(in_array($req->status, ['DITOLAK_ATASAN', 'DITOLAK_KOORDINATOR', 'DITOLAK_ADMIN']))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">✕ {{ str_replace('_', ' ', $req->status) }}</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">⏳ {{ str_replace('_', ' ', $req->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick='showAuditTrail(@json($req->histories))' class="p-1.5 text-on-surface-variant hover:text-primary transition-colors" title="Lihat Histori Audit Trail">
                                        <span class="material-symbols-outlined text-lg">history</span>
                                    </button>
                                    @if(in_array($req->status, ['DISETUJUI_KOORDINATOR', 'MENUNGGU_PERSETUJUAN_ADMIN']) || (auth()->user()->hasRole('superadmin') && !in_array($req->status, ['DISETUJUI', 'DITOLAK_ADMIN'])))
                                        <button onclick="openApproveModal({{ $req->id }})" class="px-2.5 py-1 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-colors">
                                            Setujui
                                        </button>
                                        <button onclick="openRejectModal({{ $req->id }})" class="px-2.5 py-1 bg-rose-600 text-white text-xs font-bold rounded-lg hover:bg-rose-700 transition-colors">
                                            Tolak
                                        </button>
                                    @elseif($req->status === 'MENUNGGU_PERSETUJUAN_KOORDINATOR')
                                        <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200" title="Menunggu persetujuan dari Koordinator Paket terlebih dahulu">Menunggu Koordinator</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-on-surface-variant text-sm">Tidak ada pengajuan izin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-outline-variant/30">
            {{ $leaveRequests->links() }}
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-outline-variant">
            <h3 class="text-xl font-bold text-on-surface mb-4">Setujui Pengajuan Izin</h3>
            <form id="approveForm" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Catatan Persetujuan (Opsional)</label>
                    <textarea name="note" rows="3" placeholder="Disetujui oleh admin..." class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="button" onclick="closeApproveModal()" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700">Setujui Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-outline-variant">
            <h3 class="text-xl font-bold text-on-surface mb-4">Tolak Pengajuan Izin</h3>
            <form id="rejectForm" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Alasan Penolakan</label>
                    <textarea name="note" required rows="3" placeholder="Jelaskan alasan penolakan..." class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 text-white text-sm font-semibold rounded-xl hover:bg-rose-700">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Trail Modal -->
    <div id="auditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl border border-outline-variant">
            <h3 class="text-xl font-bold text-on-surface mb-4">Histori Audit Trail Persetujuan</h3>
            <div id="auditTimeline" class="space-y-3 max-h-[350px] overflow-y-auto pr-2">
                <!-- Timeline items injected via JS -->
            </div>
            <div class="flex justify-end pt-4 border-t border-outline-variant/30 mt-4">
                <button type="button" onclick="closeAuditModal()" class="px-5 py-2 bg-surface-container-high text-on-surface text-sm font-semibold rounded-xl">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function openApproveModal(id) {
            document.getElementById('approveForm').action = '/admin/leaves/' + id + '/approve';
            document.getElementById('approveModal').classList.remove('hidden');
        }
        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
        }
        function openRejectModal(id) {
            document.getElementById('rejectForm').action = '/admin/leaves/' + id + '/reject';
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
        function showAuditTrail(histories) {
            const container = document.getElementById('auditTimeline');
            container.innerHTML = '';

            if (!histories || histories.length === 0) {
                container.innerHTML = '<p class="text-xs text-slate-500">Belum ada riwayat persetujuan.</p>';
            } else {
                histories.forEach(h => {
                    const dateStr = new Date(h.created_at).toLocaleString('id-ID');
                    const div = document.createElement('div');
                    div.className = 'p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs';
                    div.innerHTML = `
                        <div class="flex items-center justify-between font-bold text-slate-700 mb-1">
                            <span>${h.actor_name || h.actor_role} (${h.actor_role})</span>
                            <span class="text-[10px] text-slate-400 font-normal">${dateStr}</span>
                        </div>
                        <div class="text-primary font-semibold mb-0.5">Aksi: ${h.action}</div>
                        <div class="text-slate-600">Catatan: ${h.note || '-'}</div>
                    `;
                    container.appendChild(div);
                });
            }
            document.getElementById('auditModal').classList.remove('hidden');
        }
        function closeAuditModal() {
            document.getElementById('auditModal').classList.add('hidden');
        }
    </script>
</x-layouts.admin>
