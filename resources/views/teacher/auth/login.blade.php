<x-layouts.auth>
    <x-slot:title>Login Tenaga Pendidik - PKBM IBADURRAHMAN</x-slot:title>

    <main class="w-full max-w-md bg-surface-container-lowest border border-outline-variant rounded-xl p-8 md:p-10 shadow-[0px_12px_40px_rgba(38,50,56,0.06)] relative overflow-hidden mx-auto my-auto">
        <!-- Subtle Background Decoration -->
        <div class="absolute top-0 left-0 w-full h-2 bg-primary-container"></div>
        
        <!-- Branding -->
        <div class="flex flex-col items-center mb-8">
            <div class="w-20 h-20 mb-4 flex items-center justify-center">
                <img alt="PKBM IBADURRAHMAN Logo" class="w-full h-full object-contain" src="{{ asset('images/logo-pkbm-transparent.png') }}"/>
            </div>
            <h2 class="font-label-md text-label-md text-primary tracking-widest uppercase mb-2">PKBM IBADURRAHMAN</h2>
            <h1 class="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-lg md:text-headline-lg text-center text-on-surface">
                Login Tenaga Pendidik
            </h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-3 text-center">
                Masukkan Nama atau Email Anda untuk mengakses sistem.
            </p>
        </div>

        <!-- Flash Messages -->
        @if(session('error'))
            <div class="mb-4 p-3.5 bg-red-100 border border-red-200 text-red-700 text-xs rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div class="mb-4 p-3.5 bg-green-100 border border-green-200 text-green-700 text-xs rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Login Form -->
        <form class="flex flex-col gap-6" action="{{ route('teacher.login') }}" method="POST">
            @csrf
            @if(isset($units) && $units->count() > 0)
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2" for="unit-select">Pilih Unit Sekolah</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline">
                        <span class="material-symbols-outlined">school</span>
                    </div>
                    <select id="unit-select" class="w-full bg-surface-container-lowest border border-outline-variant rounded-12px py-3 pl-12 pr-10 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-colors appearance-none">
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">arrow_drop_down</span>
                </div>
            </div>
            @endif

            <!-- Input Field -->
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2" for="name">Nama Lengkap atau Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <input autocomplete="off" list="login-suggestions" class="w-full bg-surface-container-lowest border border-outline-variant rounded-12px py-3 pl-12 pr-4 font-body-md text-body-md text-on-surface placeholder-outline focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-colors" id="name" name="name" placeholder="Masukkan nama lengkap atau email Anda..." required type="text" value="{{ old('name') }}"/>
                    <datalist id="login-suggestions"></datalist>
                </div>
                @error('name')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Button -->
            <button class="w-full bg-primary-container text-on-primary text-white hover:bg-primary transition-colors py-3 px-6 rounded-12px font-label-md text-label-md flex items-center justify-center gap-2 group mt-2 shadow-sm" type="submit">
                Masuk
                <span class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </button>
        </form>

        <!-- Help Text -->
        <div class="mt-8 pt-6 border-t border-outline-variant text-center">
            <p class="font-body-sm text-body-sm text-on-surface-variant">
                Tidak dapat menemukan data Anda? <br class="md:hidden"/>
                <a class="font-label-sm text-label-sm text-primary hover:underline mt-1 inline-block" href="#">Hubungi Administrator</a>
            </p>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const unitSelect = document.getElementById('unit-select');
            const loginSuggestions = document.getElementById('login-suggestions');
            const nameInput = document.getElementById('name');

            async function fetchUsersForUnit(unitId) {
                if (!unitId || !loginSuggestions) return;
                try {
                    const response = await fetch(`{{ route('face.id.teachers') }}?unit_id=${unitId}`);
                    const users = await response.json();
                    
                    loginSuggestions.innerHTML = '';
                    
                    users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.name;
                        loginSuggestions.appendChild(option);
                    });
                } catch (err) {
                    console.error("Error fetching users:", err);
                }
            }

            if (unitSelect) {
                // Load initial suggestions
                fetchUsersForUnit(unitSelect.value);

                // Reload on select change
                unitSelect.addEventListener('change', (e) => {
                    fetchUsersForUnit(e.target.value);
                    nameInput.value = '';
                });
            }
        });
    </script>
</x-layouts.auth>
