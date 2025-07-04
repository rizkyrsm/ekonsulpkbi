<?php
$seenIcon = $seen ? 'check-double seen' : 'check';
$timeAndSeen = "<span data-time='$created_at' class='message-time text-xs text-gray-500 flex items-center gap-1 mt-1'>
    " . ($isSender ? "<span class='fas fa-$seenIcon'></span>" : "") . "
    <span class='time'>$timeAgo</span>
</span>";
?>

<div class="message-card @if($isSender) mc-sender @endif" data-id="{{ $id }}">
    {{-- Delete Button --}}
    @if ($isSender)
        @canRole('ADMIN')
            <div class="actions">
                <i class="fas fa-trash delete-btn" data-id="{{ $id }}"></i>
            </div>
        @endcanRole
    @endif

    <div class="message-card-content">
        {{-- Order ID jika ada --}}
        @if (!empty($id_order))
            <div class="text-xs text-gray-400 mb-1 italic">
                {{-- Order ID: #{{ $id_order }} --}}
            </div>
        @endif

        {{-- Teks pesan --}}
        @if ($message)
            <div class="message text-sm leading-relaxed">
                {!! nl2br(e($message)) !!}
                {!! $timeAndSeen !!}
            </div>
        @endif

        {{-- Preview Attachment ala Chatify --}}
        @if (!empty($attachment))
            @php
                $ext = pathinfo($attachment, PATHINFO_EXTENSION);
                $url = asset('storage/attachments/' . $attachment);
                $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            @endphp

            <div class="mt-2">
                @if ($isImage)
                    <div style="max-width: 250px; max-height: 250px; overflow: hidden; display: inline-block;">
                        <img src="{{ $url }}"
                             style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 0.5rem; cursor: pointer; border: 1px solid #d1d5db; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: opacity 0.2s;"
                             onclick="window.open('{{ $url }}', '_blank')"
                             alt="image">
                    </div>
                    {!! $timeAndSeen !!}
                @else
                    {{-- Optional: tampilkan nama file jika bukan gambar --}}
                    <div class="text-sm text-blue-600 mt-1">
                        <a href="{{ $url }}" target="_blank" class="hover:underline">
                            📎 Download Attachment
                        </a>
                    </div>
                    {!! $timeAndSeen !!}
                @endif
            </div>
        @endif
    </div>
</div>
