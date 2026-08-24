<x-layouts.admin>
    <x-slot:title>PKBM IBADURRAHMAN - Pengaturan Kehadiran</x-slot:title>

    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-background">Pengaturan Kehadiran</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">Atur parameter global, jam kerja, profil sekolah, dan metode absen utama.</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-outline-variant mb-8 gap-6 overflow-x-auto custom-scrollbar">
        <a href="{{ route('admin.settings.attendance') }}" class="pb-3 border-b-2 border-primary font-label-md text-label-md text-primary whitespace-nowrap">Aturan Umum</a>
        <a href="{{ route('admin.settings.gps') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Pengaturan GPS</a>
        <a href="{{ route('admin.settings.qr') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Tampilan QR Code</a>
        <a href="{{ route('admin.devices.index') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Perangkat Sekolah</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter font-sans">
        <!-- Rules Summary Card -->
        <div class="lg:col-span-4 card-layer-1 rounded-xl p-6 flex flex-col gap-6 h-fit bg-surface-container-lowest border border-outline-variant">
            <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 font-bold text-primary">
                <span class="material-symbols-outlined">schedule</span>
                Aturan Waktu Kerja
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between border-b border-outline-variant/30 pb-2">
                    <span class="text-on-surface-variant font-body-sm">Nama Sekolah</span>
                    <span class="font-label-sm text-on-surface text-right font-semibold">{{ $settings['school_name'] }}</span>
                </div>
                <div class="flex justify-between border-b border-outline-variant/30 pb-2">
                    <span class="text-on-surface-variant font-body-sm">Hari Kerja</span>
                    <span class="font-label-sm text-on-surface text-right font-semibold">{{ $settings['work_days'] }}</span>
                </div>
                <div class="flex justify-between border-b border-outline-variant/30 pb-2">
                    <span class="text-on-surface-variant font-body-sm">Mulai Masuk</span>
                    <span class="font-label-sm text-on-surface text-right">{{ substr($settings['work_start_time'], 0, 5) }} WIB</span>
                </div>
                <div class="flex justify-between border-b border-outline-variant/30 pb-2">
                    <span class="text-on-surface-variant font-body-sm">Batas Terlambat</span>
                    <span class="font-label-sm text-error text-right font-bold">> {{ substr($settings['late_threshold_time'], 0, 5) }} WIB</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant font-body-sm">Jam Pulang</span>
                    <span class="font-label-sm text-on-surface text-right font-semibold">
                        {{ substr($settings['work_end_time_start'], 0, 5) }} - {{ substr($settings['work_end_time_end'], 0, 5) }} WIB
                    </span>
                </div>
            </div>
            <div class="bg-surface-container rounded-lg p-4 border border-outline-variant/30 text-xs text-on-surface-variant leading-relaxed">
                * Kehadiran di atas jam {{ substr($settings['late_threshold_time'], 0, 5) }} WIB akan dicatat dengan status Terlambat.
            </div>
        </div>

        <!-- Settings Form Card -->
        <div class="lg:col-span-8 card-layer-1 rounded-xl p-6 bg-surface-container-lowest border border-outline-variant">
            <form action="{{ route('admin.settings.save') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Section 1: Profil Sekolah -->
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 font-bold mb-4">
                        <span class="material-symbols-outlined text-primary">domain</span>
                        Profil Sekolah
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <label class="font-label-md text-label-md text-on-surface mb-2" for="school_name">Nama Sekolah / Lembaga</label>
                            <input id="school_name" name="school_name" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="text" value="{{ $settings['school_name'] }}" required/>
                        </div>
                        <div class="flex flex-col">
                            <label class="font-label-md text-label-md text-on-surface mb-2" for="work_days">Hari Kerja Mingguan</label>
                            <input id="work_days" name="work_days" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="text" value="{{ $settings['work_days'] }}" required placeholder="Senin - Jumat"/>
                        </div>
                        <div class="flex flex-col md:col-span-2">
                            <label class="font-label-md text-label-md text-on-surface mb-2" for="school_address">Alamat Sekolah</label>
                            <textarea id="school_address" name="school_address" rows="2" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" required>{{ $settings['school_address'] }}</textarea>
                        </div>
                    </div>
                </div>

                <hr class="border-outline-variant/40">

                <!-- Section 2: Waktu Kerja & Kehadiran -->
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 font-bold mb-4">
                        <span class="material-symbols-outlined text-primary">schedule_send</span>
                        Waktu Kerja & Presensi
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="flex flex-col">
                            <label class="font-label-md text-label-md text-on-surface mb-2" for="work_start_time">Mulai Masuk</label>
                            <input id="work_start_time" name="work_start_time" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="time" value="{{ substr($settings['work_start_time'], 0, 5) }}" required/>
                        </div>
                        <div class="flex flex-col">
                            <label class="font-label-md text-label-md text-on-surface mb-2" for="late_threshold_time">Batas Terlambat</label>
                            <input id="late_threshold_time" name="late_threshold_time" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="time" value="{{ substr($settings['late_threshold_time'], 0, 5) }}" required/>
                        </div>
                        <div class="flex flex-col">
                            <label class="font-label-md text-label-md text-on-surface mb-2" for="work_end_time_start">Jam Pulang Mulai</label>
                            <input id="work_end_time_start" name="work_end_time_start" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="time" value="{{ substr($settings['work_end_time_start'], 0, 5) }}" required/>
                        </div>
                        <div class="flex flex-col">
                            <label class="font-label-md text-label-md text-on-surface mb-2" for="work_end_time_end">Jam Pulang Selesai</label>
                            <input id="work_end_time_end" name="work_end_time_end" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="time" value="{{ substr($settings['work_end_time_end'], 0, 5) }}" required/>
                        </div>
                    </div>
                </div>

                <hr class="border-outline-variant/40">

                <!-- Section 3: Konfigurasi Denda & QR -->
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 font-bold mb-4">
                        <span class="material-symbols-outlined text-primary">tune</span>
                        Konfigurasi Sistem
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Late Penalty Field (Hidden) -->
                        <input id="late_penalty_nominal" name="late_penalty_nominal" type="hidden" value="0"/>

                        <!-- QR Code Refresh Interval Field -->
                        <div class="flex flex-col">
                            <label class="font-label-md text-label-md text-on-surface mb-2" for="qr_rotation_interval">Interval Refresh QR (detik)</label>
                            <div class="relative w-full">
                                <input id="qr_rotation_interval" name="qr_rotation_interval" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 pr-16 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="number" value="{{ $settings['qr_rotation_interval'] }}" required min="10" max="300"/>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 font-label-sm text-on-surface-variant">detik</span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant mt-1">Durasi rotasi token QR Code pengaman.</span>
                        </div>

                        <!-- Main Attendance Method Selection -->
                        <div class="flex flex-col md:col-span-2">
                            <label class="font-label-md text-label-md text-on-surface mb-2" for="attendance_method">Metode Presensi Utama</label>
                            <div class="relative w-full">
                                <select id="attendance_method" name="attendance_method" class="appearance-none w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer">
                                    <option value="gps" {{ $settings['attendance_method'] === 'gps' ? 'selected' : '' }}>GPS Geolocation (Radius Sekolah)</option>
                                    <option value="qr" {{ $settings['attendance_method'] === 'qr' ? 'selected' : '' }}>Scan QR Code Admin</option>
                                    <option value="face_id" {{ $settings['attendance_method'] === 'face_id' ? 'selected' : '' }}>Biometrik Face ID</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant mt-1">Metode utama yang akan aktif secara default saat tenaga pendidik membuka halaman absen.</span>
                        </div>
                    </div>
                </div>

                <hr class="border-outline-variant/40">

                <!-- Section 4: Jadwal Harian Unit -->
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 font-bold mb-4">
                        <span class="material-symbols-outlined text-primary font-bold">calendar_month</span>
                        Jadwal Kerja Harian Unit
                    </h3>
                    <div class="overflow-x-auto border border-outline-variant/50 rounded-xl">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-[#F7FAF7] border-b border-[#E6ECE7]">
                                    <th class="py-2.5 px-4 font-semibold text-on-surface-variant">Hari</th>
                                    <th class="py-2.5 px-4 font-semibold text-on-surface-variant text-center">Aktif</th>
                                    <th class="py-2.5 px-4 font-semibold text-on-surface-variant">Mulai Masuk</th>
                                    <th class="py-2.5 px-4 font-semibold text-on-surface-variant">Batas Reward</th>
                                    <th class="py-2.5 px-4 font-semibold text-on-surface-variant">Batas Tepat Waktu</th>
                                    <th class="py-2.5 px-4 font-semibold text-on-surface-variant">Jam Pulang</th>
                                    <th class="py-2.5 px-4 font-semibold text-on-surface-variant">Pulang Akhir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/30 bg-white">
                                @foreach($schedules as $sched)
                                    <tr class="hover:bg-[#F7FAF7] transition-colors">
                                        <td class="py-3 px-4 font-bold text-on-surface capitalize">
                                            @if($sched->day_of_week === 'monday') Senin
                                            @elseif($sched->day_of_week === 'tuesday') Selasa
                                            @elseif($sched->day_of_week === 'wednesday') Rabu
                                            @elseif($sched->day_of_week === 'thursday') Kamis
                                            @elseif($sched->day_of_week === 'friday') Jumat
                                            @elseif($sched->day_of_week === 'saturday') Sabtu
                                            @elseif($sched->day_of_week === 'sunday') Minggu
                                            @else {{ $sched->day_of_week }}
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <input type="checkbox" name="schedules[{{ $sched->day_of_week }}][is_active]" value="1" {{ $sched->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                        </td>
                                        <td class="py-3 px-4">
                                            <input type="time" name="schedules[{{ $sched->day_of_week }}][work_start_time]" value="{{ substr($sched->work_start_time, 0, 5) }}" class="border border-outline-variant rounded-lg p-1.5 focus:outline-none focus:border-primary">
                                        </td>
                                        <td class="py-3 px-4">
                                            <input type="time" name="schedules[{{ $sched->day_of_week }}][reward_limit_time]" value="{{ substr($sched->reward_limit_time, 0, 5) }}" class="border border-outline-variant rounded-lg p-1.5 focus:outline-none focus:border-primary">
                                        </td>
                                        <td class="py-3 px-4">
                                            <input type="time" name="schedules[{{ $sched->day_of_week }}][late_threshold_time]" value="{{ substr($sched->late_threshold_time, 0, 5) }}" class="border border-outline-variant rounded-lg p-1.5 focus:outline-none focus:border-primary">
                                        </td>
                                        <td class="py-3 px-4">
                                            <input type="time" name="schedules[{{ $sched->day_of_week }}][work_end_time]" value="{{ substr($sched->work_end_time, 0, 5) }}" class="border border-outline-variant rounded-lg p-1.5 focus:outline-none focus:border-primary">
                                        </td>
                                        <td class="py-3 px-4">
                                            <input type="time" name="schedules[{{ $sched->day_of_week }}][work_end_time_end]" value="{{ substr($sched->work_end_time_end, 0, 5) }}" class="border border-outline-variant rounded-lg p-1.5 focus:outline-none focus:border-primary">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submit Action -->
                <div class="pt-4 border-t border-outline-variant/30 flex justify-end">
                    <button class="btn-primary px-6 py-2.5 font-label-md text-label-md hover:bg-primary-container/90 transition-all flex items-center gap-2 active:scale-95 shadow-sm text-white bg-primary rounded-xl" type="submit">
                        <span class="material-symbols-outlined">save</span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification script using SweetAlert -->
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#2E7D32',
                timer: 3000
            });
        @endif
    </script>
</x-layouts.admin>
