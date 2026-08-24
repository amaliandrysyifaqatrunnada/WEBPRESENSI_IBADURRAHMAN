<x-layouts.admin>
    <x-slot:title>PKBM IBADURRAHMAN - Registrasi Face ID</x-slot:title>

    <div class="max-w-4xl mx-auto font-sans">
        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <a href="{{ route('admin.teachers.index') }}" class="flex items-center gap-1 text-primary hover:underline font-label-md text-label-md mb-2">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Data Guru
                </a>
                <h2 class="font-headline-lg text-headline-lg text-on-background">Registrasi Face ID</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Daftarkan biometrik wajah untuk: <strong>{{ $teacher->name }}</strong></p>
            </div>
            
            @if($teacher->face_registered)
                <form action="{{ route('admin.teachers.face-id.delete', $teacher->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data biometrik wajah guru ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-label-md transition-colors flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined">delete_forever</span>
                        Hapus Biometrik Wajah
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
            <!-- Camera & Verification Challenge -->
            <div class="md:col-span-7 card-layer-1 rounded-xl p-6 bg-surface-container-lowest border border-outline-variant flex flex-col items-center">
                <h3 class="font-headline-sm text-headline-sm text-on-surface mb-4 self-start font-bold">Kamera Perekam Wajah</h3>

                <!-- Camera Stream Outer Frame -->
                <div class="w-full aspect-[4/3] max-w-[480px] bg-slate-900 rounded-xl overflow-hidden relative shadow-inner border border-outline-variant">
                    <video id="webcam" autoplay muted playsinline class="w-full h-full object-cover scale-x-[-1]"></video>
                    <canvas id="overlay" class="absolute inset-0 w-full h-full object-cover scale-x-[-1]"></canvas>

                    <!-- Loading Screen -->
                    <div id="loader" class="absolute inset-0 bg-slate-950/80 flex flex-col items-center justify-center text-white p-4 text-center">
                        <div class="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="font-label-md" id="loader-text">Mengunduh model face-api.js...</p>
                    </div>

                    <!-- Instructions Panel Overlay -->
                    <div class="absolute bottom-4 left-4 right-4 bg-black/70 backdrop-blur-sm rounded-lg p-3 text-white text-xs flex justify-between items-center" id="instruction-panel">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-400 animate-pulse" id="status-icon">settings_overscan</span>
                            <span id="status-text">Menginisialisasi kamera...</span>
                        </div>
                    </div>
                </div>

                <div class="w-full mt-6 space-y-3" id="challenge-list-container">
                    <h4 class="font-label-md text-label-md text-on-surface font-semibold">Tantangan Keaktifan (Liveness Challenges):</h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div id="challenge-mouth" class="p-3 border border-outline-variant/60 rounded-xl flex items-center gap-2 bg-surface-container-low text-on-surface-variant font-medium text-xs">
                            <span class="material-symbols-outlined text-sm" id="icon-mouth">sentiment_very_satisfied</span>
                            <span>1. Buka Mulut</span>
                        </div>
                        <div id="challenge-left" class="p-3 border border-outline-variant/60 rounded-xl flex items-center gap-2 bg-surface-container-low text-on-surface-variant font-medium text-xs">
                            <span class="material-symbols-outlined text-sm" id="icon-left">arrow_back</span>
                            <span>2. Hadap Kiri</span>
                        </div>
                        <div id="challenge-right" class="p-3 border border-outline-variant/60 rounded-xl flex items-center gap-2 bg-surface-container-low text-on-surface-variant font-medium text-xs">
                            <span class="material-symbols-outlined text-sm" id="icon-right">arrow_forward</span>
                            <span>3. Hadap Kanan</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button (Disabled until challenges pass) -->
                <button id="btn-submit-face" disabled class="w-full mt-6 py-3.5 bg-slate-400 text-white rounded-xl font-label-md transition-all flex items-center justify-center gap-2 cursor-not-allowed">
                    <span class="material-symbols-outlined">fingerprint</span>
                    Simpan Biometrik Wajah
                </button>
            </div>

            <!-- Profile and Info Card -->
            <div class="md:col-span-5 flex flex-col gap-6">
                <div class="card-layer-1 rounded-xl p-6 bg-surface-container-lowest border border-outline-variant">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-4 font-bold text-primary">Informasi Pendidik</h3>
                    
                    <div class="space-y-4">
                        <div class="flex flex-col border-b border-outline-variant/30 pb-2">
                            <span class="text-xs text-on-surface-variant font-medium">Nama Lengkap</span>
                            <span class="font-label-md text-on-surface mt-0.5 font-bold">{{ $teacher->name }}</span>
                        </div>
                        <div class="flex flex-col border-b border-outline-variant/30 pb-2">
                            <span class="text-xs text-on-surface-variant font-medium">NIP / ID</span>
                            <span class="font-body-md text-on-surface mt-0.5">{{ $teacher->nip ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col border-b border-outline-variant/30 pb-2">
                            <span class="text-xs text-on-surface-variant font-medium">Unit / Cabang</span>
                            <span class="font-body-md text-on-surface mt-0.5">{{ $teacher->unit->name ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-on-surface-variant font-medium">Status Registrasi</span>
                            @if($teacher->face_registered)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#E8F5E9] text-[#2E7D32] border border-[#C8E6C9] mt-1.5 w-fit">
                                    <span class="material-symbols-outlined text-xs">verified</span>
                                    Terdaftar pada {{ $teacher->face_registered_at ? $teacher->face_registered_at->isoFormat('D MMM YYYY, H:mm') : '-' }} WIB
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200 mt-1.5 w-fit">
                                    <span class="material-symbols-outlined text-xs">info</span>
                                    Belum Terdaftar
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-layer-1 rounded-xl p-6 bg-surface-container-lowest border border-outline-variant">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-3 font-bold">Kebijakan Privasi</h3>
                    <p class="text-xs text-on-surface-variant leading-relaxed mb-3">
                        Data wajah digunakan khusus untuk verifikasi kehadiran tenaga pendidik PKBM Ibadurrahman.
                    </p>
                    <p class="text-xs text-on-surface-variant leading-relaxed">
                        Data biometrik disimpan dalam format template matematika (embedding) terenkripsi di server dan tidak memuat raw image foto wajah.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Include vladmandic face-api.js from jsdelivr CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
    <script>
        const teacherId = "{{ $teacher->id }}";
        const registerUrl = "{{ route('admin.teachers.face-id.register', $teacher->id) }}";
        
        let webcamStream = null;
        let isModelsLoaded = false;
        let finalFaceDescriptor = null;

        // Challenge statuses
        let challenges = {
            mouth: { done: false, active: false },
            lookLeft: { done: false, active: false },
            lookRight: { done: false, active: false }
        };

        const video = document.getElementById('webcam');
        const canvas = document.getElementById('overlay');
        const loader = document.getElementById('loader');
        const loaderText = document.getElementById('loader-text');
        const statusText = document.getElementById('status-text');
        const statusIcon = document.getElementById('status-icon');
        const btnSubmit = document.getElementById('btn-submit-face');

        async function init() {
            try {
                loaderText.textContent = "Memuat model biometrik wajah...";
                
                // Load faceapi models locally from public/models path
                await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
                await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
                await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

                isModelsLoaded = true;
                loaderText.textContent = "Mengaktifkan kamera...";
                
                startCamera();
            } catch (err) {
                console.error("Gagal menginisialisasi model:", err);
                loaderText.textContent = "Gagal memuat model. Hubungkan internet.";
                Swal.fire({
                    icon: 'error',
                    title: 'Model Gagal Dimuat',
                    text: 'Tidak dapat memuat weights file dari server. Periksa jaringan Anda.',
                    confirmButtonColor: '#2E7D32'
                });
            }
        }

        async function startCamera() {
            try {
                webcamStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 640, height: 480, facingMode: "user" },
                    audio: false 
                });
                video.srcObject = webcamStream;
                video.addEventListener('play', onPlay);
            } catch (err) {
                console.error("Akses kamera ditolak:", err);
                loaderText.textContent = "Akses kamera ditolak.";
                Swal.fire({
                    icon: 'error',
                    title: 'Kamera Gagal Dibuka',
                    text: 'Aplikasi memerlukan akses kamera untuk merekam biometrik wajah.',
                    confirmButtonColor: '#2E7D32'
                });
            }
        }

        // Mouth Aspect Ratio (MAR) calculator using inner lips
        function getMAR(landmarks) {
            const innerLipTop = landmarks[62];
            const innerLipBottom = landmarks[66];
            const innerLipLeft = landmarks[60];
            const innerLipRight = landmarks[64];
            
            const verticalDist = Math.hypot(innerLipTop.x - innerLipBottom.x, innerLipTop.y - innerLipBottom.y);
            const horizontalDist = Math.hypot(innerLipLeft.x - innerLipRight.x, innerLipLeft.y - innerLipRight.y);
            
            return verticalDist / (horizontalDist || 0.001);
        }

        // Yaw angle calculator based on eye-nose distance ratio
        function getNoseYawRatio(landmarks) {
            const noseTip = landmarks[30];
            const leftEye = landmarks[36]; // outer corner
            const rightEye = landmarks[45]; // outer corner
            
            const distLeft = Math.abs(noseTip.x - leftEye.x);
            const distRight = Math.abs(rightEye.x - noseTip.x);
            
            return distLeft / (distRight || 0.001);
        }

        async function onPlay() {
            loader.classList.add('hidden');
            statusText.textContent = "Mencari wajah...";
            statusIcon.textContent = "face";
            
            // Set up overlay canvas size
            const displaySize = { width: video.videoWidth, height: video.videoHeight };
            faceapi.matchDimensions(canvas, displaySize);

            // Active first challenge
            challenges.mouth.active = true;
            updateChallengeUI();

            const interval = setInterval(async () => {
                if (video.paused || video.ended || !isModelsLoaded) return;

                const detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detections) {
                    // Draw guide box
                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

                    const landmarks = detections.landmarks.positions;
                    finalFaceDescriptor = detections.descriptor; // Save newest descriptor

                    // 1. Mouth Challenge
                    if (challenges.mouth.active && !challenges.mouth.done) {
                        statusText.textContent = "Silakan BUKA MULUT Anda.";
                        statusIcon.textContent = "sentiment_very_satisfied";

                        const mar = getMAR(landmarks);

                        if (mar > 0.35) {
                            challenges.mouth.done = true;
                            challenges.mouth.active = false;
                            challenges.lookLeft.active = true;
                            updateChallengeUI();
                            Swal.fire({
                                title: 'Tantangan 1 Selesai',
                                text: 'Mulut terbuka terdeteksi!',
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        }
                    }

                    // 2. Turn Head Left Challenge
                    else if (challenges.lookLeft.active && !challenges.lookLeft.done) {
                        statusText.textContent = "Silakan HADAP KIRI sedikit.";
                        statusIcon.textContent = "arrow_back";

                        const yawRatio = getNoseYawRatio(landmarks);
                        // Left turn (towards user's left, which is camera's right)
                        if (yawRatio > 2.2) {
                            challenges.lookLeft.done = true;
                            challenges.lookLeft.active = false;
                            challenges.lookRight.active = true;
                            updateChallengeUI();
                            Swal.fire({
                                title: 'Tantangan 2 Selesai',
                                text: 'Berhasil hadap kiri!',
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        }
                    }

                    // 3. Turn Head Right Challenge
                    else if (challenges.lookRight.active && !challenges.lookRight.done) {
                        statusText.textContent = "Silakan HADAP KANAN sedikit.";
                        statusIcon.textContent = "arrow_forward";

                        const yawRatio = getNoseYawRatio(landmarks);
                        // Right turn (towards user's right, which is camera's left)
                        if (yawRatio < 0.45) {
                            challenges.lookRight.done = true;
                            challenges.lookRight.active = false;
                            updateChallengeUI();
                            statusText.textContent = "Tantangan selesai! Siap menyimpan.";
                            statusIcon.textContent = "check_circle";

                            // Enable Save Button
                            btnSubmit.disabled = false;
                            btnSubmit.classList.remove('bg-slate-400', 'cursor-not-allowed');
                            btnSubmit.classList.add('bg-primary', 'hover:bg-primary/95');

                            clearInterval(interval);
                            Swal.fire({
                                title: 'Tantangan Selesai!',
                                text: 'Semua tantangan keaktifan wajah berhasil dilewati.',
                                icon: 'success',
                                confirmButtonColor: '#2E7D32'
                            });
                        }
                    }
                } else {
                    statusText.textContent = "Mencari wajah... Pastikan wajah terlihat jelas.";
                    statusIcon.textContent = "face_5";
                }
            }, 100);
        }

        function updateChallengeUI() {
            const chMouth = document.getElementById('challenge-mouth');
            const chLeft = document.getElementById('challenge-left');
            const chRight = document.getElementById('challenge-right');

            const iconMouth = document.getElementById('icon-mouth');
            const iconLeft = document.getElementById('icon-left');
            const iconRight = document.getElementById('icon-right');

            // Mouth
            if (challenges.mouth.done) {
                chMouth.classList.add('bg-emerald-100', 'text-emerald-800', 'border-emerald-300');
                iconMouth.textContent = 'check_circle';
                iconMouth.classList.add('text-emerald-700');
            } else if (challenges.mouth.active) {
                chMouth.classList.add('border-primary', 'bg-primary-container/10', 'text-primary');
            }

            // Left
            if (challenges.lookLeft.done) {
                chLeft.classList.add('bg-emerald-100', 'text-emerald-800', 'border-emerald-300');
                iconLeft.textContent = 'check_circle';
                iconLeft.classList.add('text-emerald-700');
            } else if (challenges.lookLeft.active) {
                chLeft.classList.add('border-primary', 'bg-primary-container/10', 'text-primary');
            }

            // Right
            if (challenges.lookRight.done) {
                chRight.classList.add('bg-emerald-100', 'text-emerald-800', 'border-emerald-300');
                iconRight.textContent = 'check_circle';
                iconRight.classList.add('text-emerald-700');
            } else if (challenges.lookRight.active) {
                chRight.classList.add('border-primary', 'bg-primary-container/10', 'text-primary');
            }
        }

        // Post Face Template Array to server
        btnSubmit.addEventListener('click', async () => {
            if (!finalFaceDescriptor) return;

            btnSubmit.disabled = true;
            btnSubmit.textContent = "Menyimpan biometrik...";

            try {
                // Convert Float32Array to standard array
                const descArray = Array.from(finalFaceDescriptor);
                
                const response = await fetch(registerUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ face_template: descArray })
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        title: 'Registrasi Berhasil',
                        text: result.message,
                        icon: 'success',
                        confirmButtonColor: '#2E7D32'
                    }).then(() => {
                        window.location.href = "{{ route('admin.teachers.index') }}";
                    });
                } else {
                    throw new Error(result.message);
                }
            } catch (err) {
                console.error("Gagal mendaftarkan wajah:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: err.message || 'Terjadi kesalahan sistem saat menyimpan data biometrik.',
                    confirmButtonColor: '#2E7D32'
                });
                btnSubmit.disabled = false;
                btnSubmit.textContent = "Simpan Biometrik Wajah";
            }
        });

        // Initialize script
        init();

        // Release camera when leaving page
        window.addEventListener('beforeunload', () => {
            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</x-layouts.admin>
