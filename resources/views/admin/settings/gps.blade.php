<x-layouts.admin>
    <x-slot:title>PKBM IBADURRAHMAN - Pengaturan GPS</x-slot:title>

    <!-- Leaflet.js assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-background">Pengaturan Kehadiran</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">Atur koordinat pusat sekolah dan radius toleransi presensi berbasis lokasi.</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-outline-variant mb-8 gap-6 overflow-x-auto custom-scrollbar">
        <a href="{{ route('admin.settings.attendance') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Aturan Umum</a>
        <a href="{{ route('admin.settings.gps') }}" class="pb-3 border-b-2 border-primary font-label-md text-label-md text-primary whitespace-nowrap font-bold">Pengaturan GPS</a>
        <a href="{{ route('admin.settings.qr') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Tampilan QR Code</a>
        <a href="{{ route('admin.devices.index') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Perangkat Sekolah</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        <!-- Map Canvas (Drag and drop) -->
        <div class="lg:col-span-7 card-layer-1 rounded-xl p-4 h-[450px] relative overflow-hidden flex flex-col">
            <h3 class="font-label-md text-label-md text-on-surface mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">map</span>
                Peta Koordinat Sekolah (Geser Pin untuk Memindahkan)
            </h3>
            <div id="settings-map" class="w-full flex-1 rounded-lg border border-outline-variant"></div>
        </div>

        <!-- Form Configurations -->
        <div class="lg:col-span-5 card-layer-1 rounded-xl p-6 flex flex-col justify-between">
            <form action="{{ route('admin.settings.save') }}" method="POST" class="space-y-5" id="gpsForm">
                @csrf
                <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 mb-2 font-bold">
                    <span class="material-symbols-outlined text-primary">my_location</span>
                    Titik Koordinat
                </h3>

                <!-- Unlock button -->
                <button type="button" id="btn-unlock-location" onclick="unlockLocation()" class="w-full bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-colors focus:outline-none">
                    <span class="material-symbols-outlined text-[18px]">lock</span>
                    Ubah Lokasi (Buka Kunci Marker)
                </button>

                <!-- Gunakan Lokasi Saya button -->
                <button type="button" id="btn-my-location" onclick="useMyLocation()" class="w-full bg-[#E8F5E9] hover:bg-[#C8E6C9] text-[#2E7D32] border border-[#A5D6A7] py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-colors focus:outline-none mt-2">
                    <span class="material-symbols-outlined text-[18px]">my_location</span>
                    Gunakan Lokasi Saya
                </button>

                <!-- Latitude Field -->
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2" for="school_latitude">Latitude Pusat</label>
                    <input id="school_latitude" name="school_latitude" class="w-full bg-slate-50 border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-not-allowed" type="text" value="{{ $settings['latitude'] }}" readonly required oninput="syncMapFromInputs()"/>
                </div>

                <!-- Longitude Field -->
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2" for="school_longitude">Longitude Pusat</label>
                    <input id="school_longitude" name="school_longitude" class="w-full bg-slate-50 border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-not-allowed" type="text" value="{{ $settings['longitude'] }}" readonly required oninput="syncMapFromInputs()"/>
                </div>

                <!-- Radius Field -->
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2" for="school_geofence_radius">Radius Toleransi Sekolah (meter)</label>
                    <div class="relative">
                        <input id="school_geofence_radius" name="school_geofence_radius" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 pr-12 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="number" value="{{ $settings['radius'] }}" required min="1" max="500" oninput="syncMapFromInputs()"/>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 font-label-sm text-on-surface-variant">meter</span>
                    </div>
                    <span class="text-xs text-on-surface-variant mt-1">Sesuai peraturan sekolah, direkomendasikan maksimal <b>2 meter</b>.</span>
                </div>

                <!-- GPS Accuracy Threshold Field -->
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2" for="gps_accuracy_threshold">Toleransi Akurasi Sinyal GPS (meter)</label>
                    <div class="relative">
                        <input id="gps_accuracy_threshold" name="gps_accuracy_threshold" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 pr-12 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="number" value="{{ $settings['gps_accuracy_threshold'] ?? 50 }}" required min="5" max="5000"/>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 font-label-sm text-on-surface-variant">meter</span>
                    </div>
                    <span class="text-xs text-on-surface-variant mt-1">Gunakan nilai lebih besar (misal 150m - 200m) jika laptop/PC penguji melaporkan akurasi rendah.</span>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-outline-variant/30 flex justify-end">
                    <button class="btn-primary px-6 py-2.5 font-label-md text-label-md hover:bg-primary-container/90 transition-all flex items-center gap-2 active:scale-95 shadow-sm text-white bg-primary rounded-xl" type="submit">
                        <span class="material-symbols-outlined">save</span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Map Geolocation Sync Scripts -->
    <script>
        let map = null;
        let marker = null;
        let circle = null;

        function initMap() {
            let initialLat = parseFloat(document.getElementById('school_latitude').value);
            let initialLng = parseFloat(document.getElementById('school_longitude').value);
            const initialRadius = parseFloat(document.getElementById('school_geofence_radius').value);

            let isConfigured = !isNaN(initialLat) && !isNaN(initialLng);
            if (!isConfigured) {
                // Default to Sidoarjo coordinates but keep inputs unconfigured
                initialLat = -7.4535;
                initialLng = 112.7097;
            }

            // Set up OSM Tiles
            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            });

            // Set up Esri Satellite Tiles
            const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            });

            // Initialize Map with satellite default
            map = L.map('settings-map', {
                center: [initialLat, initialLng],
                zoom: 18,
                zoomControl: true,
                layers: [satellite]
            });

            // Add layer switcher control
            const baseMaps = {
                "Peta Satelit": satellite,
                "Peta Jalan": osm
            };
            L.control.layers(baseMaps).addTo(map);

            // Create Non-Draggable Marker
            marker = L.marker([initialLat, initialLng], {
                draggable: false
            });

            // Create Geofence Circle
            circle = L.circle([initialLat, initialLng], {
                color: '#2E7D32',
                fillColor: '#C8E6C9',
                fillOpacity: 0.3,
                radius: initialRadius
            });

            if (isConfigured) {
                marker.addTo(map);
                circle.addTo(map);
            } else {
                // Create unconfigured banner
                const banner = document.createElement('div');
                banner.id = 'gps-unconfigured-banner';
                banner.className = 'absolute top-4 left-1/2 -translate-x-1/2 z-[1000] bg-orange-600 text-white font-bold px-4 py-2 rounded-lg shadow-md border border-orange-700';
                banner.textContent = 'Status: Koordinat belum dikonfigurasi';
                document.getElementById('settings-map').appendChild(banner);
            }

            // Listen to marker drag events
            marker.on('dragend', function (event) {
                const position = marker.getLatLng();
                document.getElementById('school_latitude').value = position.lat.toFixed(6);
                document.getElementById('school_longitude').value = position.lng.toFixed(6);
                
                // Recenter circle
                circle.setLatLng(position);
                map.panTo(position);
            });
        }

        // Unlock Location marker and inputs
        function unlockLocation() {
            if (marker) {
                if (!map.hasLayer(marker)) {
                    // Add it to map center
                    const center = map.getCenter();
                    marker.setLatLng(center);
                    circle.setLatLng(center);
                    marker.addTo(map);
                    circle.addTo(map);
                    document.getElementById('school_latitude').value = center.lat.toFixed(6);
                    document.getElementById('school_longitude').value = center.lng.toFixed(6);

                    // Remove banner
                    const banner = document.getElementById('gps-unconfigured-banner');
                    if (banner) banner.remove();
                }

                marker.dragging.enable();
                
                const latInput = document.getElementById('school_latitude');
                const lngInput = document.getElementById('school_longitude');
                
                latInput.removeAttribute('readonly');
                latInput.classList.remove('bg-slate-50', 'cursor-not-allowed');
                latInput.classList.add('bg-white');
                
                lngInput.removeAttribute('readonly');
                lngInput.classList.remove('bg-slate-50', 'cursor-not-allowed');
                lngInput.classList.add('bg-white');
                
                const btn = document.getElementById('btn-unlock-location');
                btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">lock_open</span> Marker Dapat Digeser';
                btn.classList.remove('bg-orange-50', 'text-orange-700', 'border-orange-200', 'hover:bg-orange-100');
                btn.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                btn.disabled = true;
            }
        }

        function syncMapFromInputs() {
            const lat = parseFloat(document.getElementById('school_latitude').value);
            const lng = parseFloat(document.getElementById('school_longitude').value);
            const radius = parseFloat(document.getElementById('school_geofence_radius').value);

            if (!isNaN(lat) && !isNaN(lng) && map) {
                const newLatLng = new L.LatLng(lat, lng);
                
                if (!map.hasLayer(marker)) {
                    marker.addTo(map);
                    circle.addTo(map);
                    const banner = document.getElementById('gps-unconfigured-banner');
                    if (banner) banner.remove();
                }

                marker.setLatLng(newLatLng);
                circle.setLatLng(newLatLng);
                
                if (!isNaN(radius)) {
                    circle.setRadius(radius);
                }

                map.panTo(newLatLng);
            }
        }

        function useMyLocation() {
            if (navigator.geolocation) {
                // Show loading state on button
                const btn = document.getElementById('btn-my-location');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">sync</span> Mencari lokasi...';
                btn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        // Restore button
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;

                        // Unlock marker and inputs
                        unlockLocation();

                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        document.getElementById('school_latitude').value = lat.toFixed(6);
                        document.getElementById('school_longitude').value = lng.toFixed(6);

                        syncMapFromInputs();

                        Swal.fire({
                            icon: 'info',
                            title: 'Lokasi Terdeteksi',
                            text: 'Peta telah dipindahkan ke lokasi Anda. Klik tombol Simpan Perubahan untuk menyimpan koordinat baru.',
                            confirmButtonColor: '#2E7D32'
                        });
                    },
                    (error) => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                        
                        let msg = 'Gagal mendeteksi lokasi: izin ditolak.';
                        if (error.code === error.POSITION_UNAVAILABLE) {
                            msg = 'Lokasi tidak tersedia.';
                        } else if (error.code === error.TIMEOUT) {
                            msg = 'Waktu permintaan habis.';
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg,
                            confirmButtonColor: '#ba1a1a'
                        });
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Didukung',
                    text: 'Browser Anda tidak mendukung Geolocation API.',
                    confirmButtonColor: '#ba1a1a'
                });
            }
        }

        // Initialize leaflet map on page load
        window.onload = function() {
            initMap();
        };

        // Flash message notification
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#2E7D32',
                timer: 3000
            });
        @endif

        // Validation errors alert
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Validasi',
                html: '{!! implode("<br>", $errors->all()) !!}',
                confirmButtonColor: '#ba1a1a'
            });
        @endif
    </script>
</x-layouts.admin>
