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
                                @canRole('ADMIN','CABANG')
                                    @php
                                        $from_id = $konseling->id_konselor;
                                        $to_id = $konseling->id_user;
                                    @endphp

                                    <button onclick="openChatPopup('{{ $from_id }}', '{{ $to_id }}')" class="bg-green-500 text-white px-4 py-2 rounded">
                                        <i class="bi bi-chat-heart-fill"></i> Open
                                    </button>
                                @endcanRole
                                @canRole('KONSELOR')
                                    @php
                                        $from_id = $konseling->id_konselor;
                                        $to_id = $konseling->id_user;
                                    @endphp

                                    @if ($konseling->payment_status == 'SELESAI')
                                        <button onclick="openChatPopup('{{ $from_id }}', '{{ $to_id }}')" 
                                            class="bg-green-500 text-white px-4 py-2 rounded">
                                            <i class="bi bi-chat-heart-fill"></i> Open
                                        </button>
                                    @else
                                        <button onclick="openStartChat('{{ $konseling->id_user }}')" 
                                            class="bg-blue-500 text-white px-4 py-2 rounded">
                                            <i class="bi bi-chat-heart-fill"></i> Mulai Konsultasi
                                        </button>
                                    @endif
                                @endcanRole

                                @canRole('USER')
                                    @php
                                        $from_id = $konseling->id_konselor;
                                        $to_id = $konseling->id_user;
                                    @endphp

                                    @if ($konseling->payment_status == 'SELESAI')
                                        <button onclick="openChatPopup('{{ $from_id }}', '{{ $to_id }}')" 
                                            class="bg-blue-500 text-white px-4 py-2 rounded">
                                            <i class="bi bi-green-heart-fill"></i> Open
                                        </button>
                                    @else
                                       <button onclick="checkProfileAndStartChat('{{ $to_id }}', '{{ $from_id }}')"
                                            class="bg-blue-500 text-white px-4 py-2 rounded">
                                            <i class="bi bi-chat-heart-fill"></i> Mulai Konsultasi
                                        </button>
                                    @endif
                                @endcanRole

                                
                            </td>
                            {{-- <td class="py-3 px-6">{{ $konseling->user_name }}</td> --}}
                            <td class="py-3 px-6">
                                <button 
                                    onclick="showProfileModal({{ $konseling->id_user }})" 
                                    class="ml-2 text-sm text-blue-600 hover:underline"
                                >
                                    <span class="px-2 text-white inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-500">{{ $konseling->user->detailUser->nama }} </span>
                                </button>
                            </td>
                            <td class="py-3 px-6">{{ $konseling->nama_layanan }}</td>
                            <td class="py-3 px-6">{{ $konseling->konselor->detailUser->nama }}</td>
                            <td class="py-3 px-6">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $konseling->payment_status == 'BELUM BAYAR' ? 'bg-red-500 text-red-800' : 'bg-green-500 text-green-800' }}">
                                        {{ $konseling->payment_status == 'LUNAS' ? 'AKTIF' : 'SELESAI' }}
                                    </span>
                                @canRole('KONSELOR')
                                    @if ($konseling->payment_status == 'LUNAS')
                                        <form method="POST" action="{{ route('konseling.updateStatus', $konseling->id_order) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menyelesaikan sesi ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">
                                                Tandai Selesai
                                            </button>
                                        </form>
                                    @endif
                                @endcanRole
                                </div>
                            </td>

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
                <flux:button onclick="closeStartChatPopup()" icon="x-mark" variant="danger" size="sm"></flux:button>
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

    {{-- Modal untuk user profile detail --}}
    <div id="profileModal" class="fixed inset-0 hidden z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 border rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <button onclick="closeProfileModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">&times;</button>
            <h2 class="text-xl font-bold mb-4">Detail Profil</h2>
            <div id="profileContent">
                <p>Memuat...</p>
            </div>
        </div>
    </div>

    <script>
        function showProfileModal(userId) {
            document.getElementById('profileModal').classList.remove('hidden');
            document.getElementById('profileContent').innerHTML = 'Memuat...';

            fetch(`/profile-detail-json/${userId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('profileContent').innerHTML = `
                        <p><strong>Nama:</strong> ${data.nama}</p>
                        <p><strong>NIK:</strong> ${data.nik}</p>
                        <p><strong>Tempat, Tanggal Lahir:</strong> ${data.tempat_lahir}, ${data.tgl_lahir}</p>
                        <p><strong>Alamat:</strong> ${data.alamat}</p>
                        <p><strong>No Telepon:</strong> ${data.no_tlp}</p>
                        <p><strong>Status Online:</strong> ${data.status_online}</p>
                        <p><strong>Jenis Kelamin:</strong> ${data.jenis_kelamin}</p>
                        <p><strong>Status Pernikahan:</strong> ${data.status_pernikahan}</p>
                        <p><strong>Agama:</strong> ${data.agama}</p>
                    `;
                });
        }

        function closeProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
        }
    </script>
    {{-- Akhir modal profile --}}

    {{-- CEK ISI DETAIL USER --}}
        <script>
            function checkProfileAndStartChat(userId,konselorId) {
                fetch(`/check-profile/${userId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.complete) {
                            openStartChat(konselorId);
                        } else {
                            alert('Profil Anda belum lengkap. Silakan lengkapi profil terlebih dahulu.');
                            window.location.href = 'settings/profile-detail';
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan. Coba lagi.');
                        console.error(err);
                    });
            }
        </script>
    {{-- AKHIR CEK DETAIL --}}
</div>