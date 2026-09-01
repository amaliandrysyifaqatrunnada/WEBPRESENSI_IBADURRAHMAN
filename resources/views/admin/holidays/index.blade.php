<x-layouts.admin>
    <x-slot:title>Kalender Hari Libur - PKBM IBADURRAHMAN</x-slot:title>

    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Kalender Hari Libur</h2>
            <p class="text-sm text-on-surface-variant mt-1">Kelola daftar hari libur sekolah secara global atau per unit pendidikan.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button onclick="openImportModal()" class="px-4 py-2.5 bg-surface-container-high text-on-surface hover:bg-surface-container-highest text-sm font-semibold rounded-xl transition-all flex items-center gap-2 border border-outline-variant shadow-xs">
                <span class="material-symbols-outlined text-lg text-primary">upload_file</span>
                Import File Hari Libur
            </button>
            <button onclick="openAddModal()" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition-all flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-lg">add</span>
                Tambah Hari Libur
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold flex items-center gap-3 shadow-xs">
            <span class="material-symbols-outlined text-xl text-emerald-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm font-semibold flex items-center gap-3 shadow-xs">
            <span class="material-symbols-outlined text-xl text-amber-600">warning</span>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-semibold flex items-center gap-3 shadow-xs">
            <span class="material-symbols-outlined text-xl text-rose-600">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="card-layer-1 rounded-xl p-4 mb-6">
        <form action="{{ route('admin.holidays.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / keterangan..." class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2 text-sm focus:ring-primary focus:border-primary" />
            </div>
            <div>
                <select name="unit_id" onchange="this.form.submit()" class="bg-white border border-outline-variant rounded-xl px-4 py-2 text-sm focus:ring-primary focus:border-primary">
                    <option value="All" {{ $selectedUnitId === 'All' ? 'selected' : '' }}>Semua unit (Global & Spesifik)</option>
                    <option value="Global" {{ $selectedUnitId === 'Global' ? 'selected' : '' }}>Khusus Libur Global (Semua Unit)</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ (string)$selectedUnitId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
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
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Nama Hari Libur</th>
                        <th class="py-3.5 px-4">Keterangan</th>
                        <th class="py-3.5 px-4">Berlaku Untuk</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20 text-sm">
                    @forelse($holidays as $index => $holiday)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="py-3.5 px-4 font-medium">{{ $holidays->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4 font-bold text-primary">{{ \Carbon\Carbon::parse($holiday->date)->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 font-semibold text-on-surface">{{ $holiday->name }}</td>
                            <td class="py-3.5 px-4 text-on-surface-variant text-xs">{{ $holiday->description ?: '-' }}</td>
                            <td class="py-3.5 px-4">
                                @if(!$holiday->unit_id)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Semua Unit (Global)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $holiday->unit->name }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($holiday->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='openEditModal(@json($holiday))' class="p-1.5 text-on-surface-variant hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-on-surface-variant hover:text-error transition-colors">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-on-surface-variant text-sm">Belum ada data hari libur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-outline-variant/30">
            {{ $holidays->links() }}
        </div>
    </div>

    <!-- Modal Import Hari Libur -->
    <div id="importModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl border border-outline-variant">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-outline-variant/30">
                <h3 class="text-xl font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">upload_file</span>
                    Import Hari Libur dari File
                </h3>
                <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.holidays.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl text-xs text-blue-900 space-y-1">
                    <div class="font-bold flex items-center gap-1 text-primary">
                        <span class="material-symbols-outlined text-sm">info</span>
                        Petunjuk Format File (.xlsx, .csv):
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-slate-700">
                        <li>Kolom Header Baris 1: <strong>Tanggal</strong>, <strong>Nama Hari Libur</strong>, <strong>Keterangan</strong>, <strong>Berlaku Untuk</strong>.</li>
                        <li>Format Tanggal: <code class="bg-white px-1 py-0.5 rounded border border-blue-200">YYYY-MM-DD</code> atau <code class="bg-white px-1 py-0.5 rounded border border-blue-200">DD/MM/YYYY</code>.</li>
                        <li>Kolom Berlaku Untuk: isi <code class="bg-white px-1 py-0.5 rounded border border-blue-200">Semua Unit</code> untuk global, atau nama unit (misal: <code class="bg-white px-1 py-0.5 rounded border border-blue-200">Paket A</code>).</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Pilih Berkas Excel / CSV</label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv,.txt" class="w-full text-xs text-slate-600 border border-outline-variant rounded-xl p-2.5 bg-white focus:ring-primary focus:border-primary"/>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-outline-variant/30">
                    <a href="{{ route('admin.holidays.template') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">download</span>
                        Download Template CSV
                    </a>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition-all shadow-sm flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">file_upload</span>
                            Unggah & Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Hari Libur -->
    <div id="addModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-outline-variant">
            <h3 class="text-xl font-bold text-on-surface mb-4">Tambah Hari Libur</h3>
            <form action="{{ route('admin.holidays.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Tanggal</label>
                    <input type="date" name="date" required class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Nama Hari Libur</label>
                    <input type="text" name="name" required placeholder="Contoh: Hari Kemerdekaan" class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Keterangan</label>
                    <textarea name="description" rows="2" placeholder="Catatan tambahan..." class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Berlaku Untuk</label>
                    <select name="unit_id" class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary">
                        <option value="">Semua Unit (Global)</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked id="add_is_active" class="rounded text-primary focus:ring-primary"/>
                    <label for="add_is_active" class="text-sm font-semibold text-on-surface">Aktifkan Hari Libur Ini</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Hari Libur -->
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-outline-variant">
            <h3 class="text-xl font-bold text-on-surface mb-4">Edit Hari Libur</h3>
            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Tanggal</label>
                    <input type="date" name="date" id="edit_date" required class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Nama Hari Libur</label>
                    <input type="text" name="name" id="edit_name" required class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Keterangan</label>
                    <textarea name="description" id="edit_description" rows="2" class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase mb-1">Berlaku Untuk</label>
                    <select name="unit_id" id="edit_unit_id" class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2.5 text-sm focus:ring-primary focus:border-primary">
                        <option value="">Semua Unit (Global)</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="edit_is_active" class="rounded text-primary focus:ring-primary"/>
                    <label for="edit_is_active" class="text-sm font-semibold text-on-surface">Aktifkan Hari Libur Ini</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }
        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
        }
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }
        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }
        function openEditModal(holiday) {
            document.getElementById('editForm').action = '/admin/holidays/' + holiday.id;
            document.getElementById('edit_date').value = holiday.date.split('T')[0];
            document.getElementById('edit_name').value = holiday.name;
            document.getElementById('edit_description').value = holiday.description || '';
            document.getElementById('edit_unit_id').value = holiday.unit_id || '';
            document.getElementById('edit_is_active').checked = holiday.is_active;
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</x-layouts.admin>
