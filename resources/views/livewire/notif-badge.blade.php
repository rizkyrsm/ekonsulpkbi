<div
    wire:poll.5s
    x-data="{
        open: false,
        prevCount: @entangle('count').defer,
        currentCount: @entangle('count'),
        playSoundIfNew() {
            if (this.currentCount > this.prevCount) {
                const audio = document.getElementById('notifSound');
                if (audio) {
                    audio.play().catch((e) => console.warn('Play error:', e));
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
    class="fixed bottom-4 right-4 z-50"
>
    <!-- Audio Notifikasi -->
    <audio id="notifSound" src="{{ asset('storage/sounds/notification.mp3') }}" preload="auto" muted autoplay></audio>

    <!-- Tombol Notifikasi -->
    <button
        @click="
            open = !open;
            document.getElementById('notifSound').muted = false;
        "
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full shadow-lg relative focus:outline-none flex items-center gap-2"
    >
        🔔
        <span class="inline">Notifikasi</span>
        @if ($count > 0)
            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-1">
                {{ $count }}
            </span>
        @endif
    </button>

    <!-- Popup Notifikasi -->
    <div
        x-show="open"
        @click.away="open = false"
        x-transition
        class="mt-2 w-80 max-w-[90vw] rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 overflow-hidden text-sm"
    >
        <div class="py-3 px-4 max-h-80 overflow-y-auto text-gray-800">
            <h2 class="font-bold mb-2">Belum Dibaca</h2>
            @forelse ($notifs as $notif)
                <button
                    wire:click="markAsReadAndRedirect({{ $notif->id }})"
                    class="w-full text-left px-3 py-2 mb-1 bg-blue-100 hover:bg-blue-200 rounded"
                >
                    {{ $notif->keterangan }}
                </button>
            @empty
                <div class="text-gray-500">Tidak ada notifikasi baru</div>
            @endforelse

            <h2 class="font-bold mt-4 mb-2">Terbaca</h2>
            @forelse ($allnotifs as $notifal)
                <div class="px-3 py-2 mb-1 hover:bg-gray-100 rounded">
                    {{ $notifal->keterangan }}
                </div>
            @empty
                <div class="text-gray-400">Belum ada</div>
            @endforelse
        </div>
    </div>
</div>
<script>
    document.addEventListener('livewire:load', function () {
        window.addEventListener('redirect', function (event) {
            window.location.href = event.detail.url;
        });
    });
</script>
