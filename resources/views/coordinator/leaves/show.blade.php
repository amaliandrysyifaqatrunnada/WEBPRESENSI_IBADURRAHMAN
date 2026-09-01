<x-layouts.coordinator>
    <x-slot:title>Detail & Approval Izin - Koordinator {{ $leaveRequest->unit ? $leaveRequest->unit->name : '' }}</x-slot:title>

    <div class="flex flex-col gap-6 max-w-5xl mx-auto">
        <!-- Header Banner Card -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-800 text-[11px] font-extrabold rounded-full mb-2 border border-emerald-200/60">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>KOORDINATOR PAKET: {{ $leaveRequest->unit ? strtoupper($leaveRequest->unit->name) : '-' }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600">assignment</span>
                    Detail & Tindakan Pengajuan Izin
                </h1>
                <p class="text-xs text-slate-500 mt-1">Periksa informasi pengajuan izin dan tentukan persetujuan Koordinator</p>
            </div>
            
            <a href="{{ route('coordinator.leaves.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-extrabold text-xs rounded-xl hover:bg-slate-200 transition-colors flex items-center gap-1.5 shadow-2xs">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Kembali ke Daftar</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Info Card (2 Columns Wide) -->
            <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col gap-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h2 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600">badge</span>
                        Informasi Pengajuan Izin
                    </h2>
                    
                    @if($leaveRequest->status === 'MENUNGGU_PERSETUJUAN_KOORDINATOR')
                        <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                            Menunggu Persetujuan Anda
                        </span>
                    @elseif(in_array($leaveRequest->status, ['DISETUJUI_KOORDINATOR', 'MENUNGGU_PERSETUJUAN_ADMIN']))
                        <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-800 border border-blue-200">
                            Disetujui Koordinator (Menunggu Admin)
                        </span>
                    @elseif($leaveRequest->status === 'DISETUJUI')
                        <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            Disetujui Final
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-rose-50 text-rose-800 border border-rose-200">
                            {{ str_replace('_', ' ', $leaveRequest->status) }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-1">Nama Tenaga Pendidik</div>
                        <div class="font-extrabold text-sm text-slate-900">{{ $leaveRequest->teacher ? $leaveRequest->teacher->name : '-' }}</div>
                        <div class="text-[11px] text-slate-500 font-semibold mt-0.5">NIP: {{ $leaveRequest->teacher ? ($leaveRequest->teacher->nip ?? '-') : '-' }}</div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-1">Unit / Paket</div>
                        <div class="font-extrabold text-sm text-emerald-700">{{ $leaveRequest->unit ? $leaveRequest->unit->name : '-' }}</div>
                        <div class="text-[11px] text-slate-500 font-semibold mt-0.5">Paket: {{ $leaveRequest->unit ? $leaveRequest->unit->package_type : '-' }}</div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-1">Jenis Ketidakhadiran</div>
                        <div class="font-extrabold text-sm text-slate-900 uppercase">
                            @if($leaveRequest->type === 'sakit')
                                <span class="text-purple-700 font-extrabold">Sakit</span>
                            @else
                                <span class="text-emerald-700 font-extrabold">Izin Berketerangan</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-1">Periode Tanggal Izin</div>
                        <div class="font-extrabold text-sm text-slate-900">
                            {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M Y') }}
                            @if($leaveRequest->start_date !== $leaveRequest->end_date)
                                s.d {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M Y') }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="text-xs">
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10.5px] mb-1.5">Keterangan / Alasan Pengajuan</div>
                    <div class="p-4 bg-slate-50 rounded-2xl text-slate-800 font-semibold border border-slate-200/60 leading-relaxed">
                        {{ $leaveRequest->description }}
                    </div>
                </div>

                @if($leaveRequest->attachment_path)
                    <div class="text-xs">
                        <div class="text-slate-400 font-bold uppercase tracking-wider text-[10.5px] mb-1.5">Berkas Lampiran Dokumen</div>
                        <a href="{{ route('coordinator.leaves.attachment', $leaveRequest->id) }}" target="_blank" class="p-4 bg-blue-50/80 hover:bg-blue-100/80 text-blue-900 font-extrabold rounded-2xl transition-all flex items-center justify-between border border-blue-200/80 group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-lg">attach_file</span>
                                </div>
                                <div>
                                    <div class="text-xs font-extrabold">Buka Lampiran Izin (Privat)</div>
                                    <div class="text-[10.5px] text-blue-700 font-semibold">Tersimpan aman pada storage privat server</div>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-blue-700 group-hover:translate-x-1 transition-transform">open_in_new</span>
                        </a>
                    </div>
                @endif

                <!-- Coordinator Action Form (Setujui & Tolak Side-by-Side) -->
                @if($leaveRequest->status === 'MENUNGGU_PERSETUJUAN_KOORDINATOR')
                    <div class="border-t border-slate-100 pt-6 mt-2 flex flex-col gap-4">
                        <div class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">how_to_reg</span>
                            Tindakan Persetujuan Koordinator
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3">
                            <form action="{{ route('coordinator.leaves.approve', $leaveRequest->id) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="note" value="Disetujui oleh Koordinator Paket">
                                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan izin ini?');" class="w-full bg-emerald-600 text-white font-extrabold py-3.5 px-5 rounded-2xl hover:bg-emerald-700 transition-all flex items-center justify-center gap-2 shadow-md shadow-emerald-700/20 active:scale-95 text-xs">
                                    <span class="material-symbols-outlined text-lg">check_circle</span>
                                    <span>[ SETUJUI ] KOORDINATOR</span>
                                </button>
                            </form>

                            <button type="button" onclick="document.getElementById('reject-modal').classList.remove('hidden')" class="flex-1 bg-rose-600 text-white font-extrabold py-3.5 px-5 rounded-2xl hover:bg-rose-700 transition-all flex items-center justify-center gap-2 shadow-md shadow-rose-700/20 active:scale-95 text-xs">
                                <span class="material-symbols-outlined text-lg">cancel</span>
                                <span>[ TOLAK ] KOORDINATOR</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-slate-100 rounded-2xl text-xs font-extrabold text-slate-600 text-center border border-slate-200">
                        Pengajuan ini telah diproses dengan status: <span class="text-emerald-700 uppercase">{{ str_replace('_', ' ', $leaveRequest->status) }}</span>
                    </div>
                @endif
            </div>

            <!-- Right Timeline & Audit Trail (1 Column Wide) -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col gap-4">
                <h2 class="font-extrabold text-base text-slate-900 border-b border-slate-100 pb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600">history</span>
                    Riwayat Workflow & Audit Trail
                </h2>

                <div class="flex flex-col gap-4 text-xs mt-2">
                    @forelse($leaveRequest->histories as $h)
                        <div class="flex items-start gap-3 relative pb-4 border-l-2 border-emerald-500/40 pl-4 last:border-0 last:pb-0">
                            <div class="w-3.5 h-3.5 rounded-full bg-emerald-600 border-2 border-white absolute -left-[8px] top-0.5 shadow-2xs"></div>
                            <div class="flex flex-col gap-1">
                                <div class="font-extrabold text-slate-900 text-xs">{{ $h->actor_name }}</div>
                                <div class="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-700 uppercase">
                                    <span>{{ $h->actor_role }}</span> &bull; <span>{{ $h->action }}</span>
                                </div>
                                <div class="text-[11.5px] text-slate-600 font-medium bg-slate-50 p-2.5 rounded-xl border border-slate-100 mt-1">
                                    {{ $h->note }}
                                </div>
                                <div class="text-[10px] font-bold text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($h->created_at)->format('d M Y, H:i') }} WIB
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-slate-400 text-center py-6 font-semibold">Belum ada catatan riwayat approval.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="reject-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl flex flex-col gap-5 border border-slate-100">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-lg text-rose-600 flex items-center gap-2">
                    <span class="material-symbols-outlined">cancel</span>
                    Tolak Pengajuan Izin
                </h3>
                <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <p class="text-xs text-slate-600 font-medium leading-relaxed">
                Berikan alasan / catatan penolakan secara jelas untuk disampaikan kepada tenaga pendidik.
            </p>
            
            <form action="{{ route('coordinator.leaves.reject', $leaveRequest->id) }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <div>
                    <label class="font-extrabold text-xs text-slate-800 mb-1.5 block">Catatan Penolakan (Wajib)</label>
                    <textarea name="note" required rows="3" placeholder="Masukkan alasan penolakan..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-xs font-semibold outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition-all"></textarea>
                </div>

                <div class="flex justify-end gap-2.5 pt-2">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-extrabold text-xs rounded-xl hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 text-white font-extrabold text-xs rounded-xl hover:bg-rose-700 transition-all shadow-md shadow-rose-600/20">
                        Kirim Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.coordinator>
