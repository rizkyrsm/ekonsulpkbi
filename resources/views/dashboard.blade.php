<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Card Statistik -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <!-- Card Konselor (Biru) -->
            <div class="flex items-center gap-4 rounded-xl border border-blue-700 bg-blue-500 p-4 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-800 text-white">
                    @svg('heroicon-o-user-group', 'w-12 h-12')
                </div>
                <div>
                    <div class="text-sm font-medium text-white">JUMLAH KONSELOR</div>
                    <div class="text-5xl font-bold text-white">{{ $jumlahKonselor }}</div>
                </div>
            </div>

            <!-- Card User (Kuning) -->
            <div class="flex items-center gap-4 rounded-xl border border-yellow-500 bg-yellow-500 p-4 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-700 text-white">
                    @svg('heroicon-o-user', 'w-12 h-12')
                </div>
                <div>
                    <div class="text-sm font-medium text-white">JUMLAH USER</div>
                    <div class="text-5xl font-bold text-white">{{ $jumlahUser }}</div>
                </div>
            </div>

            <!-- Card Diskon Aktif (Hijau) -->
            <div class="flex items-center gap-4 rounded-xl border border-green-500 bg-green-500 p-4 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-700 text-white">
                    @svg('heroicon-o-tag', 'w-12 h-12')
                </div>
                <div>
                    <div class="text-sm font-medium text-white">DISKON AKTIF</div>
                    <div class="text-5xl font-bold text-white">{{ $jumlahDiskonAktif }}</div>
                </div>
            </div>
        </div>

        <!-- Placeholder Bawah -->
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-300">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/10 dark:stroke-gray-900/10" />
        </div>
    </div>
</x-layouts.app>
