<x-layouts.auth>
    <x-slot:title>Presensi QR Code - PKBM IBADURRAHMAN</x-slot:title>

    <div class="absolute top-6 left-6 z-20">
        <a href="{{ route('portal') }}" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors font-label-md select-none">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Portal Utama
        </a>
    </div>

    <main class="flex-grow flex flex-col items-center justify-center p-6 relative z-10">
        <div class="text-center mb-8">
            <h1 class="font-display-lg text-on-surface tracking-tight text-3xl md:text-4xl font-extrabold uppercase mb-2">PINDAI UNTUK ABSENSI</h1>
            <p class="font-headline-sm text-lg md:text-xl text-on-surface-variant font-medium" id="school-name-subtitle">{{ $schoolName }}</p>
        </div>

        <div class="w-full max-w-md card-layer-2 rounded-2xl p-8 flex flex-col items-center gap-6 relative overflow-hidden bg-white shadow-[0px_12px_40px_rgba(38,50,56,0.08)] border border-outline-variant/30">
            <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>

            <!-- Unit Selector Dropdown -->
            <div class="w-full flex flex-col gap-2">
                <label for="unit_id_selector" class="font-label-md text-label-md text-on-surface font-semibold text-primary">Pilih Lokasi Unit</label>
                <div class="relative w-full">
                    <select id="unit_id_selector" class="appearance-none w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 pr-10 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer" onchange="onUnitChanged()">
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" data-name="{{ $unit->name }}" data-package="{{ $unit->package_type }}">{{ $unit->name }} ({{ $unit->package_type }})</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                </div>
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
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        QR Code diperbarui dalam:
                    </span>
                    <span id="countdown-text" class="font-bold text-primary">{{ $interval }} detik</span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden">
                    <div id="countdown-bar" class="bg-primary h-full transition-all duration-1000 ease-linear" style="width: 100%;"></div>
                </div>
            </div>

            <div class="text-center text-xs text-outline leading-relaxed border-t border-outline-variant/30 pt-4 w-full" id="last-updated-container">
                Terakhir diperbarui: <span id="last-updated-time" class="font-bold">-</span>
            </div>
        </div>
    </main>

    <!-- QR Rotation Scripting -->
    <script>
        const rotationInterval = parseInt("{{ $interval }}"); // rotation interval in seconds
        let secondsLeft = rotationInterval;
        let countdownTimer = null;

        function formatTime(date) {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            return `${hours}.${minutes}.${seconds}`;
        }

        function onUnitChanged() {
            const select = document.getElementById('unit_id_selector');
            if (select) {
                const selectedOption = select.options[select.selectedIndex];
                const subtitle = document.getElementById('school-name-subtitle');
                if (subtitle && selectedOption) {
                    subtitle.textContent = selectedOption.getAttribute('data-name');
                }
            }
            fetchQrToken();
        }

        // Fetch dynamic token and update image source
        function fetchQrToken() {
            const qrLoading = document.getElementById('qr-loading');
            const qrImg = document.getElementById('qr-image');
            const select = document.getElementById('unit_id_selector');
            
            if (qrLoading) qrLoading.classList.remove('hidden');
            if (qrImg) qrImg.classList.add('hidden');

            const unitId = select ? select.value : '';

            fetch("{{ route('qr.public.token') }}?unit_id=" + unitId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.url) {
                        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(data.url)}`;
                        
                        if (qrImg) {
                            qrImg.src = qrUrl;
                            qrImg.onload = function() {
                                if (qrLoading) qrLoading.classList.add('hidden');
                                qrImg.classList.remove('hidden');
                            };
                        }

                        // Reset countdown
                        secondsLeft = rotationInterval;
                        updateCountdownUI();
                        
                        // Update last updated time
                        const timeEl = document.getElementById('last-updated-time');
                        if (timeEl) {
                            timeEl.textContent = formatTime(new Date());
                        }
                    }
                })
                .catch(err => {
                    console.error("Failed to generate QR token", err);
                });
        }

        function updateCountdownUI() {
            const percentage = (secondsLeft / rotationInterval) * 100;
            const textEl = document.getElementById('countdown-text');
            const barEl = document.getElementById('countdown-bar');
            if (textEl) textEl.textContent = `${secondsLeft} detik`;
            if (barEl) barEl.style.width = `${percentage}%`;
        }

        function startTimer() {
            if (countdownTimer) {
                clearInterval(countdownTimer);
            }
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
            onUnitChanged();
            startTimer();
        };

        // Cleanup interval on page unload
        window.onunload = function() {
            if (countdownTimer) {
                clearInterval(countdownTimer);
            }
        };
    </script>
</x-layouts.auth>
