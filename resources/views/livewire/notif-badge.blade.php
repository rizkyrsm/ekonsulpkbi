<div class="relative">
    <button class="relative">
        🔔
        @if ($count > 0)
            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full px-1">
                {{ $count }}
            </span>
        @endif
    </button>
</div>
