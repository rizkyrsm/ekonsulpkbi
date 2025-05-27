<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">History Konseling</h2>

    @if ($konselings->isEmpty())
        <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4">
            Tidak ada konseling ditemukan.
        </div>
    @else

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif


     <!-- Pagination -->
            <div class="mt-4">
                {{ $konselings->links() }}
            </div>
        <div class="overflow-x-auto shadow-lg rounded-lg">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID Order</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                        <th class="py-3 px-6 text-left">User</th>
                        <th class="py-3 px-6 text-left">Nama Layanan</th>
                        <th class="py-3 px-6 text-left">Konselor</th>
                        <th class="py-3 px-6 text-left">Status</th>
                        <th class="py-3 px-6 text-left">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @foreach ($konselings as $konseling)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-6">{{ $konseling->id_order }}</td>
                            <td class="py-3 px-6">
                                {{-- <a href="{{ url('/chatify/' . $konseling->id_konselor) }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold px-4 py-2 rounded">
                                    Lihat Obrolan
                                </a> --}}
                                @canRole('ADMIN','CABANG')
                                    @php
                                        $from_id = $konseling->id_konselor;
                                        $to_id = $konseling->id_user;
                                    @endphp

                                    <button onclick="openChatPopup('{{ $from_id }}', '{{ $to_id }}')" class="bg-blue-500 text-white px-4 py-2 rounded">
                                        <i class="bi bi-chat-heart-fill"></i> Open
                                    </button>
                                @endcanRole
                                @canRole('KONSELOR')
                                   <button onclick="openStartChat('{{ $konseling->id_user }}')" 
                                        class="bg-blue-500 text-white px-4 py-2 rounded">
                                        <i class="bi bi-chat-heart-fill"></i> Mulai Konsultasi
                                    </button>
                                @endcanRole
                                @canRole('USER')
                                   <button onclick="openStartChat('{{ $konseling->id_konselor }}')" 
                                        class="bg-blue-500 text-white px-4 py-2 rounded">
                                        <i class="bi bi-chat-heart-fill"></i> Mulai Konsultasi
                                    </button>
                                @endcanRole
                                
                            </td>
                            <td class="py-3 px-6">{{ $konseling->user_name }}</td>
                            <td class="py-3 px-6">{{ $konseling->nama_layanan }}</td>
                            <td class="py-3 px-6">{{ $konseling->konselor_name }}</td>
                            <td class="py-3 px-6">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $konseling->payment_status == 'BELUM BAYAR' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $konseling->payment_status }}
                                </span>
                            <td class="py-3 px-6">{{ $konseling->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- modal riwayat konseling view --}}
    <div id="chatify-popup" class="fixed bottom-10 right-4 w-100 h-[650px] bg-white shadow-xl hidden z-50">
        <div class="flex justify-between items-center text-white p-2 bg-blue-800 border-b">
            <h1 class="text-xl font-bold mb-4">Riwayat Konseling</h1>
            <flux:button onclick="closeChatPopup()" icon="x-mark" variant="danger" size="sm"></flux:button>
        </div>
        <iframe id="chatify-frame" src="" class="w-full h-full border-none"></iframe>
    </div>

    <script>
        function openChatPopup(fromId, toId) {
            const url = `/custom-chat/${fromId}/${toId}`;
            document.getElementById('chatify-frame').src = url;
            document.getElementById('chatify-popup').classList.remove('hidden');
        }

        function closeChatPopup() {
            document.getElementById('chatify-frame').src = '';
            document.getElementById('chatify-popup').classList.add('hidden');
        }
    </script>
    {{-- akhir modal riwayat konseling --}}

    {{-- Modal untuk memulai chat --}}
        <div id="chat-start-popup" class="fixed bottom-10 right-4 w-100 h-[650px] bg-white shadow-xl hidden z-50">
            <div class="flex justify-between items-center text-white p-2 bg-green-800 border-b">
                <h1 class="text-xl font-bold mb-4">Chat</h1>
                <button onclick="closeStartChatPopup()" class="text-white px-3 py-1 rounded bg-red-600 hover:bg-red-700">
                    Close
                </button>
            </div>
            <iframe id="chat-start-frame" src="" class="w-full h-full border-none"></iframe>
        </div>
        <script>
            function openStartChat(userId) {
                const url = `/chatify/${userId}`; // atau endpoint chat yang sesuai untuk role konselor/user
                document.getElementById('chat-start-frame').src = url;
                document.getElementById('chat-start-popup').classList.remove('hidden');
            }

            function closeStartChatPopup() {
                document.getElementById('chat-start-frame').src = '';
                document.getElementById('chat-start-popup').classList.add('hidden');
            }

        </script>

    {{-- akhir modal memulai chat --}}


</div>