<x-layouts.admin>
    <x-slot:title>Pengaturan Jadwal {{ $teacher->name }} - PKBM IBADURRAHMAN</x-slot:title>

    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Atur Jadwal Kerja Guru</h2>
            <p class="text-sm text-on-surface-variant mt-1">Konfigurasi jam masuk dan pulang individual untuk {{ $teacher->name }} ({{ $teacher->display_id }}).</p>
        </div>
        <a href="{{ route('admin.teachers.schedule.index') }}" class="px-4 py-2 bg-surface-container-high text-on-surface text-sm font-semibold rounded-xl hover:bg-surface-container-highest transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Kembali
        </a>
    </div>

    <form action="{{ route('admin.teachers.schedule.update', $teacher) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Mode Selection Box -->
        <div class="card-layer-1 rounded-2xl p-6 border border-outline-variant/30">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-on-surface">Gunakan Jadwal Custom Individual</h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">Jika nonaktif, guru ini akan mengikuti jam masuk & pulang default dari unit {{ $teacher->unit ? $teacher->unit->name : '-' }}.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="use_custom_schedule" value="1" {{ $teacher->use_custom_schedule ? 'checked' : '' }} class="sr-only peer" id="toggleCustomSchedule" onchange="toggleScheduleForm()">
                    <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
        </div>

        <!-- Weekly Schedule Table -->
        <div id="scheduleFormContainer" class="card-layer-1 rounded-2xl p-6 border border-outline-variant/30 transition-all {{ $teacher->use_custom_schedule ? '' : 'opacity-50 pointer-events-none' }}">
            <h3 class="text-lg font-bold text-on-surface mb-4">Rincian Jam Kerja per Hari</h3>

            @php
                $days = [
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                    7 => 'Minggu',
                ];
            @endphp

            <div class="space-y-4 divide-y divide-outline-variant/20">
                @foreach($days as $dayNum => $dayName)
                    @php
                        $sched = $schedules->get($dayNum);
                        $startTime = $sched ? substr($sched->start_time, 0, 5) : '07:00';
                        $endTime = $sched ? substr($sched->end_time, 0, 5) : ($dayNum == 6 ? '13:00' : '15:00');
                        $isActive = $sched ? $sched->is_active : ($dayNum <= 6);
                    @endphp
                    <div class="pt-4 first:pt-0 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="w-32">
                            <span class="font-bold text-sm text-on-surface">{{ $dayName }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 flex-1">
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-semibold text-on-surface-variant">Jam Masuk:</label>
                                <input type="time" name="days[{{ $dayNum }}][start_time]" value="{{ $startTime }}" class="bg-white border border-outline-variant rounded-xl px-3 py-1.5 text-sm focus:ring-primary focus:border-primary"/>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-semibold text-on-surface-variant">Jam Pulang:</label>
                                <input type="time" name="days[{{ $dayNum }}][end_time]" value="{{ $endTime }}" class="bg-white border border-outline-variant rounded-xl px-3 py-1.5 text-sm focus:ring-primary focus:border-primary"/>
                            </div>
                            <div class="flex items-center gap-2 ml-auto">
                                <input type="checkbox" name="days[{{ $dayNum }}][is_active]" value="1" {{ $isActive ? 'checked' : '' }} id="day_active_{{ $dayNum }}" class="rounded text-primary focus:ring-primary"/>
                                <label for="day_active_{{ $dayNum }}" class="text-xs font-bold text-on-surface">Hari Kerja Aktif</label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-4 pt-4 border-t border-outline-variant/30">
            <a href="{{ route('admin.teachers.schedule.index') }}" class="px-5 py-2.5 bg-surface-container-high text-on-surface text-sm font-semibold rounded-xl hover:bg-surface-container-highest transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition-all shadow-sm">
                Simpan Jadwal
            </button>
        </div>
    </form>

    <script>
        function toggleScheduleForm() {
            const toggle = document.getElementById('toggleCustomSchedule');
            const container = document.getElementById('scheduleFormContainer');
            if (toggle.checked) {
                container.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                container.classList.add('opacity-50', 'pointer-events-none');
            }
        }
    </script>
</x-layouts.admin>
