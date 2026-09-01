<x-layouts.admin>
    <x-slot:title>PKBM IBADURRAHMAN - Profil Saya</x-slot:title>

    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-background">Profil Saya</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">Kelola informasi profil administrator Anda.</p>
    </div>

    <div class="max-w-xl mx-auto card-layer-1 rounded-xl p-6 relative overflow-hidden bg-white shadow-sm border border-outline-variant/30">
        <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
        
        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5" id="profileForm">
            @csrf
            @method('PUT')

            <!-- Avatar Section -->
            <div class="flex flex-col items-center gap-3 pb-4 border-b border-outline-variant/30">
                <div class="w-24 h-24 rounded-full overflow-hidden border border-outline-variant relative group shadow-inner">
                    <img id="profile-avatar-preview" class="w-full h-full object-cover" src="{{ $user->avatar_url }}"/>
                    <label for="avatar-upload-input" class="absolute inset-0 bg-black/40 text-white text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer font-semibold uppercase tracking-wider">
                        Upload
                    </label>
                    <input type="file" id="avatar-upload-input" name="avatar" class="hidden" accept="image/*" onchange="previewAvatar(event)">
                </div>
                <span class="text-xs text-on-surface-variant">Klik lingkaran foto di atas untuk mengganti foto profil Anda.</span>
                @error('avatar')
                    <span class="text-xs text-[#ba1a1a] font-semibold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Nama Field -->
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2 font-semibold text-primary" for="name">Nama Lengkap</label>
                <input id="name" name="name" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary @error('name') border-error @enderror" type="text" value="{{ old('name', $user->name) }}" required />
                @error('name')
                    <span class="text-xs text-[#ba1a1a] mt-1 font-semibold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email -->
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2 font-semibold text-primary" for="email">Alamat Email</label>
                <input id="email" name="email" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary @error('email') border-error @enderror" type="email" value="{{ old('email', $user->email) }}" required />
                @error('email')
                    <span class="text-xs text-[#ba1a1a] mt-1 font-semibold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Unit Sekolah -->
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2 font-semibold text-primary" for="unit_id">Unit Sekolah</label>
                <select id="unit_id" name="unit_id" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary @error('unit_id') border-error @enderror disabled:bg-slate-50 disabled:cursor-not-allowed" onchange="updatePackageType()" {{ !$user->hasRole('superadmin') ? 'disabled' : '' }}>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" data-package="{{ $unit->package_type }}" {{ old('unit_id', $user->unit_id) == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
                @error('unit_id')
                    <span class="text-xs text-[#ba1a1a] mt-1 font-semibold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Tipe Paket (Read-Only) -->
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2 font-semibold text-primary" for="package_type">Tipe Paket (Read-Only)</label>
                <input id="package_type" class="w-full bg-slate-50 border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface-variant cursor-not-allowed" type="text" value="{{ $user->unit ? $user->unit->package_type : '-' }}" readonly />
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-outline-variant/30 flex justify-end">
                <button class="btn-primary px-6 py-2.5 font-label-md text-label-md hover:bg-primary-container/90 transition-all flex items-center gap-2 active:scale-95 shadow-sm text-white bg-primary rounded-xl cursor-pointer" type="submit">
                    <span class="material-symbols-outlined">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Script for image preview and success alert -->
    <script>
        function previewAvatar(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function updatePackageType() {
            const unitSelect = document.getElementById('unit_id');
            const packageInput = document.getElementById('package_type');
            const selectedOption = unitSelect.options[unitSelect.selectedIndex];
            const packageType = selectedOption.getAttribute('data-package') || '-';
            packageInput.value = packageType;
        }

        // Success Alert using SweetAlert
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#2E7D32',
                timer: 3000
            });
        @endif
    </script>
</x-layouts.admin>
