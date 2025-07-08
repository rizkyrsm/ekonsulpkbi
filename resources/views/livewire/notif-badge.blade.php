<div
    wire:poll.5s
    x-data="{
        open: false,
        prevCount: @entangle('count').defer,
        currentCount: @entangle('count'),
        playSoundIfNew() {
            console.log('prev:', this.prevCount, 'current:', this.currentCount);
            if (this.currentCount > this.prevCount) {
                const audio = document.getElementById('notifSound');
                if (audio) {
                    audio.play().catch((e) => {
                        console.warn('Play error:', e);
                    });
                }
            }
            this.prevCount = this.currentCount;
        }
    }"
    x-init="
        const audio = document.getElementById('notifSound');
        audio.play().then(() => {
            audio.pause();
            audio.muted = false;
        }).catch(() => {});
    "
    x-effect="playSoundIfNew()"
    class="relative inline-block text-left"
>
    <!-- Audio Notifikasi -->
    <audio id="notifSound" src="{{ asset('storage/sounds/notification.mp3') }}" preload="auto" muted autoplay></audio>

    <!-- Tombol Bell -->
    <button
        @click="
            open = !open;
            document.getElementById('notifSound').muted = false;
        "
        class="relative group focus:outline-none"
    >
        🔔
        @if ($count > 0)
            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full px-1">
                {{ $count }}
            </span>
        @endif
        <div class="absolute left-full ml-2 top-1/2 -translate-y-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition z-40">
            Lihat Notifikasi
        </div>
    </button>

    <!-- Dropdown Notifikasi -->
    <div
        x-show="open"
        @click.away="open = false"
        x-transition
        class="absolute left-1/2 top-full -translate-x-1/2 mt-2 w-auto min-w-[16rem] rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
    >
        <div class="py-3 px-5 max-h-70 overflow-y-auto text-sm text-gray-800">
            <h2><b>Belum Dibaca</b></h2>
            @forelse ($notifs as $notif)
                <button
                    wire:click="markAsReadAndRedirect({{ $notif->id }})"
                    class="w-full text-left px-4 py-2 bg-blue-300 hover:bg-gray-300 border-b border-gray-500"
                >
                    {{ $notif->keterangan }}
                </button>
            @empty
                <div class="px-4 py-2 text-gray-500">Tidak ada notifikasi</div>
            @endforelse

            <h2 class="mt-2"><b>Terbaca</b></h2>
            @forelse ($allnotifs as $notifal)
                <button class="w-full text-left px-4 py-2 hover:bg-gray-300 border-b border-gray-500">
                    {{ $notifal->keterangan }}
                </button>
            @empty
                <div class="px-4 py-2 text-gray-400">Belum ada</div>
            @endforelse
        </div>
    </div>
</div>
