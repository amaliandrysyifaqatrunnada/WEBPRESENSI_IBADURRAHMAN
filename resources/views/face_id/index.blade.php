<x-layouts.auth>
    <x-slot:title>Portal Presensi Face ID - PKBM IBADURRAHMAN</x-slot:title>
 
    <main class="flex-grow flex flex-col items-center justify-center p-6 md:p-12 relative z-10 font-sans">
        <div class="glass-card rounded-2xl p-8 max-w-lg w-full border border-outline-variant/50 shadow-2xl relative overflow-hidden bg-white">
            <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
 
            <div class="text-center mb-6">
                <h1 class="font-display-lg text-primary text-2xl md:text-3xl font-bold tracking-tight">PRESENSI FACE ID</h1>
                <p class="font-headline-sm text-sm text-on-surface-variant font-semibold mt-1">Presensi Utama Berbasis Biometrik Wajah</p>
                @if($device && $unit)
                    <div class="mt-2 text-[10px] text-outline bg-slate-100 py-1 px-3 rounded-full w-fit mx-auto border border-outline-variant/30">
                        PERANGKAT: <strong class="text-on-surface">{{ $device->device_name }}</strong> (UNIT: {{ $unit->name }})
                    </div>
                @endif
            </div>
 
            <!-- STEP 1: Search Teacher -->
            <div id="step-search" class="space-y-6">
                <div class="bg-primary/5 p-4 rounded-xl border border-primary/20 text-xs text-primary leading-relaxed flex gap-2">
                    <span class="material-symbols-outlined text-[18px] shrink-0 font-bold">info</span>
                    <div>
                        <strong>Face ID:</strong> Posisikan wajah Anda pada kamera untuk mencatat kehadiran secara cepat.
                    </div>
                </div>
 
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2" for="teacher-query">Nama atau Nomor ID Tenaga Pendidik</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">person_search</span>
                        <input id="teacher-query" class="w-full pl-10 pr-4 py-3 bg-white border border-outline-variant rounded-xl font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary h-12" placeholder="Masukkan nama atau NIP Anda..." type="text"/>
                    </div>
                </div>
 
                <button id="btn-search-teacher" class="w-full py-3.5 bg-primary hover:bg-primary/95 text-white rounded-xl font-label-md transition-all shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">manage_search</span>
                    Mulai Presensi Face ID
                </button>
 
                <a href="{{ route('portal') }}" class="block text-center text-xs text-on-surface-variant hover:text-primary hover:underline transition-colors mt-2">
                    Kembali ke Portal Utama
                </a>
            </div>

            <!-- STEP 2: Camera & Liveness Challenge & Verification -->
            <div id="step-camera" class="hidden flex flex-col items-center space-y-6">
                <!-- Identified Teacher Info Header -->
                <div class="w-full bg-slate-50 border border-outline-variant/40 p-3 rounded-xl flex items-center justify-between text-xs">
                    <div>
                        <div class="text-on-surface-variant font-medium">Tenaga Pendidik Teridentifikasi:</div>
                        <div class="font-bold text-on-surface mt-0.5 text-sm" id="identified-teacher-name">Nama Guru</div>
                    </div>
                    <button id="btn-reset-flow" class="text-red-600 hover:underline flex items-center gap-0.5 font-semibold">
                        <span class="material-symbols-outlined text-[14px]">cancel</span> Batal
                    </button>
                </div>

                <!-- Select Action Type -->
                <div class="w-full flex gap-3" id="action-type-selector">
                    <label class="flex-1 border border-outline-variant rounded-xl p-3 flex items-center justify-center gap-2 cursor-pointer transition-all hover:bg-slate-50 active:scale-95" id="label-checkin">
                        <input type="radio" name="action_type" value="check_in" checked class="text-primary focus:ring-primary">
                        <span class="font-label-md text-sm text-on-surface font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-emerald-600 text-sm font-bold">login</span> Absen Masuk
                        </span>
                    </label>
                    <label class="flex-1 border border-outline-variant rounded-xl p-3 flex items-center justify-center gap-2 cursor-pointer transition-all hover:bg-slate-50 active:scale-95" id="label-checkout">
                        <input type="radio" name="action_type" value="check_out" class="text-primary focus:ring-primary">
                        <span class="font-label-md text-sm text-on-surface font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-orange-600 text-sm font-bold">logout</span> Absen Pulang
                        </span>
                    </label>
                </div>

                <!-- GPS Location Information -->
                <div class="w-full bg-emerald-50/50 border border-emerald-100 rounded-xl p-3 text-[11px] text-emerald-800 flex justify-between">
                    <div>GPS: <span class="font-bold" id="gps-status">Mencari lokasi...</span></div>
                    <div>Akurasi: <span class="font-bold" id="gps-accuracy">-</span></div>
                    <div>Jarak: <span class="font-bold" id="gps-distance">-</span></div>
                </div>

                <!-- Webcam Feed -->
                <div class="w-full aspect-[4/3] max-w-[400px] bg-slate-900 rounded-xl overflow-hidden relative shadow-inner border border-outline-variant">
                    <video id="webcam" autoplay muted playsinline class="w-full h-full object-cover scale-x-[-1]"></video>
                    <canvas id="overlay" class="absolute inset-0 w-full h-full object-cover scale-x-[-1]"></canvas>

                    <!-- Loading Screen -->
                    <div id="loader" class="absolute inset-0 bg-slate-950/80 flex flex-col items-center justify-center text-white p-4 text-center">
                        <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-xs font-semibold" id="loader-text">Mempersiapkan kamera...</p>
                    </div>

                    <!-- Instructions Panel Overlay -->
                    <div class="absolute bottom-4 left-4 right-4 bg-black/70 backdrop-blur-sm rounded-lg p-2.5 text-white text-[11px] flex justify-between items-center" id="instruction-panel">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-emerald-400 animate-pulse text-sm" id="status-icon">settings_overscan</span>
                            <span id="status-text">Menginisialisasi kamera...</span>
                        </div>
                    </div>
                </div>

                <!-- Selfie Action Button -->
                <div class="w-full">
                    <button id="btn-capture-selfie" disabled class="w-full py-3.5 bg-slate-400 text-white rounded-xl font-label-md transition-all shadow-sm flex items-center justify-center gap-2 cursor-not-allowed">
                        <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                        Jepret & Proses Presensi
                    </button>
                </div>
            </div>

            <!-- STEP 3: Verification Result -->
            <div id="step-result" class="hidden flex flex-col items-center space-y-6 py-4 text-center">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center border border-emerald-200">
                    <span class="material-symbols-outlined text-3xl font-bold">check_circle</span>
                </div>

                <div class="space-y-1">
                    <h2 class="font-headline-md text-lg text-emerald-800 font-bold" id="result-message">Presensi Berhasil Dicatat</h2>
                    <p class="text-xs text-on-surface-variant leading-relaxed">Terima kasih, data kehadiran Anda telah masuk ke dalam sistem.</p>
                </div>

                <div class="w-full bg-slate-50 border border-outline-variant/40 rounded-xl p-4 text-left text-xs space-y-3 font-medium">
                    <div class="flex justify-between border-b border-outline-variant/20 pb-2">
                        <span class="text-on-surface-variant">Nama Lengkap</span>
                        <span class="font-bold text-on-surface text-right" id="result-name">Nama Guru</span>
                    </div>
                    <div class="flex justify-between border-b border-outline-variant/20 pb-2">
                        <span class="text-on-surface-variant">Unit Sekolah</span>
                        <span class="text-on-surface text-right" id="result-unit">Unit TK</span>
                    </div>
                    <div class="flex justify-between border-b border-outline-variant/20 pb-2">
                        <span class="text-on-surface-variant">Metode Presensi</span>
                        <span class="text-on-surface text-right font-semibold bg-primary/10 text-primary px-2 py-0.5 rounded">Face ID</span>
                    </div>
                    <div class="flex justify-between border-b border-outline-variant/20 pb-2">
                        <span class="text-on-surface-variant">Jam Presensi</span>
                        <span class="text-on-surface text-right font-bold text-sm" id="result-time">07:12 WIB</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant" id="result-status-label">Status Kehadiran</span>
                        <span class="text-on-surface text-right font-bold" id="result-status">Tepat Waktu</span>
                    </div>
                </div>
 
                <button onclick="window.location.reload()" class="w-full py-3 bg-primary hover:bg-primary/95 text-white rounded-xl font-label-md transition-all shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">done_all</span>
                    Selesai
                </button>
            </div>
        </div>
    </main>
 
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
    <script>
        const searchUrl = "{{ route('face.id.search') }}";
        const attendanceUrl = "{{ route('face.id.attendance') }}";
 
        let selectedTeacher = null;
        let webcamStream = null;
        let isModelsLoaded = false;
        let isCameraReady = false;
        let livenessPassed = false;
        let capturedDescriptor = null;
        let detectionInterval = null;
 
        // GPS status variables
        let locationCoords = { latitude: 0, longitude: 0, accuracy: 999 };
        let hasGps = false;
 
        // DOM elements
        const stepSearch = document.getElementById('step-search');
        const stepCamera = document.getElementById('step-camera');
        const stepResult = document.getElementById('step-result');
 
        const inputQuery = document.getElementById('teacher-query');
        const btnSearch = document.getElementById('btn-search-teacher');
 
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('overlay');
        const loader = document.getElementById('loader');
        const loaderText = document.getElementById('loader-text');
        const statusText = document.getElementById('status-text');
        const statusIcon = document.getElementById('status-icon');
 
        const gpsStatus = document.getElementById('gps-status');
        const gpsAccuracy = document.getElementById('gps-accuracy');
        const gpsDistance = document.getElementById('gps-distance');
 
        const txtTeacherName = document.getElementById('identified-teacher-name');
        const btnReset = document.getElementById('btn-reset-flow');
 
        // Server time sync
        const serverTime = {{ now()->timestamp * 1000 }};
        const clientTime = Date.now();
        const serverTimeOffset = serverTime - clientTime;
 
        // Initial Location Fetch (Wajib untuk GPS Geofence)
        function initLocation() {
            if (!navigator.geolocation) {
                gpsStatus.textContent = "GPS tidak didukung browser.";
                gpsStatus.className = "font-bold text-red-600";
                return;
            }
 
            gpsStatus.textContent = "Mencari lokasi...";
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    locationCoords.latitude = pos.coords.latitude;
                    locationCoords.longitude = pos.coords.longitude;
                    locationCoords.accuracy = pos.coords.accuracy;
                    hasGps = true;
                    
                    gpsStatus.textContent = "Lokasi didapatkan";
                    gpsStatus.className = "font-bold text-emerald-700";
                    gpsAccuracy.textContent = Math.round(pos.coords.accuracy) + " m";
                    
                    // Distance calculation (Haversine in JS for display only)
                    const unitLat = selectedTeacher && selectedTeacher.unit_latitude ? selectedTeacher.unit_latitude : parseFloat("{{ $unit?->latitude ?? 0 }}");
                    const unitLng = selectedTeacher && selectedTeacher.unit_longitude ? selectedTeacher.unit_longitude : parseFloat("{{ $unit?->longitude ?? 0 }}");
                    const dist = calculateHaversine(pos.coords.latitude, pos.coords.longitude, unitLat, unitLng);
                    gpsDistance.textContent = Math.round(dist) + " m";
                },
                (err) => {
                    console.error("GPS Error:", err);
                    gpsStatus.textContent = "Akses lokasi ditolak.";
                    gpsStatus.className = "font-bold text-red-600";
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses GPS Diperlukan',
                        text: 'Aktifkan GPS perangkat sekolah dan izinkan akses lokasi pada browser.',
                        confirmButtonColor: '#2E7D32'
                    });
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }
 
        function calculateHaversine(lat1, lon1, lat2, lon2) {
            const R = 6371000; // meters
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }
 
        // Search Teacher from DB (Scoping to device unit)
        btnSearch.addEventListener('click', async () => {
            const query = inputQuery.value.trim();
            if (!query) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Masukkan nama atau nomor ID pendidik.',
                    confirmButtonColor: '#2E7D32'
                });
                return;
            }
 
            btnSearch.disabled = true;
            btnSearch.textContent = "Mencari...";
 
            try {
                const response = await fetch(searchUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ query: query })
                });
 
                const result = await response.json();
 
                if (result.success) {
                    selectedTeacher = result.teacher;
                    txtTeacherName.textContent = selectedTeacher.name;
                    
                    // Advance to Step 2 (Camera)
                    stepSearch.classList.add('hidden');
                    stepCamera.classList.remove('hidden');
                    
                    // Init Camera and Location
                    initLocation();
                    startCamera();
                } else {
                    throw new Error(result.message);
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Pendidik Tidak Ditemukan',
                    text: err.message || 'Data pendidik tidak terdaftar pada unit perangkat ini.',
                    confirmButtonColor: '#2E7D32'
                });
                btnSearch.disabled = false;
                btnSearch.textContent = "Mulai Presensi Selfie";
            }
        });
 
        async function startCamera() {
            loader.classList.remove('hidden');
            loaderText.textContent = "Meminta akses kamera...";
            statusText.textContent = "Meminta akses kamera...";
            statusIcon.textContent = "video_camera_front";
            isCameraReady = false;

            const cameraTimeout = setTimeout(() => {
                if (!isCameraReady) {
                    if (webcamStream) {
                        webcamStream.getTracks().forEach(track => track.stop());
                        webcamStream = null;
                    }
                    loaderText.textContent = "Kamera gagal aktif (Timeout 10s).";
                    statusText.textContent = "Kamera gagal";
                    statusIcon.textContent = "videocam_off";
                    Swal.fire({
                        icon: 'error',
                        title: 'Kamera Timeout',
                        text: 'Kamera gagal aktif dalam 10 detik. Pastikan izin kamera telah diberikan.',
                        confirmButtonText: 'Coba Lagi',
                        confirmButtonColor: '#2E7D32'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            }, 10000);

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                clearTimeout(cameraTimeout);
                loaderText.textContent = "Browser tidak mendukung akses kamera.";
                statusText.textContent = "Kamera gagal";
                statusIcon.textContent = "videocam_off";
                Swal.fire({
                    icon: 'error',
                    title: 'Kamera Tidak Didukung',
                    text: 'Browser Anda tidak mendukung akses kamera. Silakan gunakan Chrome/Safari versi terbaru.',
                    confirmButtonColor: '#2E7D32'
                });
                return;
            }

            try {
                webcamStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 640, height: 480, facingMode: "user" },
                    audio: false 
                });
                video.srcObject = webcamStream;
                
                video.onloadedmetadata = () => {
                    video.play();
                    isCameraReady = true;
                    clearTimeout(cameraTimeout);
                    
                    loader.classList.add('hidden');
                    statusText.textContent = "Kamera aktif. Silakan jepret foto untuk presensi.";
                    statusIcon.textContent = "photo_camera";
                    
                    const btnCapture = document.getElementById('btn-capture-selfie');
                    btnCapture.disabled = false;
                    btnCapture.classList.remove('bg-slate-400', 'cursor-not-allowed');
                    btnCapture.classList.add('bg-primary', 'hover:bg-primary/95');
                };
            } catch (err) {
                clearTimeout(cameraTimeout);
                console.error("Camera access failed:", err);
                
                let errMsg = "Gagal mengakses kamera perangkat.";
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    errMsg = "Izin kamera ditolak oleh pengguna atau sistem.";
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    errMsg = "Kamera sedang digunakan oleh aplikasi/tab lain.";
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    errMsg = "Kamera tidak ditemukan pada perangkat Anda.";
                }
                
                loaderText.textContent = errMsg;
                statusText.textContent = "Kamera gagal";
                statusIcon.textContent = "videocam_off";
                
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Kamera Gagal',
                    text: errMsg,
                    confirmButtonText: 'Coba Lagi',
                    confirmButtonColor: '#2E7D32'
                }).then(() => {
                    window.location.reload();
                });
            }
        }


  
        // Add selfie capture and watermark click listener
        document.getElementById('btn-capture-selfie').addEventListener('click', async () => {
            if (!isCameraReady) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Kamera belum aktif. Silakan tunggu.',
                    confirmButtonColor: '#2E7D32'
                });
                return;
            }

            const btnCapture = document.getElementById('btn-capture-selfie');
            btnCapture.disabled = true;
            btnCapture.innerHTML = '<span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin shrink-0"></span> Memproses...';
  
            // 1. Capture and mirror video frame (standard mirrored selfie)
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = video.videoWidth || 640;
            tempCanvas.height = video.videoHeight || 480;
            const tempCtx = tempCanvas.getContext('2d');
  
            tempCtx.translate(tempCanvas.width, 0);
            tempCtx.scale(-1, 1);
            tempCtx.drawImage(video, 0, 0, tempCanvas.width, tempCanvas.height);
            tempCtx.setTransform(1, 0, 0, 1, 0, 0);
  
            // 2. Watermark the captured photo with Unit and Time
            statusText.textContent = "Menempelkan watermark...";
            statusIcon.textContent = "draw";
  
            const watermarkCanvas = document.createElement('canvas');
            watermarkCanvas.width = tempCanvas.width;
            watermarkCanvas.height = tempCanvas.height;
            const wCtx = watermarkCanvas.getContext('2d');
  
            // Draw captured selfie
            wCtx.drawImage(tempCanvas, 0, 0);
  
            // Draw a translucent black overlay at the bottom
            const rectHeight = 65;
            wCtx.fillStyle = 'rgba(0, 0, 0, 0.6)';
            wCtx.fillRect(0, watermarkCanvas.height - rectHeight, watermarkCanvas.width, rectHeight);
  
            // Draw text
            wCtx.fillStyle = '#FFFFFF';
            wCtx.font = 'bold 16px sans-serif';
            wCtx.textBaseline = 'top';
  
            const unitName = selectedTeacher ? selectedTeacher.unit_name : "{{ $unit?->name ?? 'PKBM Ibadurrahman' }}";
            wCtx.fillText(unitName, 15, watermarkCanvas.height - rectHeight + 10);
  
            wCtx.font = '14px sans-serif';
            
            // Format dynamic date and time using server time offset
            const now = new Date(Date.now() + serverTimeOffset);
            const pad = (n) => String(n).padStart(2, '0');
            const dateString = pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear();
            const timeString = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds()) + ' WIB';
            wCtx.fillText(dateString + ' ' + timeString, 15, watermarkCanvas.height - rectHeight + 35);
  
            const base64Selfie = watermarkCanvas.toDataURL('image/jpeg', 0.85);
  
            // 3. Submit to backend
            statusText.textContent = "Memverifikasi data & lokasi...";
            statusIcon.textContent = "autorenew";
            
            submitBiometricAttendance(base64Selfie);
        });
  
        // Send selfie verification and GPS to backend
        async function submitBiometricAttendance(base64Selfie) {
            if (!selectedTeacher) return;
  
            // Get selected action_type
            const actionType = document.querySelector('input[name="action_type"]:checked').value;
  
            // If location coords are not fetched yet, wait a moment or fail
            if (!hasGps) {
                statusText.textContent = "Gagal memproses. Lokasi GPS tidak aktif.";
                statusIcon.textContent = "error";
                Swal.fire({
                    icon: 'error',
                    title: 'Verifikasi Gagal',
                    text: 'Lokasi GPS perangkat belum didapatkan. Pastikan izin lokasi aktif.',
                    confirmButtonColor: '#2E7D32'
                }).then(() => window.location.reload());
                return;
            }
  
            try {
                const response = await fetch(attendanceUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        teacher_id: selectedTeacher.id,
                        latitude: locationCoords.latitude,
                        longitude: locationCoords.longitude,
                        accuracy: locationCoords.accuracy,
                        action_type: actionType,
                        selfie: base64Selfie,
                        face_descriptor: []
                    })
                });
  
                const result = await response.json();
  
                // Stop Webcam stream
                if (webcamStream) {
                    webcamStream.getTracks().forEach(track => track.stop());
                }
                if (detectionInterval) {
                    clearInterval(detectionInterval);
                }
  
                if (result.success) {
                    // Populate Step 3 Success UI
                    document.getElementById('result-name').textContent = result.teacher_name;
                    document.getElementById('result-unit').textContent = result.unit_name;
                    document.getElementById('result-time').textContent = result.time + " WIB";
                    
                    const statusLabel = document.getElementById('result-status-label');
                    const statusTextElem = document.getElementById('result-status');
                    
                    if (result.type === 'check_in') {
                        statusLabel.textContent = "Status Masuk";
                        statusTextElem.textContent = result.message.includes("Reward") ? "Tepat Waktu (🏆 Reward)" : "Tepat Waktu";
                        statusTextElem.className = "text-emerald-700 font-bold";
                        if (result.message.toLowerCase().includes("terlambat")) {
                            statusTextElem.textContent = "Terlambat";
                            statusTextElem.className = "text-red-600 font-bold";
                        }
                    } else {
                        statusLabel.textContent = "Status Pulang";
                        statusTextElem.textContent = result.message.toLowerCase().includes("awal") ? "Pulang Lebih Awal" : "Normal";
                        statusTextElem.className = result.message.toLowerCase().includes("awal") ? "text-amber-600 font-bold" : "text-emerald-700 font-bold";
                    }
  
                    // Show step 3
                    stepCamera.classList.add('hidden');
                    stepResult.classList.remove('hidden');
  
                    // Auto-refresh after 8 seconds to prevent screen hogging
                    setTimeout(() => {
                        window.location.reload();
                    }, 8000);
                } else {
                    throw new Error(result.message);
                }
            } catch (err) {
                console.error(err);
                let readableMsg = err.message || 'Verifikasi presensi atau lokasi GPS ditolak.';
                if (err.message === 'FACE_NOT_ENROLLED') {
                    readableMsg = "Wajah Anda belum terdaftar. Silakan hubungi admin.";
                } else if (err.message === 'FACE_NOT_MATCHED') {
                    readableMsg = "Wajah tidak cocok dengan data yang terdaftar.";
                } else if (err.message === 'OUTSIDE_GEOFENCE') {
                    readableMsg = "Anda berada di luar area presensi.";
                } else if (err.message === 'GPS_ACCURACY_TOO_LOW') {
                    readableMsg = "Akurasi GPS kurang baik. Silakan tunggu hingga lokasi lebih akurat.";
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Presensi Gagal',
                    text: readableMsg,
                    confirmButtonColor: '#2E7D32'
                }).then(() => {
                    window.location.reload();
                });
            }
        }
  
        btnReset.addEventListener('click', () => {
            window.location.reload();
        });
  
        // Release camera when leaving page
        window.addEventListener('beforeunload', () => {
            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
            }
            if (detectionInterval) {
                clearInterval(detectionInterval);
            }
        });
    </script>
</x-layouts.auth>
