<?php
$seenIcon = $seen ? 'check-double seen' : 'check';
$timeAndSeen = "<span data-time='$created_at' class='message-time'>
    " . ($isSender ? "<span class='fas fa-$seenIcon'></span>" : "") . "
    <span class='time'>$timeAgo</span>
</span>";
?>
<div class="message-card @if($isSender) mc-sender @endif" data-id="{{ $id }}">
    {{-- Delete Message --}}
    @if ($isSender)
        @canRole('ADMIN')
            <div class="actions">
                <i class="fas fa-trash delete-btn" data-id="{{ $id }}"></i>
            </div>
        @endcanRole
    @endif

    <div class="message-card-content">
        @if (!empty($id_order))
            <div class="text-xs text-gray-400 mb-1 italic">
                {{-- Order ID: #{{ $id_order }} --}}
            </div>
        @endif

        {{-- Text or file --}}
        @if (@$attachment['type'] != 'image' || $message)
            <div class="message">
                {!! ($message == null && $attachment != null && @$attachment['type'] != 'file') ? $attachment['title'] : nl2br(e($message)) !!}
                {!! $timeAndSeen !!}

                @if (@$attachment['type'] == 'file')
                    <a href="{{ route(config('chatify.attachments.download_route_name'), ['fileName' => $attachment['file']]) }}" class="file-download">
                        <span class="fas fa-file"></span> {{ $attachment['title'] }}
                    </a>
                @endif
            </div>
        @endif

        {{-- Gambar --}}
        @if (@$attachment['type'] == 'image')
            <div class="image-wrapper" style="text-align: {{ $isSender ? 'end' : 'start' }}">
                <div class="image-file chat-image" style="background-image: url('{{ asset('storage/attachments/' . $attachment['file']) }}')">
                    <div>{{ $attachment['title'] }}</div>
                </div>
                <div style="margin-bottom:5px">
                    {!! $timeAndSeen !!}
                </div>
            </div>
        @endif
    </div>
</div>
