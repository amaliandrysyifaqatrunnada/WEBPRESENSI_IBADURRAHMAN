<x-layouts.admin>
    <x-slot:title>PKBM IBADURRAHMAN - Ubah Password</x-slot:title>

    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-background">Ubah Password</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">Perbarui kata sandi keamanan akun administrator Anda secara berkala.</p>
    </div>

    <div class="max-w-xl mx-auto card-layer-1 rounded-xl p-6 relative overflow-hidden bg-white shadow-sm border border-outline-variant/30">
        <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
        
        <form action="{{ route('admin.profile.password') }}" method="POST" class="space-y-5" id="passwordForm">
            @csrf

            <!-- Password Saat Ini -->
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2 font-semibold text-primary" for="current_password">Password Saat Ini</label>
                <div class="relative">
                    <input id="current_password" name="current_password" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary @error('current_password') border-error @enderror" type="password" required />
                    <button type="button" onclick="togglePasswordVisibility('current_password')" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors focus:outline-none">
                        <span class="material-symbols-outlined text-[20px]" id="visibility_icon_current_password">visibility</span>
                    </button>
                </div>
                @error('current_password')
                    <span class="text-xs text-[#ba1a1a] mt-1 font-semibold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password Baru -->
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2 font-semibold text-primary" for="password">Password Baru</label>
                <div class="relative">
                    <input id="password" name="password" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary @error('password') border-error @enderror" type="password" required />
                    <button type="button" onclick="togglePasswordVisibility('password')" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors focus:outline-none">
                        <span class="material-symbols-outlined text-[20px]" id="visibility_icon_password">visibility</span>
                    </button>
                </div>
                @error('password')
                    <span class="text-xs text-[#ba1a1a] mt-1 font-semibold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2 font-semibold text-primary" for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="password" required />
                    <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors focus:outline-none">
                        <span class="material-symbols-outlined text-[20px]" id="visibility_icon_password_confirmation">visibility</span>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-outline-variant/30 flex justify-end">
                <button class="btn-primary px-6 py-2.5 font-label-md text-label-md hover:bg-primary-container/90 transition-all flex items-center gap-2 active:scale-95 shadow-sm text-white bg-primary rounded-xl cursor-pointer" type="submit">
                    <span class="material-symbols-outlined">save</span>
                    Ubah Password
                </button>
            </div>
        </form>
    </div>

    <!-- Visibility & Notification scripts -->
    <script>
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById('visibility_icon_' + fieldId);
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.textContent = 'visibility_off';
                } else {
                    input.type = 'password';
                    icon.textContent = 'visibility';
                }
            }
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
