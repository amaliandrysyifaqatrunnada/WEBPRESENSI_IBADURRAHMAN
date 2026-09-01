<x-layouts.auth>
    <x-slot:title>Login Admin - PKBM IBADURRAHMAN</x-slot:title>

    <div class="absolute inset-0 bg-pattern pointer-events-none"></div>
    <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-bl from-surface-container-high/50 to-transparent pointer-events-none rounded-bl-full opacity-60"></div>
    <div class="absolute bottom-0 left-0 w-1/3 h-2/3 bg-gradient-to-tr from-surface-container-lowest/80 to-transparent pointer-events-none rounded-tr-full opacity-60"></div>

    <main class="w-full max-w-md px-container-padding relative z-10 md:max-w-lg lg:max-w-[480px] mx-auto my-auto">
        <!-- Login Card -->
        <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-[16px] login-card-shadow p-8 md:p-10 flex flex-col gap-stack-lg">
            <!-- Branding Header -->
            <div class="flex flex-col items-center text-center gap-stack-sm">
                <div class="w-16 h-16 flex items-center justify-center mb-2">
                    <img alt="PKBM IBADURRAHMAN Logo" class="w-full h-full object-contain" src="{{ asset('images/logo-pkbm-transparent.png') }}"/>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface">PKBM IBADURRAHMAN</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Portal Administratif</p>
            </div>

            <!-- Flash Messages -->
            @if(session('error'))
                <div class="p-3.5 bg-error-container/20 border border-error/30 text-error text-xs rounded-xl flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-base">error</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if(session('info'))
                <div class="p-3.5 bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-xl flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-base">info</span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif
            @if(session('success'))
                <div class="p-3.5 bg-primary/10 border border-primary/20 text-primary text-xs rounded-xl flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('admin.login') }}" method="POST" class="flex flex-col gap-stack-md">
                @csrf
                <!-- Email Field -->
                <div class="flex flex-col relative">
                    <label class="font-label-md text-label-md text-on-surface-variant mb-[8px]" for="email">Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-opacity-80">mail</span>
                        <input class="custom-input w-full bg-surface-container-lowest border border-[#E6ECE7] rounded-xl py-3 pl-10 pr-4 font-body-md text-body-md text-on-surface placeholder:text-outline transition-all duration-200" id="email" name="email" placeholder="admin@ibadurrahman.sch.id" value="{{ old('email') }}" required type="email"/>
                    </div>
                    @error('email')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="flex flex-col relative">
                    <div class="flex justify-between items-center mb-[8px]">
                        <label class="font-label-md text-label-md text-on-surface-variant" for="password">Kata Sandi</label>
                        <a class="font-label-sm text-label-sm text-primary hover:text-primary-container transition-colors" href="#">Lupa kata sandi?</a>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-opacity-80">lock</span>
                        <input class="custom-input w-full bg-surface-container-lowest border border-[#E6ECE7] rounded-xl py-3 pl-10 pr-10 font-body-md text-body-md text-on-surface placeholder:text-outline transition-all duration-200" id="password" name="password" placeholder="••••••••" required type="password"/>
                        <button id="toggle-password" type="button" aria-label="Toggle password visibility" class="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface-variant transition-colors">
                            <span class="material-symbols-outlined text-[20px]" id="eye-icon">visibility_off</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center gap-2 mt-2">
                    <input class="w-4 h-4 rounded border-[#E6ECE7] text-primary-container focus:ring-primary-container focus:ring-2 focus:ring-opacity-20 cursor-pointer" id="remember" name="remember" type="checkbox"/>
                    <label class="font-body-sm text-body-sm text-on-surface-variant cursor-pointer select-none" for="remember">Ingat perangkat ini</label>
                </div>

                <!-- Actions -->
                <div class="mt-4">
                    <button class="w-full bg-[#2E7D32] text-white rounded-xl py-3 px-4 font-label-md text-label-md hover:bg-primary transition-all duration-200 active:scale-[0.98] shadow-sm hover:shadow-md flex justify-center items-center gap-2" type="submit">
                        <span class="">Masuk</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </div>
            </form>

            <!-- Footer Meta -->
            <div class="text-center border-t border-outline-variant/30 pt-stack-md mt-stack-sm">
                <p class="font-label-sm text-label-sm text-outline">
                    Hanya Akses Administratif Aman. <br class="md:hidden"/> Diberdayakan oleh Sistem Institusional.
                </p>
            </div>
        </div>
    </main>

    <!-- Decorative Corner Accents -->
    <div class="fixed top-6 left-6 text-outline opacity-40 font-mono text-[10px] hidden md:block select-none">
        SYS.AUTH.2024 // PKBM.V2
    </div>
    <div class="fixed bottom-6 right-6 text-outline opacity-40 hidden md:flex items-center gap-2 select-none">
        <span class="material-symbols-outlined text-[16px]">verified_user</span>
        <span class="font-label-sm text-label-sm">Koneksi Aman</span>
    </div>

    <!-- Toggle Password Visibility JS -->
    <script>
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');

        if (togglePassword && passwordInput && eyeIcon) {
            togglePassword.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.textContent = isPassword ? 'visibility' : 'visibility_off';
            });
        }
    </script>
</x-layouts.auth>
