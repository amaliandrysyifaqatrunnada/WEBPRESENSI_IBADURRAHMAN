<x-layouts.admin>
    <x-slot:title>Data Tenaga Pendidik - PKBM IBADURRAHMAN</x-slot:title>

    <!-- Page Header & Controls -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface mb-2">
                {{ $showTrashed ? 'Arsip Tenaga Pendidik (Terhapus)' : 'Tenaga Pendidik' }}
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant">
                {{ $showTrashed ? 'Daftar guru yang dihapus sementara. Anda dapat memulihkan data mereka di sini.' : 'Kelola data rekam medis, posisi, dan status aktif guru.' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3 w-full sm:w-auto">
            <!-- Trashed Toggle Button -->
            @if($showTrashed)
                <a href="{{ route('admin.teachers.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface hover:bg-surface-container-low transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">group</span>
                    Daftar Guru Aktif
                </a>
            @else
                <a href="{{ route('admin.teachers.index', ['trashed' => 1]) }}" class="flex items-center gap-2 px-4 py-2 bg-surface border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface hover:bg-surface-container-low transition-colors shadow-sm text-error hover:text-error/80">
                    <span class="material-symbols-outlined text-[20px]">delete_sweep</span>
                    Lihat Arsip Terhapus
                </a>
            @endif
            <!-- Export Dropdown -->
            <div class="relative" id="export-dropdown-wrapper">
                <button onclick="toggleExportDropdown(event)" class="flex items-center gap-2 px-4 py-2 bg-surface border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface hover:bg-surface-container-low transition-colors shadow-sm focus:outline-none cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    Ekspor
                </button>
                <div id="export-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg border border-outline-variant shadow-lg py-1 hidden z-30 font-sans">
                    <a href="{{ route('admin.teachers.export', array_merge(['format' => 'excel'], request()->all())) }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low transition-colors">Excel (.xlsx)</a>
                    <a href="{{ route('admin.teachers.export', array_merge(['format' => 'csv'], request()->all())) }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low transition-colors">CSV</a>
                    <a href="{{ route('admin.teachers.export', array_merge(['format' => 'pdf'], request()->all())) }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low transition-colors">PDF</a>
                </div>
            </div>
            <!-- Import Trigger Button -->
            <button onclick="openImportModal()" class="flex items-center gap-2 px-4 py-2 bg-surface border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface hover:bg-surface-container-low transition-colors shadow-sm cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">upload</span>
                Impor
            </button>
            @if(!$showTrashed)
                <button class="flex items-center gap-2 px-5 py-2 bg-[#2E7D32] hover:bg-[#1b6d24] text-white rounded-lg font-label-md text-label-md transition-colors shadow-sm active:scale-95" onclick="openAddModal()">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Tambah Guru
                </button>
            @endif
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-surface-container-lowest border border-[#E6ECE7] rounded-xl overflow-hidden shadow-sm">
        <!-- Table Toolbar -->
        <div class="p-4 border-b border-[#E6ECE7] flex flex-col sm:flex-row justify-between gap-4 bg-surface-bright">
            <!-- Search & Filter Form -->
            <form action="{{ route('admin.teachers.index') }}" method="GET" class="w-full flex flex-col sm:flex-row justify-between gap-4">
                @if($showTrashed)
                    <input type="hidden" name="trashed" value="1">
                @endif
                <!-- Search Input -->
                <div class="relative w-full sm:max-w-xs">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                    <input name="search" value="{{ $filters['search'] ?? '' }}" class="w-full pl-10 pr-4 py-2 bg-white border border-[#E6ECE7] rounded-lg font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] transition-colors h-10" placeholder="Cari guru..." type="text"/>
                </div>
                <!-- Filters -->
                <div class="flex gap-2">
                    <!-- Status Filter -->
                    <div class="relative">
                        <select name="status" onchange="this.form.submit()" class="appearance-none pl-4 pr-10 py-2 bg-white border border-[#E6ECE7] rounded-lg font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] h-10 cursor-pointer">
                            <option value="All Status" {{ ($filters['status'] ?? '') == 'All Status' ? 'selected' : '' }}>Semua Status</option>
                            <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[20px]">expand_more</span>
                    </div>
                    <!-- Position Filter -->
                    <div class="relative hidden sm:block">
                        <select name="position" onchange="this.form.submit()" class="appearance-none pl-4 pr-10 py-2 bg-white border border-[#E6ECE7] rounded-lg font-body-sm text-body-sm text-on-surface focus:outline-none focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] h-10 cursor-pointer">
                            <option value="All Positions" {{ ($filters['position'] ?? '') == 'All Positions' ? 'selected' : '' }}>Semua Posisi</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ ($filters['position'] ?? '') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[20px]">expand_more</span>
                    </div>
                    <!-- Apply Button -->
                    <button type="submit" class="bg-surface border border-outline-variant text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-surface-container-low transition-colors h-10 shadow-sm">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Wrapper -->
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-[#F7FAF7] border-b-2 border-[#E6ECE7]">
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Profil</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">ID / NIP</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Nama Guru</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Jabatan</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Kontak & Email</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Status</th>
                        <th class="py-3 px-6 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E6ECE7] bg-white">
                    @forelse($teachers as $teacher)
                        <tr class="hover:bg-[#F7FAF7] transition-colors group">
                            <!-- Profil -->
                            <td class="py-4 px-6">
                                <div class="w-10 h-10 rounded-full bg-surface-container overflow-hidden border border-[#E6ECE7] flex items-center justify-center font-bold text-primary bg-primary/10">
                                    @if($teacher->avatar)
                                        <img alt="Teacher Photo" class="w-full h-full object-cover" src="{{ asset('storage/' . $teacher->avatar) }}"/>
                                    @else
                                        {{ strtoupper(substr($teacher->name, 0, 2)) }}
                                    @endif
                                </div>
                            </td>
                            <!-- ID/NIP -->
                            <td class="py-4 px-6">
                                <div class="font-label-md text-label-md text-on-surface">{{ $teacher->display_id }}</div>
                                <div class="font-body-sm text-body-sm text-on-surface-variant">{{ $teacher->nip ?? '-' }}</div>
                            </td>
                            <!-- Nama -->
                            <td class="py-4 px-6">
                                <div class="font-label-md text-label-md text-on-surface">{{ $teacher->name }}</div>
                            </td>
                            <!-- Jabatan -->
                            <td class="py-4 px-6">
                                <div class="font-body-sm text-body-sm text-on-surface">{{ $teacher->position }}</div>
                            </td>
                            <!-- Kontak -->
                            <td class="py-4 px-6">
                                <div class="font-body-sm text-body-sm text-on-surface">{{ $teacher->phone ?? '-' }}</div>
                                <div class="font-label-sm text-label-sm text-outline">{{ $teacher->email }}</div>
                            </td>
                            <!-- Status -->
                            <td class="py-4 px-6">
                                @if($teacher->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#E8F5E9] text-[#2E7D32] border border-[#C8E6C9]">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFF8E1] text-[#F57F17] border border-[#FFECB3]">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <!-- Aksi -->
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @if($showTrashed)
                                        <!-- Restore Action -->
                                        <button onclick="confirmRestore({{ $teacher->id }}, '{{ $teacher->name }}')" class="p-1.5 text-on-surface-variant hover:text-[#2E7D32] hover:bg-[#E8F5E9] rounded transition-colors" title="Pulihkan Data">
                                            <span class="material-symbols-outlined text-[20px]">restore</span>
                                        </button>
                                    @else
                                        <!-- Face ID Action -->
                                        <a href="{{ route('admin.teachers.face-id', $teacher->id) }}" class="p-1.5 rounded transition-colors {{ $teacher->face_registered ? 'text-[#2E7D32] hover:bg-[#E8F5E9]' : 'text-on-surface-variant hover:bg-surface-container-high' }}" title="{{ $teacher->face_registered ? 'Reset/Daftar Ulang Face ID' : 'Daftarkan Face ID' }}">
                                            <span class="material-symbols-outlined text-[20px]">face</span>
                                        </a>
                                        <!-- Edit Action -->
                                        <button onclick="openEditModal({{ json_encode($teacher) }})" class="p-1.5 text-on-surface-variant hover:text-[#2E7D32] hover:bg-[#E8F5E9] rounded transition-colors" title="Edit">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </button>
                                        <!-- Delete Action (Soft Delete) -->
                                        <button onclick="confirmDelete({{ $teacher->id }}, '{{ $teacher->name }}')" class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded transition-colors" title="Hapus">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-6 text-center text-on-surface-variant">
                                Tidak ada data tenaga pendidik ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="p-4 border-t border-[#E6ECE7] bg-white flex items-center justify-between text-sm">
            <span class="font-body-sm text-on-surface-variant">
                Menampilkan {{ $teachers->firstItem() ?? 0 }} sampai {{ $teachers->lastItem() ?? 0 }} dari {{ $teachers->total() }} entries
            </span>
            <div class="flex gap-1">
                @if ($teachers->onFirstPage())
                    <button class="px-3 py-1 border border-[#E6ECE7] rounded text-on-surface-variant opacity-50 cursor-not-allowed" disabled>Prev</button>
                @else
                    <a href="{{ $teachers->previousPageUrl() }}" class="px-3 py-1 border border-[#E6ECE7] rounded text-on-surface hover:bg-surface-container">Prev</a>
                @endif

                @foreach ($teachers->getUrlRange(1, $teachers->lastPage()) as $page => $url)
                    @if ($page == $teachers->currentPage())
                        <button class="px-3 py-1 bg-[#2E7D32] text-white rounded font-medium">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1 border border-[#E6ECE7] rounded text-on-surface hover:bg-surface-container">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($teachers->hasMorePages())
                    <a href="{{ $teachers->nextPageUrl() }}" class="px-3 py-1 border border-[#E6ECE7] rounded text-on-surface hover:bg-surface-container">Next</a>
                @else
                    <button class="px-3 py-1 border border-[#E6ECE7] rounded text-on-surface-variant opacity-50 cursor-not-allowed" disabled>Next</button>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal: Tambah / Edit Guru -->
    <div class="fixed inset-0 z-[60] flex items-center justify-center hidden" id="addTeacherModal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <!-- Modal Content -->
        <div class="relative bg-white rounded-xl shadow-[0px_12px_40px_rgba(38,50,56,0.12)] w-full max-w-2xl mx-4 max-h-[90vh] overflow-hidden flex flex-col border border-[#E6ECE7]">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-[#E6ECE7] flex justify-between items-center bg-[#F7FAF7]">
                <h3 class="font-headline-sm text-headline-sm text-on-surface" id="modal-title">Tambah Guru Baru</h3>
                <button class="text-on-surface-variant hover:text-on-surface rounded-full p-1 hover:bg-[#E6ECE7] transition-colors" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <!-- Modal Body (Scrollable) -->
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-white">
                <form id="teacherForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <!-- Method spoofing placeholder for updates -->
                    <div id="method-spoof"></div>

                    <!-- Photo Upload Area -->
                    <div class="flex items-center gap-6 pb-6 border-b border-[#E6ECE7]">
                        <div class="w-24 h-24 rounded-full bg-surface-container-high border-2 border-dashed border-[#bfcaba] flex flex-col items-center justify-center text-on-surface-variant cursor-pointer hover:bg-surface-variant hover:border-[#2E7D32] transition-colors relative overflow-hidden" onclick="document.getElementById('avatar-input').click()">
                            <span class="material-symbols-outlined mb-1">add_a_photo</span>
                            <span class="text-xs">Upload</span>
                            <input type="file" id="avatar-input" name="avatar" class="hidden" accept="image/*" onchange="previewImage(this)">
                            <!-- Real-time image preview -->
                            <img id="avatar-preview" class="absolute inset-0 w-full h-full object-cover hidden" alt="Avatar Preview">
                        </div>
                        <div>
                            <h4 class="font-label-md text-label-md text-on-surface mb-1">Foto Profil</h4>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mb-3">JPG, PNG, atau GIF. Maksimal ukuran 2MB.</p>
                            <button class="px-3 py-1.5 bg-[#E8F5E9] text-[#2E7D32] border border-[#2E7D32]/20 rounded font-label-sm text-label-sm hover:bg-[#C8E6C9] transition-colors" type="button" onclick="document.getElementById('avatar-input').click()">
                                Pilih Berkas
                            </button>
                        </div>
                    </div>

                    <!-- Form Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                        <!-- ID Pendidik (Readonly for reference) -->
                        <div class="relative">
                            <label class="block font-label-md text-label-md text-on-surface mb-2">Teacher ID</label>
                            <input id="display-teacher-id" class="w-full px-4 py-2.5 bg-surface border border-[#E6ECE7] rounded-xl font-body-md text-body-md text-on-surface-variant focus:outline-none cursor-not-allowed" readonly type="text" value="Auto-Generated"/>
                        </div>

                        <!-- Full Name -->
                        <div class="relative">
                            <label class="block font-label-md text-label-md text-on-surface mb-2" for="form-name">Nama Lengkap <span class="text-error">*</span></label>
                            <input id="form-name" name="name" class="w-full px-4 py-2.5 bg-white border border-[#E6ECE7] rounded-xl font-body-md text-body-md text-on-surface focus:outline-none focus:border-[#2E7D32] transition-colors" placeholder="Contoh: Budi Santoso, S.Pd" required type="text"/>
                        </div>

                        <!-- Email -->
                        <div class="relative">
                            <label class="block font-label-md text-label-md text-on-surface mb-2" for="form-email">Email Login <span class="text-error">*</span></label>
                            <input id="form-email" name="email" class="w-full px-4 py-2.5 bg-white border border-[#E6ECE7] rounded-xl font-body-md text-body-md text-on-surface focus:outline-none focus:border-[#2E7D32] transition-colors" placeholder="budi@ibadurrahman.sch.id" required type="email"/>
                        </div>

                        <!-- Password / PIN -->
                        <div class="relative">
                            <label class="block font-label-md text-label-md text-on-surface mb-2" for="form-password" id="password-label">Kata Sandi / PIN <span class="text-error">*</span></label>
                            <input id="form-password" name="password" class="w-full px-4 py-2.5 bg-white border border-[#E6ECE7] rounded-xl font-body-md text-body-md text-on-surface focus:outline-none focus:border-[#2E7D32] transition-colors" placeholder="Minimal 6 karakter" type="password"/>
                        </div>

                        <!-- NIP -->
                        <div class="relative">
                            <label class="block font-label-md text-label-md text-on-surface mb-2" for="form-nip">NIP</label>
                            <input id="form-nip" name="nip" class="w-full px-4 py-2.5 bg-white border border-[#E6ECE7] rounded-xl font-body-md text-body-md text-on-surface focus:outline-none focus:border-[#2E7D32] transition-colors" placeholder="Masukkan NIP" type="text"/>
                        </div>

                        @if(auth()->user()->hasRole('superadmin'))
                        <!-- Unit Sekolah (Superadmin Only) -->
                        <div class="relative">
                            <label class="block font-label-md text-label-md text-on-surface mb-2" for="form-unit-id">Unit Sekolah <span class="text-error">*</span></label>
                            <div class="relative">
                                <select id="form-unit-id" name="unit_id" class="appearance-none w-full px-4 py-2.5 bg-white border border-[#E6ECE7] rounded-xl font-body-md text-body-md text-on-surface focus:outline-none focus:border-[#2E7D32] transition-colors cursor-pointer" required>
                                    <option value="" disabled selected>Pilih Unit</option>
                                    @foreach(\App\Models\Unit::all() as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        @endif

                        <!-- Position / Jabatan -->
                        <div class="relative">
                            <label class="block font-label-md text-label-md text-on-surface mb-2" for="form-position">Jabatan / Posisi <span class="text-error">*</span></label>
                            <div class="relative">
                                <select id="form-position" name="position" class="appearance-none w-full px-4 py-2.5 bg-white border border-[#E6ECE7] rounded-xl font-body-md text-body-md text-on-surface focus:outline-none focus:border-[#2E7D32] transition-colors cursor-pointer" required>
                                    <option value="" disabled selected>Pilih Posisi</option>
                                    <option value="Guru Kelas">Guru Kelas</option>
                                    <option value="Guru Mapel">Guru Mapel</option>
                                    <option value="Guru BK">Guru BK</option>
                                    <option value="Staff Tata Usaha">Staff Tata Usaha</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="relative">
                            <label class="block font-label-md text-label-md text-on-surface mb-2" for="form-phone">Nomor Telepon</label>
                            <input id="form-phone" name="phone" class="w-full px-4 py-2.5 bg-white border border-[#E6ECE7] rounded-xl font-body-md text-body-md text-on-surface focus:outline-none focus:border-[#2E7D32] transition-colors" placeholder="08xx-xxxx-xxxx" type="tel"/>
                        </div>

                        <!-- Status -->
                        <div class="relative">
                            <label class="block font-label-md text-label-md text-on-surface mb-2">Status Awal</label>
                            <div class="flex items-center gap-4 h-[44px]">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input checked id="status-active" class="text-[#2E7D32] focus:ring-[#2E7D32]" name="status" type="radio" value="active"/>
                                    <span class="font-body-md text-body-md text-on-surface">Aktif</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input id="status-inactive" class="text-[#2E7D32] focus:ring-[#2E7D32]" name="status" type="radio" value="inactive"/>
                                    <span class="font-body-md text-body-md text-on-surface">Nonaktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-[#E6ECE7] bg-[#F7FAF7] flex justify-end gap-3">
                <button class="px-5 py-2.5 rounded-xl font-label-md text-label-md text-on-surface-variant hover:bg-[#E6ECE7] transition-colors" onclick="closeModal()" type="button">
                    Batal
                </button>
                <button class="px-5 py-2.5 bg-[#2E7D32] hover:bg-[#1b6d24] text-white rounded-xl font-label-md text-label-md transition-colors shadow-sm active:scale-95" type="button" onclick="submitForm()">
                    Simpan Guru
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden forms for delete/restore operations -->
    <form id="delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <form id="restore-form" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Page Interaction Scripts -->
    <script>
        const modal = document.getElementById('addTeacherModal');
        const form = document.getElementById('teacherForm');
        const modalTitle = document.getElementById('modal-title');
        const methodSpoof = document.getElementById('method-spoof');
        const passwordLabel = document.getElementById('password-label');
        const passwordInput = document.getElementById('form-password');
        const displayIdInput = document.getElementById('display-teacher-id');
        const avatarPreview = document.getElementById('avatar-preview');

        // Flash message notification using SweetAlert
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#2E7D32',
                timer: 3000
            });
        @endif

        // Display validation errors if any
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#2E7D32'
            });
        @endif

        function openAddModal() {
            modalTitle.textContent = "Tambah Guru Baru";
            form.action = "{{ route('admin.teachers.store') }}";
            methodSpoof.innerHTML = ""; // no update spoof
            passwordLabel.innerHTML = 'Kata Sandi / PIN <span class="text-error">*</span>';
            passwordInput.required = true;
            displayIdInput.value = "Auto-Generated";

            // Reset form
            form.reset();
            avatarPreview.classList.add('hidden');
            avatarPreview.src = "";

            const unitSelect = document.getElementById('form-unit-id');
            if (unitSelect) {
                unitSelect.value = "";
            }

            modal.classList.remove('hidden');
        }

        function openEditModal(teacher) {
            modalTitle.textContent = "Edit Data Guru";
            form.action = `/admin/teachers/${teacher.id}`;
            methodSpoof.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            passwordLabel.innerHTML = 'Kata Sandi / PIN (Kosongkan jika tidak diubah)';
            passwordInput.required = false;
            displayIdInput.value = teacher.display_id || '';

            // Populate inputs
            document.getElementById('form-name').value = teacher.name;
            document.getElementById('form-email').value = teacher.email;
            document.getElementById('form-nip').value = teacher.nip ?? '';
            document.getElementById('form-position').value = teacher.position;
            document.getElementById('form-phone').value = teacher.phone ?? '';
            passwordInput.value = ""; // clear password field

            const unitSelect = document.getElementById('form-unit-id');
            if (unitSelect) {
                unitSelect.value = teacher.unit_id ?? '';
            }

            // Set radio button status
            if (teacher.status === 'active') {
                document.getElementById('status-active').checked = true;
            } else {
                document.getElementById('status-inactive').checked = true;
            }

            // Show avatar preview
            if (teacher.avatar) {
                avatarPreview.src = `/storage/${teacher.avatar}`;
                avatarPreview.classList.remove('hidden');
            } else {
                avatarPreview.classList.add('hidden');
                avatarPreview.src = "";
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function submitForm() {
            if (form.reportValidity()) {
                form.submit();
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                    avatarPreview.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Soft Delete trigger
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Guru?',
                text: `Anda yakin ingin menghapus data pendidik "${name}" secara sementara?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#BA1A1A',
                cancelButtonColor: '#707a6c',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteForm = document.getElementById('delete-form');
                    deleteForm.action = `/admin/teachers/${id}`;
                    deleteForm.submit();
                }
            });
        }

        // Restore trigger
        function confirmRestore(id, name) {
            Swal.fire({
                title: 'Pulihkan Guru?',
                text: `Anda yakin ingin memulihkan data pendidik "${name}" kembali aktif?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2E7D32',
                cancelButtonColor: '#707a6c',
                confirmButtonText: 'Ya, Pulihkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const restoreForm = document.getElementById('restore-form');
                    restoreForm.action = `/admin/teachers/${id}/restore`;
                    restoreForm.submit();
                }
            });
        }

        // Export dropdown toggling
        function toggleExportDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('export-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        document.addEventListener('click', function(event) {
            const wrapper = document.getElementById('export-dropdown-wrapper');
            const dropdown = document.getElementById('export-dropdown');
            if (dropdown && wrapper && !wrapper.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Import modal toggles
        function openImportModal() {
            document.getElementById('importTeachersModal').classList.remove('hidden');
        }
        function closeImportModal() {
            document.getElementById('importTeachersModal').classList.add('hidden');
        }

        // Import SweetAlert2 Notification Alerts
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#2E7D32'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#2E7D32'
            });
        @endif

        @if(session('error_import'))
            Swal.fire({
                icon: 'error',
                title: 'Impor Gagal',
                html: `
                    <div class="text-left max-h-60 overflow-y-auto text-xs space-y-2 border border-outline-variant/50 p-3 rounded-lg bg-slate-50 custom-scrollbar font-mono leading-relaxed">
                        <div class="font-semibold text-error mb-2">{{ session('error_import') }}</div>
                        <ul class="list-decimal pl-4 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                `,
                confirmButtonColor: '#2E7D32',
                customClass: {
                    htmlContainer: 'swal-custom-container'
                }
            });
        @endif
    </script>

    <!-- Import Teachers Modal -->
    <div id="importTeachersModal" class="fixed inset-0 bg-on-surface/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-inverse-surface rounded-xl border border-outline-variant shadow-2xl w-full max-w-md p-6 relative overflow-hidden font-sans">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-4 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">publish</span>
                Impor Data Guru
            </h3>
            <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex flex-col">
                    <label class="font-label-md text-on-surface mb-2" for="import-file">Pilih Berkas (.xlsx, .xls, .csv)</label>
                    <input id="import-file" name="file" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="file" accept=".xlsx,.xls,.csv" required/>
                </div>
                <div class="bg-surface-container-low p-4 rounded-xl text-xs text-on-surface-variant leading-relaxed space-y-2 border border-outline-variant/30">
                    <div class="font-semibold text-on-surface flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">info</span> Panduan Kolom Berkas:
                    </div>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Kolom Wajib: <b>Nama Lengkap</b>, <b>Email</b>, <b>Jabatan</b>.</li>
                        <li>Kolom Opsional: <b>NIP</b>, <b>Telepon</b>.</li>
                        <li>Baris pertama harus berisi judul kolom (header).</li>
                        <li>Gunakan database transaction: Jika terdapat satu saja baris data yang eror, seluruh proses impor dibatalkan.</li>
                    </ul>
                </div>
                <div class="pt-4 border-t border-outline-variant/30 flex justify-end gap-3">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2 border border-outline-variant rounded-xl font-label-md hover:bg-surface-container-low transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-xl font-label-md hover:bg-primary/95 transition-all shadow-sm">Impor Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
