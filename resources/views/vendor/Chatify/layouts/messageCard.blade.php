@php
    // Ikon seen
    $seenIcon = $seen ? 'check-double seen' : 'check';

    // Format waktu + ikon seen
    $timeAndSeen = "<span data-time='$created_at' class='message-time text-xs text-gray-500 flex items-center gap-1 mt-1'>
        " . ($isSender ? "<span class='fas fa-$seenIcon'></span>" : "") . "
        <span class='time'>$timeAgo</span>
    </span>";

    // Path & cek file attachment
    $ext = pathinfo($attachment, PATHINFO_EXTENSION);
    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    $relativePath = 'storage/attachments/' . $attachment;
    $fileExists = !empty($attachment) && file_exists(public_path($relativePath));
    $url = $fileExists ? asset($relativePath) : '#';
@endphp

<div class="message-card {{ $isSender ? 'mc-sender' : '' }}" data-id="{{ $id }}">
    {{-- Tombol hapus (jika pengirim) --}}
    @if ($isSender)
        @canRole('ADMIN')
            <div class="actions">
                <i class="fas fa-trash delete-btn" data-id="{{ $id }}"></i>
            </div>
        @endcanRole
    @endif

    <div class="message-card-content">
        {{-- Tampilkan Order ID jika ada --}}
        @if (!empty($id_order))
            <div class="text-xs text-gray-400 mb-1 italic">
                {{-- Order ID: #{{ $id_order }} --}}
            </div>
        @endif

        {{-- Pesan teks --}}
        @if ($message)
            <div class="message text-sm leading-relaxed">
                {!! nl2br(e($message)) !!}
                {!! $timeAndSeen !!}
            </div>
        @endif

        {{-- Attachment jika ada --}}
        @if (!empty($attachment))
            <div class="mt-2">
                @if ($fileExists)
                    @if ($isImage)
                        {{-- Gambar --}}
                        <div style="max-width: 250px; max-height: 250px; overflow: hidden; display: inline-block;">
                            <img src="{{ $url }}"
                                 class="chat-image"
                                 alt="attachment-image"
                                 style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 0.5rem; cursor: pointer; border: 1px solid #d1d5db; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: opacity 0.2s;"
                                 onclick="event.preventDefault(); window.open('{{ $url }}', '_blank');">
                        </div>
                        {!! $timeAndSeen !!}
                    @else
                        {{-- File non-gambar --}}
                        <div class="text-sm text-blue-600 mt-1">
                            <a href="{{ $url }}"
                               target="_blank"
                               class="hover:underline"
                               onclick="event.preventDefault(); window.open('{{ $url }}', '_blank');">
                                📎 Download Attachment
                            </a>
                        </div>
                        {!! $timeAndSeen !!}
                    @endif
                @else
                    <div class="text-xs text-red-500 italic mt-1">⚠️ Attachment tidak tersedia.</div>
                @endif
            </div>
        @endif
    </div>
</div>
