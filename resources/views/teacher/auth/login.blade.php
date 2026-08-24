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
                Masukkan Email Anda untuk mengakses sistem.
            </p>
        </div>

        <!-- Login Form -->
        <form class="flex flex-col gap-6" action="{{ route('teacher.login') }}" method="POST">
            @csrf
            <!-- Input Field -->
            <div class="flex flex-col">
                <label class="font-label-md text-label-md text-on-surface mb-2" for="email">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline">
                        <span class="material-symbols-outlined">mail</span>
                    </div>
                    <input autocomplete="off" class="w-full bg-surface-container-lowest border border-outline-variant rounded-12px py-3 pl-12 pr-4 font-body-md text-body-md text-on-surface placeholder-outline focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-colors" id="email" name="email" placeholder="Contoh: budi@ibadurrahman.sch.id" required type="email" value="{{ old('email') }}"/>
                </div>
                @error('email')
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
</x-layouts.auth>
