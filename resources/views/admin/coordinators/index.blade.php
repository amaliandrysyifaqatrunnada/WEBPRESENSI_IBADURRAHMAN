<x-layouts.admin>
    <x-slot:title>Manajemen Koordinator Paket - Superadmin</x-slot:title>

    <div class="flex flex-col gap-6 max-w-6xl mx-auto">
        <!-- Header Card -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-800 text-[11px] font-extrabold rounded-full mb-2 border border-emerald-200/60">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>PENGATURAN SUPERADMIN</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600">manage_accounts</span>
                    Manajemen Koordinator Paket
                </h1>
                <p class="text-xs text-slate-500 mt-1">Tetapkan penanggung jawab Koordinator untuk masing-masing unit/paket (TK, Paket A, Paket B, Paket C)</p>
            </div>
            
            <button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="px-5 py-3 bg-emerald-600 text-white font-extrabold text-xs rounded-2xl hover:bg-emerald-700 transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2 active:scale-95">
                <span class="material-symbols-outlined text-base">person_add</span>
                <span>Tugaskan Koordinator Baru</span>
            </button>
        </div>

        <!-- Coordinator List Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-600 font-extrabold border-b border-slate-200/80 uppercase tracking-wider text-[10.5px]">
                        <tr>
                            <th class="py-4 px-4">No</th>
                            <th class="py-4 px-4">Nama Koordinator</th>
                            <th class="py-4 px-4">Email Login</th>
                            <th class="py-4 px-4">Unit / Paket Tanggung Jawab</th>
                            <th class="py-4 px-4">Peran Sistem</th>
                            <th class="py-4 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($coordinators as $idx => $coord)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-900">{{ $idx + 1 }}</td>
                                <td class="py-4 px-4 font-extrabold text-slate-900 text-xs">{{ $coord->name }}</td>
                                <td class="py-4 px-4 font-semibold text-slate-600">{{ $coord->email }}</td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 text-emerald-800 font-extrabold text-[11px] rounded-full border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        {{ $coord->unit ? strtoupper($coord->unit->name) : 'Belum Diatur' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 uppercase font-extrabold text-emerald-700">Koordinator Paket</td>
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <form action="{{ route('admin.coordinators.destroy', $coord->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peran koordinator ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 text-rose-700 font-extrabold rounded-xl hover:bg-rose-100 transition-colors inline-flex items-center gap-1 text-[11px] border border-rose-200/60">
                                            <span class="material-symbols-outlined text-xs">delete</span>
                                            <span>Hapus Peran</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 font-semibold">Belum ada Koordinator Paket yang ditugaskan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Assign Modal -->
    <div id="add-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl flex flex-col gap-5 border border-slate-100">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-lg text-emerald-700 flex items-center gap-2">
                    <span class="material-symbols-outlined">person_add</span>
                    Tugaskan Koordinator Paket Baru
                </h3>
                <button onclick="document.getElementById('add-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.coordinators.store') }}" method="POST" class="flex flex-col gap-4 text-xs">
                @csrf
                <div>
                    <label class="font-extrabold text-slate-700 mb-1.5 block">Pilih Pengguna User</label>
                    <select name="user_id" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Pengguna --</option>
                        @foreach($availableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-extrabold text-slate-700 mb-1.5 block">Pilih Unit / Paket Tanggung Jawab</label>
                    <select name="unit_id" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Unit/Paket --</option>
                        @foreach($units as $un)
                            <option value="{{ $un->id }}">{{ $un->name }} ({{ $un->package_type }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 mt-2">
                    <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-extrabold text-xs rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white font-extrabold text-xs rounded-xl hover:bg-emerald-700 shadow-md shadow-emerald-600/20">
                        Simpan & Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
