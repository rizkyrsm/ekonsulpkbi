<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

            <livewire:notif-badge />
    
        <!-- Card Statistik -->
        @canRole('ADMIN')
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
        @endcanRole

        @canRole('USER')
        <div class="grid grid-cols-3 gap-4">
            @foreach($layanans as $layanan)
                <div class="rounded-lg shadow-md bg-white p-4 hover:shadow-lg transition border border-blue-500">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $layanan->nama_layanan }}</h3>
                    <p class="text-gray-600 line-through">Rp. {{ number_format($layanan->harga_layanan, 0, ',', '.') }} </p>
                    <p class="text-green-600 font-bold">Potongan Diskon 100%</p>
                    <p class="text-green-600 font-bold">Gunakan Kode Voucher: PKBIJAYA</p>
                    <a href="{{ route('dashboard.keranjang', ['id' => $layanan->id_layanan]) }}" class="mt-2 w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center justify-center gap-2">
                        Mulai Konseling
                        <flux:icon.chat-bubble-oval-left variant="solid" />
                    </a>
                </div>
            @endforeach
        </div>
        @endcanRole
        
    </div>

</x-layouts.app>


