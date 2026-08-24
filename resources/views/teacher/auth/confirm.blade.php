<x-layouts.auth>
    <x-slot:title>Konfirmasi Data Guru - PKBM IBADURRAHMAN</x-slot:title>

    <main class="w-full max-w-md mx-auto my-auto">
        <!-- Glassmorphism Card Wrapper -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-[0px_12px_40px_rgba(38,50,56,0.08)] overflow-hidden transition-all duration-300">
            <!-- Header Section -->
            <div class="p-8 text-center border-b border-surface-variant bg-surface">
                <h1 class="font-headline-md text-headline-md text-on-surface mb-2">Konfirmasi Kehadiran</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Silakan periksa detail profil Anda sebelum masuk.</p>
            </div>
            
            <!-- Profile Data Section -->
            <div class="p-8 flex flex-col items-center">
                <!-- Profile Photo -->
                <div class="relative w-32 h-32 mb-6 rounded-full overflow-hidden border-4 border-surface shadow-sm ring-2 ring-primary/20 bg-surface-container-high flex items-center justify-center">
                    @if($teacher->avatar)
                        <img alt="Teacher Photo" class="w-full h-full object-cover" src="{{ asset('storage/' . $teacher->avatar) }}"/>
                    @else
                        <!-- Stitch logo default as profile pic -->
                        <img alt="PKBM Ibadurrahman Logo" class="w-full h-full object-contain p-2" src="{{ asset('images/logo-pkbm-transparent.png') }}"/>
                    @endif
                </div>

                <!-- Teacher Details -->
                <div class="text-center w-full space-y-1 mb-8">
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">{{ $teacher->name }}</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">badge</span>
                        {{ $teacher->nip ? 'NIP. ' . $teacher->nip : 'NIP. Tidak tersedia' }}
                    </p>
                    <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 bg-surface-variant text-on-surface-variant rounded-full font-label-sm text-label-sm">
                        <span class="material-symbols-outlined text-[14px]">school</span>
                        {{ $teacher->position }}
                    </div>
                </div>

                <!-- Confirmation Question -->
                <div class="w-full bg-surface-container-low p-4 rounded-lg border border-outline-variant mb-8 text-center">
                    <p class="font-headline-sm text-headline-sm text-on-surface">Apakah data ini benar?</p>
                </div>

                <!-- Actions Container -->
                <div class="w-full flex flex-col sm:flex-row gap-4">
                    <!-- Secondary Action: Kembali -->
                    <a href="{{ route('teacher.login') }}" class="flex-1 px-6 py-3 border border-outline text-on-surface-variant font-label-md text-label-md rounded-lg hover:bg-surface-variant hover:text-on-surface transition-colors focus:ring-2 focus:ring-primary/20 outline-none flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        Kembali
                    </a>
                    
                    <!-- Primary Action: Masuk -->
                    <form action="{{ route('teacher.confirm') }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-6 py-3 bg-primary text-on-primary font-label-md text-label-md rounded-lg hover:bg-tertiary transition-colors shadow-sm focus:ring-2 focus:ring-primary focus:ring-offset-2 outline-none flex items-center justify-center gap-2">
                            Masuk
                            <span class="material-symbols-outlined text-[20px]">login</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Subtle Footer -->
            <div class="py-4 text-center bg-surface border-t border-surface-variant">
                <p class="font-label-sm text-label-sm text-outline flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">shield</span>
                    Sistem Absensi Terenkripsi
                </p>
            </div>
        </div>

        <!-- Ambient shadow behind the card -->
        <div class="absolute inset-0 -z-10 flex items-center justify-center pointer-events-none opacity-50">
            <div class="w-full max-w-lg h-96 bg-primary/5 blur-[100px] rounded-full"></div>
        </div>
    </main>
</x-layouts.auth>
