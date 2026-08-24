<x-layouts.admin>
    <x-slot:title>PKBM IBADURRAHMAN - Perangkat Sekolah</x-slot:title>

    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-background">Pengaturan Kehadiran</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">Daftarkan dan ikat perangkat sekolah (tablet/laptop/PC) untuk menampilkan portal QR Code Presensi resmi.</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-outline-variant mb-8 gap-6 overflow-x-auto custom-scrollbar">
        <a href="{{ route('admin.settings.attendance') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Aturan Umum</a>
        <a href="{{ route('admin.settings.gps') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Pengaturan GPS</a>
        <a href="{{ route('admin.settings.qr') }}" class="pb-3 border-b-2 border-transparent font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors whitespace-nowrap">Tampilan QR Code</a>
        <a href="{{ route('admin.devices.index') }}" class="pb-3 border-b-2 border-primary font-label-md text-label-md text-primary whitespace-nowrap font-bold">Perangkat Sekolah</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter font-sans">
        <!-- Add Device Card -->
        <div class="lg:col-span-4 card-layer-1 rounded-xl p-6 flex flex-col gap-6 h-fit bg-surface-container-lowest border border-outline-variant">
            <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 font-bold text-primary">
                <span class="material-symbols-outlined">add_to_queue</span>
                Daftar Perangkat Baru
            </h3>
            
            <form action="{{ route('admin.devices.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2" for="device_name">Nama Perangkat</label>
                    <input id="device_name" name="device_name" class="w-full bg-white border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="text" placeholder="Misal: Tablet Presensi TK" required/>
                </div>
                
                <div class="flex flex-col">
                    <label class="font-label-md text-label-md text-on-surface mb-2">Unit Terikat</label>
                    <input class="w-full bg-slate-100 border border-outline-variant rounded-xl py-2.5 px-4 font-body-md text-body-md text-slate-500 cursor-not-allowed" type="text" value="{{ $unit->name ?? 'Unit Admin' }}" readonly disabled/>
                </div>

                <button type="submit" class="w-full py-3 bg-primary hover:bg-primary/95 text-white rounded-xl font-label-md transition-all shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    Simpan Perangkat
                </button>
            </form>

            @if($activeDeviceOnBrowser)
                <div class="mt-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800">
                    <h4 class="font-bold flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-sm">laptop_mac</span>
                        Browser Ini Terikat
                    </h4>
                    <p class="text-xs mt-1 leading-relaxed">
                        Browser ini sudah aktif sebagai perangkat: <strong class="underline">{{ $activeDeviceOnBrowser->device_name }}</strong>.
                    </p>
                    <form action="{{ route('admin.devices.deactivate-browser') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-colors flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-xs">link_off</span>
                            Lepas Ikatan Browser Perangkat
                        </button>
                    </form>
                </div>
            @else
                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs leading-relaxed">
                    <h4 class="font-bold flex items-center gap-1.5 text-sm">
                        <span class="material-symbols-outlined text-sm">info</span>
                        Informasi Ikatan
                    </h4>
                    <p class="mt-1">
                        Browser ini belum terikat dengan perangkat sekolah apa pun. Klik <strong>"Ikat Browser Perangkat"</strong> pada daftar perangkat untuk mengaktifkannya di browser tablet/laptop presensi sekolah.
                    </p>
                </div>
            @endif
        </div>

        <!-- Devices List Card -->
        <div class="lg:col-span-8 card-layer-1 rounded-xl p-6 bg-surface-container-lowest border border-[#E6ECE7]">
            <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 font-bold mb-6">
                <span class="material-symbols-outlined text-primary">devices</span>
                Daftar Perangkat Terdaftar - Unit {{ $unit->name ?? '' }}
            </h3>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[600px] text-body-sm">
                    <thead>
                        <tr class="bg-[#F7FAF7] border-b border-[#E6ECE7]">
                            <th class="py-3 px-4 font-label-sm text-label-sm text-on-surface-variant uppercase">Nama Perangkat</th>
                            <th class="py-3 px-4 font-label-sm text-label-sm text-on-surface-variant uppercase">Status</th>
                            <th class="py-3 px-4 font-label-sm text-label-sm text-on-surface-variant uppercase">Terakhir Digunakan</th>
                            <th class="py-3 px-4 font-label-sm text-label-sm text-on-surface-variant uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E6ECE7] bg-white">
                        @forelse($devices as $device)
                            @php
                                $isThisDeviceBound = $activeDeviceOnBrowser && $activeDeviceOnBrowser->id === $device->id;
                            @endphp
                            <tr class="hover:bg-[#F7FAF7] transition-colors {{ $isThisDeviceBound ? 'bg-emerald-50/30' : '' }}">
                                <td class="py-4 px-4 font-medium">
                                    <div class="text-on-surface font-semibold flex items-center gap-2">
                                        {{ $device->device_name }}
                                        @if($isThisDeviceBound)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F5E9] text-[#2E7D32] border border-[#C8E6C9] gap-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32] animate-pulse"></span>
                                                Browser Ini
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-outline font-mono mt-1">TOKEN: {{ substr($device->device_token, 0, 18) }}...</div>
                                </td>
                                <td class="py-4 px-4">
                                    @if($device->status)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#E8F5E9] text-[#2E7D32] border border-[#C8E6C9]">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-on-surface-variant">
                                    {{ $device->last_used_at ? $device->last_used_at->isoFormat('D MMM YYYY, H:mm') . ' WIB' : '-' }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Toggle Status -->
                                        <form action="{{ route('admin.devices.toggle', $device->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-xs font-medium border border-outline-variant rounded hover:bg-surface-container transition-colors" title="{{ $device->status ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                {{ $device->status ? 'Matikan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        @if($device->status)
                                            @if($isThisDeviceBound)
                                                <form action="{{ route('admin.devices.deactivate-browser') }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-red-50 text-red-700 border border-red-200 rounded hover:bg-red-100 transition-colors">
                                                        Lepas
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.devices.activate-browser', $device->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 text-xs font-bold bg-[#E8F5E9] text-[#2E7D32] border border-[#C8E6C9] rounded hover:bg-[#C8E6C9] transition-colors">
                                                        Ikat Browser Perangkat
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        <!-- Delete -->
                                        <form action="{{ route('admin.devices.destroy', $device->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus perangkat ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-error hover:bg-error-container/20 rounded transition-colors" title="Hapus Perangkat">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-4 text-center text-on-surface-variant">
                                    Belum ada perangkat terdaftar untuk unit ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
