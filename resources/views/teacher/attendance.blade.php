<x-layouts.auth>
    <x-slot:title>PKBM IBADURRAHMAN - Presensi Guru</x-slot:title>

    <!-- html5-qrcode library for QR scanning -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <!-- Ambient background grid -->
    <div class="absolute inset-0 bg-pattern pointer-events-none"></div>

    <main class="w-full max-w-lg p-container-padding flex flex-col gap-6 relative z-10 mx-auto my-6">
        <!-- Main Card -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-[0px_12px_40px_rgba(38,50,56,0.12)] p-6 flex flex-col gap-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>

            <div class="flex justify-between items-center pb-4 border-b border-outline-variant/30">
                <div class="flex items-center gap-3">
                    <img alt="PKBM Ibadurrahman Logo" class="h-10 w-10 object-contain" src="{{ asset('images/logo-pkbm-transparent.png') }}"/>
                    <div>
                        <h1 class="font-label-md text-label-md text-primary tracking-wider font-bold">PKBM IBADURRAHMAN</h1>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Sistem Absensi Guru</p>
                    </div>
                </div>
                <!-- Mini Logged Teacher Profile -->
                <div class="flex items-center gap-2">
                    <div class="text-right">
                        <div class="font-label-sm text-label-sm text-on-surface">{{ $teacher->name }}</div>
                        <div class="text-[10px] text-outline">{{ $teacher->position }}</div>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center font-bold text-xs text-primary overflow-hidden">
                        @if($teacher->avatar)
                            <img src="{{ asset('storage/' . $teacher->avatar) }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($teacher->name, 0, 2)) }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Clock & Date -->
            <div class="bg-surface-container rounded-lg p-5 flex flex-col items-center justify-center gap-1 border border-outline-variant/30">
                <div class="font-display-lg text-display-lg text-primary font-bold tracking-tight" id="dynamic-clock">12:00:00</div>
                <div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest" id="dynamic-date">
                    {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
                </div>
            </div>

            <!-- Identitas Unit Pendidik -->
            <div class="bg-surface-container rounded-lg p-4 border border-outline-variant/30 text-xs text-on-surface-variant flex flex-col gap-1.5 font-sans">
                <div class="flex items-center gap-1.5 font-bold text-primary">
                    <span class="material-symbols-outlined text-[16px]">domain</span>
                    <span>UNIT: {{ $teacher->unit ? $teacher->unit->name : '-' }} ({{ $teacher->unit ? $teacher->unit->package_type : '-' }})</span>
                </div>
                <div class="flex items-start gap-1.5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-[16px] flex-shrink-0 mt-0.5">location_on</span>
                    <span>{{ $teacher->unit ? $teacher->unit->address : 'Alamat belum diatur' }}</span>
                </div>
            </div>

            <!-- Method Selector (Tabbed) -->
            <!-- Method Selector (Tabbed) -->
            <div class="grid grid-cols-3 gap-2 border-b border-outline-variant/30 pb-3" id="method-selector">
                <button onclick="switchMethod('gps')" id="btn-method-gps" class="py-2.5 px-3 rounded-lg text-xs font-bold border flex flex-col items-center gap-1 transition-all {{ $schoolSettings['method'] === 'gps' ? 'bg-primary/10 text-primary border-primary' : 'bg-white text-outline border-outline-variant hover:bg-slate-50' }}">
                    <span class="material-symbols-outlined">location_on</span>
                    GPS Geofence
                </button>
                <button onclick="switchMethod('qr')" id="btn-method-qr" class="py-2.5 px-3 rounded-lg text-xs font-bold border flex flex-col items-center gap-1 transition-all {{ $schoolSettings['method'] === 'qr' ? 'bg-primary/10 text-primary border-primary' : 'bg-white text-outline border-outline-variant hover:bg-slate-50' }}">
                    <span class="material-symbols-outlined">qr_code_scanner</span>
                    QR Scanner
                </button>
                <button onclick="switchMethod('face_id')" id="btn-method-face" class="py-2.5 px-3 rounded-lg text-xs font-bold border flex flex-col items-center gap-1 transition-all {{ $schoolSettings['method'] === 'face_id' ? 'bg-primary/10 text-primary border-primary' : 'bg-white text-outline border-outline-variant hover:bg-slate-50' }}">
                    <span class="material-symbols-outlined">face</span>
                    Face ID
                </button>
            </div>

            <!-- Map View Container (GPS Method) -->
            <div id="map-container" class="w-full h-48 rounded-lg overflow-hidden border border-outline-variant/50 relative bg-surface-container-low flex items-center justify-center {{ $schoolSettings['method'] === 'gps' ? '' : 'hidden' }}">
                <div id="map" class="w-full h-full absolute inset-0"></div>
                <div id="map-loading" class="relative z-10 flex flex-col items-center gap-2 text-on-surface-variant">
                    <span class="material-symbols-outlined animate-spin text-[32px]">sync</span>
                    <span class="text-xs">Memuat peta lokasi...</span>
                </div>
            </div>

            <!-- QR Reader Container (QR Method) -->
            <div id="qr-scanner-container" class="w-full aspect-square rounded-lg overflow-hidden border-2 border-dashed border-outline-variant bg-surface-container-low {{ $schoolSettings['method'] === 'qr' ? '' : 'hidden' }} flex flex-col items-center justify-center p-4 relative">
                <div id="qr-reader" class="w-full h-full bg-black"></div>
                <button onclick="switchMethod('gps')" class="absolute top-2 right-2 bg-on-surface/80 text-white rounded-full p-1.5 hover:bg-on-surface transition-colors z-20">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            <!-- Face ID Container (Face ID Method) -->
            <div id="face-id-container" class="w-full aspect-[4/3] rounded-lg overflow-hidden border border-outline-variant bg-surface-container-low {{ $schoolSettings['method'] === 'face_id' ? '' : 'hidden' }} flex flex-col items-center justify-center relative">
                <video id="face-webcam" autoplay muted playsinline class="w-full h-full object-cover scale-x-[-1]"></video>
                <canvas id="face-overlay" class="absolute inset-0 w-full h-full object-cover scale-x-[-1]"></canvas>

                <!-- Loading Screen -->
                <div id="face-loader" class="absolute inset-0 bg-slate-950/80 flex flex-col items-center justify-center text-white p-4 text-center">
                    <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p class="text-xs font-semibold" id="face-loader-text">Mempersiapkan kamera...</p>
                </div>

                <!-- Instructions Panel Overlay -->
                <div class="absolute bottom-4 left-4 right-4 bg-black/70 backdrop-blur-sm rounded-lg p-2.5 text-white text-[11px] flex justify-between items-center" id="face-instruction-panel">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-emerald-400 animate-pulse text-sm" id="face-status-icon">settings_overscan</span>
                        <span id="face-status-text">Menginisialisasi kamera...</span>
                    </div>
                </div>
            </div>

            <!-- Status Indicator Bar -->
            <div class="flex items-center justify-between gap-3 bg-secondary-container/20 border border-secondary-container rounded-lg p-4" id="location-bar">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary filled-icon" id="status-icon" style="font-variation-settings: 'FILL' 1;">location_on</span>
                    <span class="font-label-sm text-label-sm text-primary" id="location-status">Mendeteksi lokasi GPS...</span>
                </div>
            </div>

            <!-- Main Action Area -->
            <div class="flex flex-col gap-3">
                @if($attendance && $attendance->clock_in && $attendance->clock_out)
                    <!-- Already Checked Out -->
                    <div class="w-full bg-[#E8F5E9] text-[#2E7D32] border border-[#C8E6C9] py-4 rounded-lg font-label-md text-label-md text-center flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">verified</span>
                        SUDAH PRESENSI MASUK & PULANG
                    </div>
                @else
                    <!-- Absen Masuk atau Pulang -->
                    <button class="w-full bg-primary text-on-primary h-14 rounded-lg font-label-md text-label-md uppercase tracking-wider hover:bg-primary/95 transition-colors flex items-center justify-center gap-2 shadow-[0px_4px_20px_rgba(38,50,56,0.06)] active:scale-[0.98] text-white" id="absen-btn" onclick="submitAttendance()" disabled>
                        <span class="material-symbols-outlined">fingerprint</span>
                        <span id="absen-btn-text">
                            {{ ($attendance && $attendance->clock_in) ? 'ABSEN PULANG (CHECK OUT)' : 'ABSEN MASUK (CHECK IN)' }}
                        </span>
                    </button>
                @endif
            </div>

            <!-- Success Overlay (Hidden initially) -->
            <div class="absolute inset-0 bg-surface-container-lowest/98 backdrop-blur-md z-[50] flex flex-col items-center justify-center gap-6 opacity-0 pointer-events-none transition-opacity duration-300" id="success-overlay">
                <div class="w-24 h-24 bg-[#E8F5E9] rounded-full flex items-center justify-center shadow-lg border border-[#C8E6C9]">
                    <span class="material-symbols-outlined text-[#2E7D32] text-[48px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <div class="text-center">
                    <h2 class="font-headline-md text-headline-md text-[#2E7D32] font-bold">Berhasil!</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-2" id="success-time-text">Absensi berhasil dicatat.</p>
                </div>
                <button class="px-8 py-2.5 bg-primary hover:bg-primary/95 text-white rounded-lg font-label-md text-label-md transition-colors" onclick="location.reload()">
                    Tutup
                </button>
            </div>
        </div>

        <!-- Riwayat Kehadiran (Attendance History) -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-[0px_12px_40px_rgba(38,50,56,0.06)] p-6">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">history</span>
                Riwayat Presensi
            </h3>
            <div class="overflow-hidden border border-outline-variant/50 rounded-lg">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container border-b border-outline-variant/50">
                            <th class="py-2.5 px-4 font-label-sm text-label-sm text-on-surface-variant">Tanggal</th>
                            <th class="py-2.5 px-4 font-label-sm text-label-sm text-on-surface-variant">Masuk</th>
                            <th class="py-2.5 px-4 font-label-sm text-label-sm text-on-surface-variant">Pulang</th>
                            <th class="py-2.5 px-4 font-label-sm text-label-sm text-on-surface-variant">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30 text-body-sm">
                        @forelse($history as $hist)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="py-3 px-4 font-medium text-on-surface">
                                    {{ \Carbon\Carbon::parse($hist->date)->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-on-surface">
                                    <div>{{ $hist->clock_in ? \Carbon\Carbon::parse($hist->clock_in)->format('H:i') : '-' }}</div>
                                    @if($hist->clock_in)
                                        <div class="text-[10px] text-on-surface-variant font-medium mt-0.5">
                                            {{ $hist->status_masuk ?: 'Tepat Waktu' }}
                                            @if($hist->reward)
                                                <span class="text-primary font-bold">🏆 Reward</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-on-surface">
                                    <div>{{ $hist->clock_out ? \Carbon\Carbon::parse($hist->clock_out)->format('H:i') : '-' }}</div>
                                    @if($hist->clock_out)
                                        <div class="text-[10px] text-on-surface-variant font-medium mt-0.5">
                                            {{ $hist->status_pulang ?: 'Normal' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($hist->status === 'hadir')
                                        <span class="text-[#2E7D32] bg-[#E8F5E9] px-2 py-0.5 rounded-full text-xs font-semibold">
                                            Hadir
                                        </span>
                                    @elseif($hist->status === 'terlambat')
                                        <span class="text-[#F57F17] bg-[#FFF8E1] px-2 py-0.5 rounded-full text-xs font-semibold">
                                            Terlambat
                                        </span>
                                    @elseif($hist->status === 'izin')
                                        <span class="text-[#3F51B5] bg-[#E8EAF6] px-2 py-0.5 rounded-full text-xs font-semibold">
                                            Izin
                                        </span>
                                    @elseif($hist->status === 'sakit')
                                        <span class="text-[#9C27B0] bg-[#F3E5F5] px-2 py-0.5 rounded-full text-xs font-semibold">
                                            Sakit
                                        </span>
                                    @else
                                        <span class="text-[#D32F2F] bg-[#FFEBEE] px-2 py-0.5 rounded-full text-xs font-semibold">
                                            Alpha
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 px-4 text-center text-on-surface-variant">
                                    Belum ada riwayat kehadiran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Logout Action -->
        <form action="{{ route('teacher.logout') }}" method="POST" class="text-center mt-2">
            @csrf
            <button type="submit" class="text-xs text-outline hover:text-primary transition-colors cursor-pointer select-none">
                Keluar dari Sesi Tenaga Pendidik
            </button>
        </form>
    </main>

    <!-- Load Leaflet for fully reliable, keyless map rendering (GPS) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>

    <!-- Map, GPS, and QR scanning scripts -->
    <script>
        // Settings loaded from server
        const schoolLat = parseFloat("{{ $schoolSettings['latitude'] }}");
        const schoolLng = parseFloat("{{ $schoolSettings['longitude'] }}");
        const schoolRadius = parseFloat("{{ $schoolSettings['radius'] }}");

        let currentLat = 0;
        let currentLng = 0;
        let currentAccuracy = 0;
        let currentMethod = "{{ $schoolSettings['method'] }}";
        let isWithinGeofence = false;
        let html5QrScanner = null;

        // Face ID state variables
        let faceWebcamStream = null;
        let isFaceModelsLoaded = false;
        let faceDetectionInterval = null;
        let faceLivenessPassed = false;
        let faceCapturedDescriptor = null;

        // Server time offset calculation
        const serverTime = {{ now()->timestamp * 1000 }};
        const clientTime = Date.now();
        const serverTimeOffset = serverTime - clientTime;

        // Leaflet references
        let map = null;
        let userMarker = null;
        let schoolCircle = null;

        // DOM elements
        const locStatus = document.getElementById('location-status');
        const absenBtn = document.getElementById('absen-btn');
        const statusIcon = document.getElementById('status-icon');

        const faceVideo = document.getElementById('face-webcam');
        const faceCanvas = document.getElementById('face-overlay');
        const faceLoader = document.getElementById('face-loader');
        const faceLoaderText = document.getElementById('face-loader-text');
        const faceStatusText = document.getElementById('face-status-text');
        const faceStatusIcon = document.getElementById('face-status-icon');

        // 1. Dynamic Clock
        function updateTime() {
            const clockEl = document.getElementById('dynamic-clock');
            if (clockEl) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = `${hours}:${minutes}:${seconds}`;
            }
        }
        setInterval(updateTime, 1000);
        updateTime();

        // 2. Leaflet Map Initialization
        function initLeafletMap(lat, lng) {
            const loadingMap = document.getElementById('map-loading');
            if (loadingMap) loadingMap.classList.add('hidden');
            
            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            });

            const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri'
            });

            map = L.map('map', {
                center: [schoolLat, schoolLng],
                zoom: 18,
                zoomControl: false,
                layers: [satellite]
            });

            const baseMaps = {
                "Peta Satelit": satellite,
                "Peta Jalan": osm
            };
            L.control.layers(baseMaps).addTo(map);

            schoolCircle = L.circle([schoolLat, schoolLng], {
                color: '#2E7D32',
                fillColor: '#C8E6C9',
                fillOpacity: 0.3,
                radius: schoolRadius
            }).addTo(map);

            L.marker([schoolLat, schoolLng]).addTo(map).bindPopup('PKBM Ibadurrahman').openPopup();

            userMarker = L.marker([lat, lng], {
                title: "Lokasi Anda"
            }).addTo(map);

            const group = new L.featureGroup([userMarker, schoolCircle]);
            map.fitBounds(group.getBounds().pad(0.2));
        }

        function updateMapPosition(lat, lng) {
            if (map) {
                userMarker.setLatLng([lat, lng]);
                const group = new L.featureGroup([userMarker, schoolCircle]);
                map.fitBounds(group.getBounds().pad(0.2));
            } else {
                initLeafletMap(lat, lng);
            }
        }

        // 3. Geolocation Tracking
        function trackLocation() {
            const locationBar = document.getElementById('location-bar');
            if (isNaN(schoolLat) || isNaN(schoolLng)) {
                locStatus.innerHTML = `Status Lokasi: <b>Koordinat unit belum dikonfigurasi oleh Admin</b>`;
                if (locationBar) {
                    locationBar.classList.remove('bg-secondary-container/20', 'border-secondary-container');
                    locationBar.classList.add('bg-error-container/20', 'border-error');
                }
                if (absenBtn) absenBtn.disabled = true;
                const loadingMap = document.getElementById('map-loading');
                if (loadingMap) {
                    loadingMap.innerHTML = `<div class="text-error font-semibold flex items-center justify-center h-full">Koordinat unit belum dikonfigurasi</div>`;
                }
                return;
            }
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (position) => {
                        currentLat = position.coords.latitude;
                        currentLng = position.coords.longitude;
                        currentAccuracy = position.coords.accuracy || 0;

                        const distance = calculateDistance(currentLat, currentLng, schoolLat, schoolLng);
                        
                        updateMapPosition(currentLat, currentLng);

                        const isAccuracyOk = currentAccuracy <= 50;
                        isWithinGeofence = (distance <= schoolRadius);

                        if (currentMethod === 'gps') {
                            if (!isAccuracyOk) {
                                locStatus.innerHTML = `GPS kurang akurat (${currentAccuracy.toFixed(1)}m). Aktifkan GPS akurasi tinggi.`;
                                if (locationBar) {
                                    locationBar.classList.remove('bg-secondary-container/20', 'border-secondary-container');
                                    locationBar.classList.add('bg-error-container/20', 'border-error');
                                }
                                if (absenBtn) absenBtn.disabled = true;
                            } else if (isWithinGeofence) {
                                locStatus.innerHTML = `Status Lokasi: <b>Dalam Jangkauan</b> (Jarak: ${distance.toFixed(1)}m, Akurasi: ${currentAccuracy.toFixed(1)}m)`;
                                if (locationBar) {
                                    locationBar.classList.remove('bg-error-container/20', 'border-error');
                                    locationBar.classList.add('bg-secondary-container/20', 'border-secondary-container');
                                }
                                if (absenBtn) absenBtn.disabled = false;

                                if (schoolCircle) {
                                    schoolCircle.setStyle({color: '#2E7D32', fillColor: '#C8E6C9'});
                                }
                            } else {
                                locStatus.innerHTML = `Status Lokasi: <b>Di Luar Jangkauan</b> (Jarak: ${distance.toFixed(1)}m, Akurasi: ${currentAccuracy.toFixed(1)}m)`;
                                if (locationBar) {
                                    locationBar.classList.remove('bg-secondary-container/20', 'border-secondary-container');
                                    locationBar.classList.add('bg-error-container/20', 'border-error');
                                }
                                if (absenBtn) absenBtn.disabled = true;

                                if (schoolCircle) {
                                    schoolCircle.setStyle({color: '#ba1a1a', fillColor: '#ffdad6'});
                                }
                            }
                        } else if (currentMethod === 'qr') {
                            if (!isAccuracyOk) {
                                locStatus.innerHTML = `Mode QR (GPS kurang akurat: ${currentAccuracy.toFixed(1)}m). Aktifkan GPS akurasi tinggi.`;
                                if (locationBar) {
                                    locationBar.classList.remove('bg-secondary-container/20', 'border-secondary-container');
                                    locationBar.classList.add('bg-error-container/20', 'border-error');
                                }
                            } else if (!isWithinGeofence) {
                                locStatus.innerHTML = `Mode QR (Di Luar Jangkauan: ${distance.toFixed(1)}m, Akurasi: ${currentAccuracy.toFixed(1)}m)`;
                                if (locationBar) {
                                    locationBar.classList.remove('bg-secondary-container/20', 'border-secondary-container');
                                    locationBar.classList.add('bg-error-container/20', 'border-error');
                                }
                            } else {
                                locStatus.innerHTML = `Mode QR (Lokasi Valid. Jarak: ${distance.toFixed(1)}m, Akurasi: ${currentAccuracy.toFixed(1)}m)`;
                                if (locationBar) {
                                    locationBar.classList.remove('bg-error-container/20', 'border-error');
                                    locationBar.classList.add('bg-secondary-container/20', 'border-secondary-container');
                                }
                            }
                        } else if (currentMethod === 'face_id') {
                            if (!isAccuracyOk) {
                                locStatus.innerHTML = `Mode Face ID (GPS kurang akurat: ${currentAccuracy.toFixed(1)}m). Aktifkan GPS akurasi tinggi.`;
                                if (locationBar) {
                                    locationBar.classList.remove('bg-secondary-container/20', 'border-secondary-container');
                                    locationBar.classList.add('bg-error-container/20', 'border-error');
                                }
                                if (absenBtn) absenBtn.disabled = true;
                            } else if (!isWithinGeofence) {
                                locStatus.innerHTML = `Mode Face ID (Di Luar Jangkauan: ${distance.toFixed(1)}m, Akurasi: ${currentAccuracy.toFixed(1)}m)`;
                                if (locationBar) {
                                    locationBar.classList.remove('bg-secondary-container/20', 'border-secondary-container');
                                    locationBar.classList.add('bg-error-container/20', 'border-error');
                                }
                                if (absenBtn) absenBtn.disabled = true;
                            } else {
                                locStatus.innerHTML = `Mode Face ID (Lokasi Valid. Jarak: ${distance.toFixed(1)}m, Akurasi: ${currentAccuracy.toFixed(1)}m)`;
                                if (locationBar) {
                                    locationBar.classList.remove('bg-error-container/20', 'border-error');
                                    locationBar.classList.add('bg-secondary-container/20', 'border-secondary-container');
                                }
                                if (absenBtn && faceLivenessPassed) absenBtn.disabled = false;
                            }
                        }
                    },
                    (error) => {
                        locStatus.textContent = 'Gagal mendeteksi lokasi: GPS dinonaktifkan.';
                        if (locationBar) {
                            locationBar.classList.remove('bg-secondary-container/20', 'border-secondary-container');
                            locationBar.classList.add('bg-error-container/20', 'border-error');
                        }
                        if (absenBtn) absenBtn.disabled = true;
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                locStatus.textContent = 'Perangkat Anda tidak mendukung pelacakan GPS.';
                if (absenBtn) absenBtn.disabled = true;
            }
        }

        trackLocation();
        switchMethod(currentMethod);

        // Distance formula Helper
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000; // earth radius in meters
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // Tab switcher
        function switchMethod(method) {
            currentMethod = method;
            const mapContainer = document.getElementById('map-container');
            const qrContainer = document.getElementById('qr-scanner-container');
            const faceContainer = document.getElementById('face-id-container');

            const btnGps = document.getElementById('btn-method-gps');
            const btnQr = document.getElementById('btn-method-qr');
            const btnFace = document.getElementById('btn-method-face');

            // Reset tabs
            btnGps.className = "py-2.5 px-3 rounded-lg text-xs font-bold border flex flex-col items-center gap-1 transition-all bg-white text-outline border-outline-variant hover:bg-slate-50";
            btnQr.className = "py-2.5 px-3 rounded-lg text-xs font-bold border flex flex-col items-center gap-1 transition-all bg-white text-outline border-outline-variant hover:bg-slate-50";
            btnFace.className = "py-2.5 px-3 rounded-lg text-xs font-bold border flex flex-col items-center gap-1 transition-all bg-white text-outline border-outline-variant hover:bg-slate-50";

            if (method === 'gps') {
                btnGps.className = "py-2.5 px-3 rounded-lg text-xs font-bold border flex flex-col items-center gap-1 transition-all bg-primary/10 text-primary border-primary";
            } else if (method === 'qr') {
                btnQr.className = "py-2.5 px-3 rounded-lg text-xs font-bold border flex flex-col items-center gap-1 transition-all bg-primary/10 text-primary border-primary";
            } else if (method === 'face_id') {
                btnFace.className = "py-2.5 px-3 rounded-lg text-xs font-bold border flex flex-col items-center gap-1 transition-all bg-primary/10 text-primary border-primary";
            }

            mapContainer.classList.add('hidden');
            qrContainer.classList.add('hidden');
            faceContainer.classList.add('hidden');

            stopQrScanner();
            stopFaceCamera();

            if (method === 'gps') {
                mapContainer.classList.remove('hidden');
                statusIcon.textContent = 'location_on';
                resetButton();
                trackLocation();
            } else if (method === 'qr') {
                qrContainer.classList.remove('hidden');
                statusIcon.textContent = 'qr_code_scanner';
                if (absenBtn) absenBtn.disabled = true;
                startQrScanner();
            } else if (method === 'face_id') {
                faceContainer.classList.remove('hidden');
                statusIcon.textContent = 'face';
                if (absenBtn) absenBtn.disabled = true;
                startFaceCamera();
            }
        }

        // 4. html5-qrcode scanner controller
        function startQrScanner() {
            html5QrScanner = new Html5Qrcode("qr-reader");
            html5QrScanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText) => {
                    stopQrScanner();
                    let token = decodedText;
                    if (decodedText.includes('qr_token=')) {
                        try {
                            const urlParts = decodedText.split('?');
                            if (urlParts.length > 1) {
                                const urlParams = new URLSearchParams(urlParts[1]);
                                token = urlParams.get('qr_token') || decodedText;
                            }
                        } catch (e) {
                            console.error("Failed to parse token from URL", e);
                        }
                    }
                    submitAttendance(token);
                },
                (errorMessage) => {}
            ).catch(err => {
                showErrorMessage("Akses kamera gagal: " + err);
                switchMethod('gps');
            });
        }

        function stopQrScanner() {
            if (html5QrScanner) {
                html5QrScanner.stop().then(() => {
                    html5QrScanner = null;
                }).catch(err => {});
            }
        }

        // 5. Face Camera controller
        async function startFaceCamera() {
            if (!isFaceModelsLoaded) {
                faceLoaderText.textContent = "Memuat model biometrik wajah...";
                try {
                    await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
                    await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
                    await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
                    isFaceModelsLoaded = true;
                } catch (e) {
                    console.error("Models loading error:", e);
                    faceLoaderText.textContent = "Gagal memuat model. Hubungkan internet.";
                    showErrorMessage("Gagal memuat model weights biometrik wajah.");
                    return;
                }
            }

            try {
                faceWebcamStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 640, height: 480, facingMode: "user" },
                    audio: false 
                });
                faceVideo.srcObject = faceWebcamStream;
                faceVideo.addEventListener('play', onFacePlay);
            } catch (err) {
                console.error("Camera access failed:", err);
                faceLoaderText.textContent = "Gagal mengakses kamera.";
                showErrorMessage("Akses kamera ditolak.");
            }
        }

        function getMAR(landmarks) {
            const innerLipTop = landmarks[62];
            const innerLipBottom = landmarks[66];
            const innerLipLeft = landmarks[60];
            const innerLipRight = landmarks[64];
            
            const verticalDist = Math.hypot(innerLipTop.x - innerLipBottom.x, innerLipTop.y - innerLipBottom.y);
            const horizontalDist = Math.hypot(innerLipLeft.x - innerLipRight.x, innerLipLeft.y - innerLipRight.y);
            
            return verticalDist / (horizontalDist || 0.001);
        }

        function onFacePlay() {
            if (faceLoader) faceLoader.classList.add('hidden');
            faceStatusText.textContent = "Mencari wajah...";
            faceStatusIcon.textContent = "face";

            faceLivenessPassed = false;
            faceCapturedDescriptor = null;

            const displaySize = { width: faceVideo.videoWidth || 640, height: faceVideo.videoHeight || 480 };
            faceapi.matchDimensions(faceCanvas, displaySize);

            if (absenBtn) absenBtn.disabled = true;

            faceDetectionInterval = setInterval(async () => {
                if (faceVideo.paused || faceVideo.ended || !isFaceModelsLoaded) return;

                const detection = await faceapi.detectSingleFace(faceVideo, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                const ctx = faceCanvas.getContext('2d');
                ctx.clearRect(0, 0, faceCanvas.width, faceCanvas.height);

                if (detection) {
                    const resizedDetection = faceapi.resizeResults(detection, displaySize);
                    faceapi.draw.drawFaceLandmarks(faceCanvas, resizedDetection);

                    faceCapturedDescriptor = Array.from(detection.descriptor);

                    if (!faceLivenessPassed) {
                        faceStatusText.textContent = "Silakan BUKA MULUT Anda sedikit untuk verifikasi keaktifan.";
                        faceStatusIcon.textContent = "sentiment_very_satisfied";

                        const landmarks = detection.landmarks.positions;
                        const mar = getMAR(landmarks);

                        if (mar > 0.3) {
                            faceLivenessPassed = true;
                            faceStatusText.textContent = "Verifikasi keaktifan sukses! Siap presensi.";
                            faceStatusIcon.textContent = "check_circle";

                            // Enable the primary button for check-in
                            const distance = calculateDistance(currentLat, currentLng, schoolLat, schoolLng);
                            if (currentAccuracy <= 50 && distance <= schoolRadius) {
                                if (absenBtn) absenBtn.disabled = false;
                            }
                            clearInterval(faceDetectionInterval);
                        }
                    }
                } else {
                    faceStatusText.textContent = "Pastikan wajah terlihat jelas di kamera.";
                    faceStatusIcon.textContent = "face_5";
                }
            }, 150);
        }

        function stopFaceCamera() {
            if (faceWebcamStream) {
                faceWebcamStream.getTracks().forEach(track => track.stop());
                faceWebcamStream = null;
            }
            if (faceDetectionInterval) {
                clearInterval(faceDetectionInterval);
                faceDetectionInterval = null;
            }
            if (faceCanvas) {
                const ctx = faceCanvas.getContext('2d');
                ctx.clearRect(0, 0, faceCanvas.width, faceCanvas.height);
            }
            if (faceLoader) {
                faceLoader.classList.remove('hidden');
                faceLoaderText.textContent = "Mempersiapkan kamera...";
            }
        }

        // 6. AJAX Attendance Submission
        function submitAttendance(qrToken = null) {
            const distance = calculateDistance(currentLat, currentLng, schoolLat, schoolLng);
            const isAccuracyOk = currentAccuracy <= 50;

            if (!isAccuracyOk) {
                showErrorMessage('Lokasi GPS kurang akurat. Aktifkan GPS dengan akurasi tinggi dan coba kembali.');
                return;
            }

            if (distance > schoolRadius) {
                showErrorMessage('Anda berada di luar jangkauan radius sekolah.');
                return;
            }

            const hasCheckedIn = "{{ ($attendance && $attendance->clock_in) ? '1' : '0' }}" === "1";
            const actionType = hasCheckedIn ? 'check_out' : 'check_in';

            if (currentMethod === 'face_id') {
                if (!faceLivenessPassed || !faceCapturedDescriptor) {
                    showErrorMessage('Verifikasi keaktifan wajah gagal atau belum selesai.');
                    return;
                }

                // Process capture & mirrored watermark
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = faceVideo.videoWidth || 640;
                tempCanvas.height = faceVideo.videoHeight || 480;
                const tempCtx = tempCanvas.getContext('2d');

                tempCtx.translate(tempCanvas.width, 0);
                tempCtx.scale(-1, 1);
                tempCtx.drawImage(faceVideo, 0, 0, tempCanvas.width, tempCanvas.height);
                tempCtx.setTransform(1, 0, 0, 1, 0, 0);

                const watermarkCanvas = document.createElement('canvas');
                watermarkCanvas.width = tempCanvas.width;
                watermarkCanvas.height = tempCanvas.height;
                const wCtx = watermarkCanvas.getContext('2d');

                wCtx.drawImage(tempCanvas, 0, 0);

                // Draw a translucent black overlay at the bottom
                const rectHeight = 65;
                wCtx.fillStyle = 'rgba(0, 0, 0, 0.6)';
                wCtx.fillRect(0, watermarkCanvas.height - rectHeight, watermarkCanvas.width, rectHeight);

                // Draw text
                wCtx.fillStyle = '#FFFFFF';
                wCtx.font = 'bold 16px sans-serif';
                wCtx.textBaseline = 'top';

                const unitName = "{{ $teacher->unit ? $teacher->unit->name : '-' }}";
                wCtx.fillText(unitName, 15, watermarkCanvas.height - rectHeight + 10);

                wCtx.font = '14px sans-serif';
                
                // Format dynamic date and time using server time offset
                const now = new Date(Date.now() + serverTimeOffset);
                const pad = (n) => String(n).padStart(2, '0');
                const dateString = pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear();
                const timeString = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds()) + ' WIB';
                wCtx.fillText(dateString + ' ' + timeString, 15, watermarkCanvas.height - rectHeight + 35);

                const base64Selfie = watermarkCanvas.toDataURL('image/jpeg', 0.85);

                if (absenBtn) {
                    absenBtn.disabled = true;
                    absenBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span> Memproses...';
                }

                // POST Face ID attendance directly to the FaceIDController endpoint
                fetch("{{ route('face.id.attendance') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        latitude: currentLat,
                        longitude: currentLng,
                        accuracy: currentAccuracy,
                        action_type: actionType,
                        selfie: base64Selfie,
                        face_descriptor: faceCapturedDescriptor
                    })
                })
                .then(async (response) => {
                    const data = await response.json();
                    stopFaceCamera();
                    if (response.ok && data.success) {
                        document.getElementById('success-time-text').textContent = data.message;
                        document.getElementById('success-overlay').classList.remove('opacity-0', 'pointer-events-none');
                    } else {
                        let readableMsg = data.message || 'Presensi gagal. Silakan coba lagi.';
                        if (data.message === 'FACE_NOT_ENROLLED') {
                            readableMsg = "Wajah Anda belum terdaftar. Silakan hubungi admin.";
                        } else if (data.message === 'FACE_NOT_MATCHED') {
                            readableMsg = "Wajah tidak cocok dengan data yang terdaftar.";
                        } else if (data.message === 'OUTSIDE_GEOFENCE') {
                            readableMsg = "Anda berada di luar area presensi.";
                        } else if (data.message === 'GPS_ACCURACY_TOO_LOW') {
                            readableMsg = "Akurasi GPS kurang baik. Silakan tunggu hingga lokasi lebih akurat.";
                        }
                        showErrorMessage(readableMsg);
                        resetButton();
                    }
                })
                .catch((err) => {
                    stopFaceCamera();
                    showErrorMessage('Koneksi bermasalah atau verifikasi wajah gagal.');
                    resetButton();
                });

                return;
            }

            // Fallback for GPS & QR
            if (absenBtn) {
                absenBtn.disabled = true;
                absenBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span> Memproses...';
            }

            fetch("{{ route('teacher.attendance.submit') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    action_type: actionType,
                    latitude: currentLat,
                    longitude: currentLng,
                    accuracy: currentAccuracy,
                    method: currentMethod,
                    qr_token: qrToken
                })
            })
            .then(async (response) => {
                const data = await response.json();
                if (response.ok && data.success) {
                    document.getElementById('success-time-text').textContent = data.message;
                    document.getElementById('success-overlay').classList.remove('opacity-0', 'pointer-events-none');
                } else {
                    showErrorMessage(data.message || 'Presensi gagal. Silakan coba lagi.');
                    resetButton();
                }
            })
            .catch((err) => {
                showErrorMessage('Koneksi bermasalah atau validasi presensi gagal.');
                resetButton();
            });
        }

        function showErrorMessage(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Presensi Gagal',
                    text: message,
                    confirmButtonColor: '#2E7D32'
                });
            } else {
                alert(message);
            }
        }

        function resetButton() {
            if (absenBtn) {
                if (currentMethod === 'gps') {
                    absenBtn.disabled = !isWithinGeofence;
                } else if (currentMethod === 'qr') {
                    absenBtn.disabled = true;
                } else if (currentMethod === 'face_id') {
                    absenBtn.disabled = !faceLivenessPassed;
                }
                const hasCheckedIn = "{{ ($attendance && $attendance->clock_in) ? '1' : '0' }}" === "1";
                absenBtn.innerHTML = `<span class="material-symbols-outlined">fingerprint</span> ${hasCheckedIn ? 'ABSEN PULANG (CHECK OUT)' : 'ABSEN MASUK (CHECK IN)'}`;
            }
        }

        // Direct scan redirection
        const urlParams = new URLSearchParams(window.location.search);
        const qrTokenParam = urlParams.get('qr_token');
        if (qrTokenParam) {
            switchMethod('qr');
            submitAttendance(qrTokenParam);
        }
    </script>
</x-layouts.auth>
</x-layouts.auth>
