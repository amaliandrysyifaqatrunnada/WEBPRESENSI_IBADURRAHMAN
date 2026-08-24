<x-layouts.auth>
    <x-slot:title>Portal Face ID - Kesalahan Perangkat</x-slot:title>

    <main class="flex-grow flex flex-col items-center justify-center p-6 md:p-12 relative z-10 font-sans">
        <div class="glass-card rounded-2xl p-8 max-w-md w-full text-center border border-outline-variant/50 shadow-2xl relative overflow-hidden bg-white">
            <div class="absolute top-0 left-0 w-full h-2 bg-red-600"></div>

            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-stack-md border border-red-100">
                <span class="material-symbols-outlined text-3xl font-semibold">report_gmailerrorred</span>
            </div>

            <h1 class="font-headline-md text-headline-md text-on-surface mb-2 font-bold">Akses Portal Ditolak</h1>
            
            <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed mb-6">
                {{ $message }}
            </p>

            <div class="p-4 bg-slate-50 border border-outline-variant/30 rounded-xl text-left text-xs text-on-surface-variant space-y-2 mb-6">
                <div class="font-bold text-on-surface">Detail Kesalahan:</div>
                <div>Kode: <code class="bg-slate-200 px-1 py-0.5 rounded text-red-700 font-mono">{{ $reason }}</code></div>
                <div>Solusi: Mintalah admin unit Anda untuk mendaftarkan dan mengeklik <strong>"Ikat Browser Perangkat"</strong> pada perangkat ini di dashboard admin.</div>
            </div>

            <a href="{{ route('portal') }}" class="w-full py-3 bg-primary hover:bg-primary/95 text-white rounded-xl font-label-md transition-all shadow-sm flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">home</span>
                Kembali ke Portal Utama
            </a>
        </div>
    </main>
</x-layouts.auth>
