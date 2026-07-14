{{-- Khushkhabri / good-news note (#14) — admin-editable from Settings.
     Title + short teaser always visible; full text expands on click.
     Renders nothing when no title is configured. --}}
@php
    $noticeTitle = setting('notice_title');
    $noticeShort = setting('notice_short');
    $noticeFull  = setting('notice_full');
@endphp
@if ($noticeTitle)
    <div x-data="{ open: false }"
         class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 sm:p-5 {{ $class ?? '' }}">
        <div class="flex items-start gap-3">
            <i class="fas fa-gift text-amber-500 text-lg mt-0.5"></i>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-amber-900">{{ $noticeTitle }}</div>
                @if ($noticeShort)
                    <p class="text-sm text-amber-800/90 mt-0.5 leading-relaxed">{{ $noticeShort }}</p>
                @endif
                @if ($noticeFull)
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="text-sm text-amber-800/90 mt-2 leading-relaxed whitespace-pre-line">{{ $noticeFull }}</div>
                    <button type="button" @click="open = !open"
                            class="mt-2 text-xs font-semibold text-amber-700 hover:text-amber-900 inline-flex items-center gap-1">
                        <span x-text="open ? 'Show less' : 'Read more'"></span>
                        <i class="fas fa-chevron-down text-[9px]" :class="open ? 'rotate-180' : ''" style="transition:transform .2s;"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif
