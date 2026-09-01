<x-layouts.auth>
    <x-slot:title>PKBM IBADURRAHMAN - Pengajuan & Riwayat Izin</x-slot:title>

    <div class="absolute inset-0 bg-pattern pointer-events-none"></div>

    <main class="w-full max-w-xl p-container-padding flex flex-col gap-6 relative z-10 mx-auto my-6">
        <!-- Header Card -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-lg p-6 flex flex-col gap-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>

            <div class="flex justify-between items-center pb-4 border-b border-outline-variant/30">
                <div class="flex items-center gap-3">
                    <img alt="Logo" class="h-10 w-10 object-contain" src="{{ asset('images/logo-pkbm-transparent.png') }}"/>
                    <div>
                        <h1 class="font-label-md text-label-md text-primary tracking-wider font-bold">PKBM IBADURRAHMAN</h1>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Izin & Ketidakhadiran</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('teacher.attendance') }}" class="px-3 py-1.5 bg-primary/10 text-primary text-xs font-bold rounded-lg hover:bg-primary/20 transition-all flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        Presensi
                    </a>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="bg-surface-container rounded-lg p-4 border border-outline-variant/30 text-xs text-on-surface-variant flex items-center justify-between">
                <div>
                    <div class="font-bold text-sm text-on-surface">{{ $teacher->name }}</div>
                    <div class="text-[11px] text-outline mt-0.5">{{ $teacher->display_id }} | Unit: {{ $teacher->unit ? $teacher->unit->name : '-' }}</div>
                </div>
            </div>

            <!-- Informational Banner for GPS-Free Leave -->
            <div class="p-3.5 bg-blue-50/80 border border-blue-200 rounded-xl text-xs text-blue-900 flex flex-col gap-1 shadow-xs">
                <div class="font-bold flex items-center gap-1.5 text-primary">
                    <span class="material-symbols-outlined text-base">location_off</span>
                    <span>Pengajuan Izin Tanpa GPS</span>
                </div>
                <p class="text-[11.5px] text-slate-700">Pengajuan izin dapat dilakukan dari mana saja, termasuk dari rumah. GPS tidak diperlukan untuk mengajukan izin.</p>
                <p class="text-[11px] text-amber-800 font-semibold italic">Pengajuan belum dianggap sebagai izin resmi sampai disetujui oleh pihak yang berwenang.</p>
            </div>

            @if(session('success'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs flex flex-col gap-1 shadow-xs">
                    <div class="font-bold flex items-center gap-1.5 text-red-700">
                        <span class="material-symbols-outlined text-base">error</span>
                        <span>Gagal Mengirim Pengajuan:</span>
                    </div>
                    <ul class="list-disc pl-4 space-y-0.5 text-[11px] text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Pengajuan Izin Baru -->
            <div class="bg-surface-container-low rounded-xl p-5 border border-outline-variant/30">
                <h2 class="text-base font-bold text-on-surface mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary">edit_note</span>
                    Form Pengajuan Izin Baru
                </h2>
                <form action="{{ route('teacher.leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Jenis Ketidakhadiran</label>
                        <select name="type" required class="w-full bg-white border border-outline-variant rounded-xl px-3 py-2 text-sm focus:ring-primary focus:border-primary">
                            <option value="izin">Izin Berketerangan</option>
                            <option value="sakit">Sakit</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Tanggal Mulai</label>
                            <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full bg-white border border-outline-variant rounded-xl px-3 py-2 text-sm focus:ring-primary focus:border-primary"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Tanggal Selesai</label>
                            <input type="date" name="end_date" required value="{{ date('Y-m-d') }}" class="w-full bg-white border border-outline-variant rounded-xl px-3 py-2 text-sm focus:ring-primary focus:border-primary"/>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Keterangan / Alasan</label>
                        <textarea name="description" required rows="3" placeholder="Jelaskan alasan izin / sakit..." class="w-full bg-white border border-outline-variant rounded-xl px-3 py-2 text-sm focus:ring-primary focus:border-primary"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Lampiran Dokumen (Opsional)</label>
                        <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-600 border border-outline-variant rounded-xl p-2 bg-white"/>
                        <span class="text-[10px] text-slate-400 mt-1 block">Format: PDF, JPG, PNG (Maksimal 5MB)</span>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90 transition-all flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-base">send</span>
                        Ajukan Sekarang
                    </button>
                </form>
            </div>

            <!-- Tabel Riwayat Pengajuan Izin -->
            <div class="mt-2">
                <h2 class="text-base font-bold text-on-surface mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary">history</span>
                    Riwayat Pengajuan Izin Saya
                </h2>

                <div class="space-y-3">
                    @forelse($leaveRequests as $req)
                        <div class="p-4 rounded-xl border border-outline-variant/30 bg-white shadow-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-primary">
                                    {{ \Carbon\Carbon::parse($req->start_date)->format('d/m/Y') }}
                                    @if($req->start_date != $req->end_date)
                                        - {{ \Carbon\Carbon::parse($req->end_date)->format('d/m/Y') }}
                                    @endif
                                </span>
                                @if($req->status === 'DISETUJUI')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800">✓ Disetujui (Izin Diterima)</span>
                                @elseif($req->status === 'MENUNGGU_PERSETUJUAN_KOORDINATOR')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">⏳ Menunggu Koordinator</span>
                                @elseif(in_array($req->status, ['DISETUJUI_KOORDINATOR', 'MENUNGGU_PERSETUJUAN_ADMIN']))
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">⏳ Disetujui Koordinator (Menunggu Admin)</span>
                                @elseif($req->status === 'DITOLAK_KOORDINATOR')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">✕ Ditolak Koordinator</span>
                                @elseif($req->status === 'DITOLAK_ADMIN')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">✕ Ditolak Admin</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">⏳ {{ str_replace('_', ' ', $req->status) }}</span>
                                @endif
                            </div>

                            <div class="text-xs text-on-surface">
                                <span class="font-bold uppercase text-slate-600">Jenis: {{ $req->type }}</span>
                                <p class="mt-1 text-slate-600">{{ $req->description }}</p>
                            </div>

                            @if($req->attachment_path)
                                <div class="pt-1">
                                    <a href="{{ route('teacher.leaves.attachment', $req) }}" target="_blank" class="text-xs text-primary font-bold hover:underline inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">attach_file</span>
                                        Lihat Dokumen Lampiran
                                    </a>
                                </div>
                            @endif

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                <span>Diajukan: {{ $req->created_at->format('d/m/Y H:i') }}</span>
                                <button onclick='showHistories(@json($req->histories))' class="text-primary font-bold hover:underline">
                                    Histori Process
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-500 bg-surface-container rounded-xl border border-outline-variant/30">
                            Belum ada riwayat pengajuan izin.
                        </div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $leaveRequests->links() }}
                </div>
            </div>
        </div>
    </main>

    <!-- History Process Modal -->
    <div id="historyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-outline-variant mx-4">
            <h3 class="text-lg font-bold text-on-surface mb-3">Histori Persetujuan</h3>
            <div id="historyList" class="space-y-3 max-h-[300px] overflow-y-auto pr-1"></div>
            <div class="flex justify-end pt-3 border-t border-outline-variant/30 mt-3">
                <button type="button" onclick="closeHistoryModal()" class="px-4 py-1.5 bg-slate-200 text-slate-800 text-xs font-bold rounded-xl">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function showHistories(histories) {
            const list = document.getElementById('historyList');
            list.innerHTML = '';
            if (!histories || histories.length === 0) {
                list.innerHTML = '<p class="text-xs text-slate-400">Belum ada catatan histori.</p>';
            } else {
                histories.forEach(h => {
                    const dateStr = new Date(h.created_at).toLocaleString('id-ID');
                    const item = document.createElement('div');
                    item.className = 'p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs';
                    item.innerHTML = `
                        <div class="flex justify-between font-bold text-slate-700">
                            <span>${h.actor_name || h.actor_role}</span>
                            <span class="text-[10px] font-normal text-slate-400">${dateStr}</span>
                        </div>
                        <div class="text-primary font-semibold mt-1">Status/Aksi: ${h.action}</div>
                        <div class="text-slate-600 mt-0.5">Catatan: ${h.note || '-'}</div>
                    `;
                    list.appendChild(item);
                });
            }
            document.getElementById('historyModal').classList.remove('hidden');
        }
        function closeHistoryModal() {
            document.getElementById('historyModal').classList.add('hidden');
        }
    </script>
</x-layouts.auth>
