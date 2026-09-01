<x-layouts.auth>
    <x-slot:title>Portal - PKBM IBADURRAHMAN</x-slot:title>

    <main class="flex-grow flex flex-col items-center justify-center p-6 md:p-12 relative z-10 max-w-5xl mx-auto w-full my-auto">
        <!-- Logo Area -->
        <div class="flex items-center justify-center mb-6 select-none bg-transparent">
            <img src="{{ asset('images/logo-pkbm-transparent.png') }}" alt="PKBM IBADURRAHMAN Logo" class="w-32 h-32 md:w-40 md:h-40 object-contain filter drop-shadow-sm">
        </div>
        
        <!-- Header Text -->
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-5xl font-extrabold text-primary tracking-tight mb-2">PKBM IBADURRAHMAN</h1>
            <p class="text-sm md:text-base text-on-surface-variant font-semibold uppercase tracking-wider">Sistem Absensi Tenaga Pendidik</p>
        </div>

        <!-- 3 Columns Grid of Large Cards -->
        <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 1. Login Admin Card -->
            <a class="bg-white rounded-2xl border border-outline-variant/30 shadow-[0_4px_20px_rgba(0,0,0,0.015)] p-6 md:p-8 flex flex-col justify-between transition-all duration-350 hover:shadow-[0_12px_36px_rgba(46,125,50,0.08)] hover:-translate-y-1 hover:border-primary/40 group cursor-pointer relative overflow-hidden" href="{{ route('admin.login') }}" onclick="window.location='{{ route('admin.login') }}'">
                <div>
                    <!-- Icon container -->
                    <div class="w-14 h-14 bg-primary/5 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-primary/10 transition-colors duration-300">
                        <span class="material-symbols-outlined text-3xl text-primary font-semibold">admin_panel_settings</span>
                    </div>
                    <!-- Title -->
                    <h2 class="text-xl font-bold text-on-surface mb-2 flex items-center gap-2">
                        Login Admin
                    </h2>
                    <!-- Description -->
                    <p class="text-sm text-on-surface-variant leading-relaxed">Akses dashboard pengelolaan data tenaga pendidik, pengaturan absensi, dan laporan institusi.</p>
                </div>
                <!-- Action Link -->
                <div class="mt-6 flex items-center text-xs font-bold text-primary gap-1.5 opacity-80 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300">
                    Masuk sebagai Admin 
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </div>
            </a>

            <!-- 2. Login Tenaga Pendidik Card -->
            <a class="bg-white rounded-2xl border border-outline-variant/30 shadow-[0_4px_20px_rgba(0,0,0,0.015)] p-6 md:p-8 flex flex-col justify-between transition-all duration-350 hover:shadow-[0_12px_36px_rgba(46,125,50,0.08)] hover:-translate-y-1 hover:border-primary/40 group cursor-pointer relative overflow-hidden" href="{{ route('teacher.login') }}" onclick="window.location='{{ route('teacher.login') }}'">
                <div>
                    <!-- Icon container -->
                    <div class="w-14 h-14 bg-primary/5 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-primary/10 transition-colors duration-300">
                        <span class="material-symbols-outlined text-3xl text-primary font-semibold">co_present</span>
                    </div>
                    <!-- Title -->
                    <h2 class="text-xl font-bold text-on-surface mb-2 flex items-center gap-2">
                        Login Tenaga Pendidik
                    </h2>
                    <!-- Description -->
                    <p class="text-sm text-on-surface-variant leading-relaxed">Portal absensi harian, riwayat kehadiran, dan informasi jadwal untuk tenaga pendidik.</p>
                </div>
                <!-- Action Link -->
                <div class="mt-6 flex items-center text-xs font-bold text-primary gap-1.5 opacity-80 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300">
                    Masuk sebagai Pendidik 
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </div>
            </a>

            <!-- 3. Tampilkan Barcode Card -->
            <a class="bg-white rounded-2xl border border-outline-variant/30 shadow-[0_4px_20px_rgba(0,0,0,0.015)] p-6 md:p-8 flex flex-col justify-between transition-all duration-350 hover:shadow-[0_12px_36px_rgba(46,125,50,0.08)] hover:-translate-y-1 hover:border-primary/40 group cursor-pointer relative overflow-hidden" href="{{ route('qr.public') }}" onclick="window.location='{{ route('qr.public') }}'">
                <div>
                    <!-- Icon container -->
                    <div class="w-14 h-14 bg-primary/5 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-primary/10 transition-colors duration-300">
                        <span class="material-symbols-outlined text-3xl text-primary font-semibold">qr_code_2</span>
                    </div>
                    <!-- Title -->
                    <h2 class="text-xl font-bold text-on-surface mb-2 flex items-center gap-2">
                        Tampilkan Barcode
                    </h2>
                    <!-- Description -->
                    <p class="text-sm text-on-surface-variant leading-relaxed">Tampilkan QR Code dinamis untuk absensi tenaga pendidik.</p>
                </div>
                <!-- Action Link -->
                <div class="mt-6 flex items-center text-xs font-bold text-primary gap-1.5 opacity-80 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300">
                    Buka Layar QR 
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </div>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full py-6 text-center z-10 border-t border-outline-variant/30 mt-auto bg-surface/50 backdrop-blur-sm">
        <p class="font-body-sm text-body-sm text-on-surface-variant">
            © 2026 PKBM IBADURRAHMAN. Hak Cipta Dilindungi.
        </p>
        <div class="mt-2 flex justify-center gap-4">
            <a class="font-label-sm text-label-sm text-primary hover:underline" href="#">Kebijakan Privasi</a>
            <span class="text-outline-variant">•</span>
            <a class="font-label-sm text-label-sm text-primary hover:underline" href="#">Syarat dan Ketentuan</a>
        </div>
    </footer>
</x-layouts.auth>
