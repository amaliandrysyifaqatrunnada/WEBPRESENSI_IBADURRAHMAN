<x-layouts.admin>
    <x-slot:title>Jadwal Kerja Tenaga Pendidik - PKBM IBADURRAHMAN</x-slot:title>

    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Jadwal Kerja Tenaga Pendidik</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola jadwal kerja individual per orang untuk menentukan jam masuk dan jam pulang efektif.</p>
    </div>

    <!-- Filter Bar -->
    <div class="card-layer-1 rounded-xl p-4 mb-6">
        <form action="{{ route('admin.teachers.schedule.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari NIP / Nama Guru..." class="w-full bg-white border border-outline-variant rounded-xl px-4 py-2 text-sm focus:ring-primary focus:border-primary" />
            </div>
            @if(auth()->user()->hasRole('superadmin'))
                <div>
                    <select name="unit_id" onchange="this.form.submit()" class="bg-white border border-outline-variant rounded-xl px-4 py-2 text-sm focus:ring-primary focus:border-primary">
                        <option value="All" {{ $selectedUnitId === 'All' ? 'selected' : '' }}>Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ (string)$selectedUnitId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button type="submit" class="px-4 py-2 bg-surface-container-high text-on-surface text-sm font-semibold rounded-xl hover:bg-surface-container-highest transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="card-layer-1 rounded-xl overflow-hidden shadow-sm border border-outline-variant/30">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant/30 text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        <th class="py-3.5 px-4">No</th>
                        <th class="py-3.5 px-4">ID / NIP</th>
                        <th class="py-3.5 px-4">Nama Guru</th>
                        <th class="py-3.5 px-4">Unit Pendidikan</th>
                        <th class="py-3.5 px-4">Mode Jadwal Kerja</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20 text-sm">
                    @forelse($teachers as $index => $teacher)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="py-3.5 px-4 font-medium">{{ $teachers->firstItem() + $index }}</td>
                            <td class="py-3.5 px-4 font-bold text-primary">{{ $teacher->display_id }}</td>
                            <td class="py-3.5 px-4 font-semibold text-on-surface">
                                {{ $teacher->name }}<br>
                                <span class="text-xs font-normal text-on-surface-variant">{{ $teacher->position }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-on-surface-variant">
                                {{ $teacher->unit ? $teacher->unit->name : '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($teacher->use_custom_schedule)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        ✨ Custom Schedule Individual
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        🏢 Default Unit
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.teachers.schedule.edit', $teacher) }}" class="px-3.5 py-1.5 bg-primary/10 text-primary hover:bg-primary/20 text-xs font-bold rounded-xl transition-all inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">schedule</span>
                                    Atur Jadwal
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-on-surface-variant text-sm">Tidak ada data tenaga pendidik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-outline-variant/30">
            {{ $teachers->links() }}
        </div>
    </div>
</x-layouts.admin>
