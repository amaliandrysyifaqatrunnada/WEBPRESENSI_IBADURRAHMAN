<x-layouts.admin>
    <x-slot:title>PKBM IBADURRAHMAN - Tampilan QR Code</x-slot:title>

    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-background">Pengaturan Kehadiran</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">Gunakan layar ini di kantor administrasi agar guru dapat memindai QR Code sebagai failover alternatif.</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-outline-variant mb-8 gap-6 overflow-x-auto custom-scrollbar">
        <a href="{{ route('admin.settings.attendance') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Aturan Umum</a>
        <a href="{{ route('admin.settings.gps') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Pengaturan GPS</a>
        <a href="{{ route('admin.settings.qr') }}" class="pb-3 border-b-2 border-primary font-label-md text-label-md text-primary whitespace-nowrap font-bold">Tampilan QR Code</a>
        <a href="{{ route('admin.devices.index') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Perangkat Sekolah</a>
    </div>

    <div class="max-w-md mx-auto card-layer-2 rounded-2xl p-8 flex flex-col items-center gap-6 relative overflow-hidden bg-white">
        <div class="absolute top-0 left-0 w-full h-2 bg-[#2E7D32]"></div>

        <div class="text-center">
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">QR Code Absensi Dinamis</h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Pindai kode di bawah untuk mencatat kehadiran masuk/pulang.</p>
        </div>

        <!-- QR Display Canvas -->
        <div class="w-64 h-64 border-2 border-outline-variant/30 rounded-xl bg-surface-container flex items-center justify-center relative overflow-hidden p-4 shadow-inner" id="qr-loading-container">
            <img id="qr-image" class="w-full h-full object-contain hidden" alt="QR Code"/>
            <div id="qr-loading" class="flex flex-col items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined animate-spin text-[32px]">sync</span>
                <span class="text-xs">Membuat QR Code...</span>
            </div>
        </div>

        <!-- Countdown / Progress Indicator -->
        <div class="w-full flex flex-col gap-2">
            <div class="flex justify-between items-center text-xs text-on-surface-variant font-label-sm">
                <span>Memperbarui otomatis dalam:</span>
                <span id="countdown-text" class="font-bold text-[#2E7D32]">30s</span>
            </div>
            <!-- Progress Bar -->
            <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden">
                <div id="countdown-bar" class="bg-[#2E7D32] h-full transition-all duration-1000 ease-linear" style="width: 100%;"></div>
            </div>
        </div>

        <div class="text-center text-xs text-outline leading-relaxed border-t border-outline-variant/30 pt-4 w-full">
            <span class="material-symbols-outlined text-sm align-middle mr-1">security</span>
            Token QR Code dienkripsi secara aman dan berubah setiap 30 detik untuk mencegah kecurangan absensi lokasi.
        </div>
    </div>

    <!-- QR Rotation Scripting -->
    <script>
        const rotationInterval = parseInt("{{ $interval }}"); // rotation interval in seconds
        let secondsLeft = rotationInterval;
        let countdownTimer = null;

        // Fetch dynamic token and update image source
        function fetchQrToken() {
            document.getElementById('qr-loading').classList.remove('hidden');
            document.getElementById('qr-image').classList.add('hidden');

            fetch("{{ route('admin.settings.qr.token') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.url) {
                        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(data.url)}`;
                        
                        const qrImg = document.getElementById('qr-image');
                        qrImg.src = qrUrl;
                        qrImg.onload = function() {
                            document.getElementById('qr-loading').classList.add('hidden');
                            qrImg.classList.remove('hidden');
                        };

                        // Reset countdown
                        secondsLeft = rotationInterval;
                        updateCountdownUI();
                    }
                })
                .catch(err => {
                    console.error("Failed to generate QR token", err);
                });
        }

        function updateCountdownUI() {
            const percentage = (secondsLeft / rotationInterval) * 100;
            document.getElementById('countdown-text').textContent = `${secondsLeft}s`;
            document.getElementById('countdown-bar').style.width = `${percentage}%`;
        }

        function startTimer() {
            countdownTimer = setInterval(() => {
                secondsLeft--;
                if (secondsLeft < 0) {
                    // Fetch new QR Code
                    fetchQrToken();
                } else {
                    updateCountdownUI();
                }
            }, 1000);
        }

        // Initialize Page
        window.onload = function() {
            fetchQrToken();
            startTimer();
        };

        // Cleanup interval on page unload
        window.onunload = function() {
            if (countdownTimer) {
                clearInterval(countdownTimer);
            }
        };
    </script>
</x-layouts.admin>
