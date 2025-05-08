
<div>
    <h3 class="text-lg font-semibold text-grey">Pilih Konselor</h3>
    <div class="grid gap-4 grid-cols-1 md:grid-cols-4">
    <!-- Daftar Konselor -->
        @foreach($konselors as $konselor)
        <label class="rounded-lg shadow-md bg-white p-4 hover:shadow-lg transition border border-blue-500 flex items-center space-x-4 cursor-pointer">
            <input type="radio" name="konselor" value="{{ $konselor->id }}" 
                   class="h-5 w-5 text-blue-600 focus:ring-2 focus:ring-green-500" />
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ $konselor->nama }}</h3>
                <p class="text-green-600 font-bold">{{ $konselor->name }}</p>
            </div>
        </label>
        
        
        @endforeach
    </div>

<div class="p-4 space-y-4">
    <input type="text" wire:model.defer="voucher" placeholder="Masukkan kode voucher" class="border border-blue-500 p-2 border rounded">
    <button wire:click="applyVoucher" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Terapkan Voucher</button>
    @if($message)
        <p class="text-green-600">{{ $message }}</p>
    @endif

    <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
        @foreach($layanans as $layanan)
            <div class="rounded-lg shadow-md bg-white p-4 hover:shadow-lg transition border border-blue-500">
                <h3 class="text-lg font-semibold text-gray-800">{{ $layanan->nama_layanan }}</h3>
                <p class="text-gray-600">Harga : Rp. {{ number_format($layanan->harga_layanan, 0, ',', '.') }}</p>
                <p class="text-gray-600">Voucher : {{ $vouchernofalid }}</p>
                <p class="text-gray-600">Jumlah Potongan : {{ $jenispotongan }}</p>
                <p class="text-green-600 font-bold">Total : Rp. {{ number_format($hargaSetelahDiskon[$layanan->id_layanan], 0, ',', '.') }}</p>
            </div>
        @endforeach
        <div class="rounded-lg shadow-md bg-white p-4 hover:shadow-lg transition border border-blue-500">
            <h3 class="text-lg font-semibold text-gray-800">Metode Pembayaran</h3>
        </div>
    </div>
</div>
</div>