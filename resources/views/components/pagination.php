@if (!empty($pagination['pages'] ?? []) && ($pagination['last'] ?? 1) > 1)
    <div class="flex flex-col gap-3 border-t border-stone-100 px-4 py-3 text-sm text-stone-500 sm:flex-row sm:items-center sm:justify-between">
        <p>Showing {{ $pagination['from'] ?? 0 }}-{{ $pagination['to'] ?? 0 }} of {{ $pagination['total'] ?? 0 }}</p>
        <div class="flex flex-wrap items-center gap-1">
            @if (!empty($pagination['prev_url'] ?? ''))
                <a href="{{ $pagination['prev_url'] }}" class="rounded-lg border border-stone-200 px-3 py-1 text-sm font-semibold text-stone-600 hover:bg-stone-50">Prev</a>
            @else
                <span class="rounded-lg border border-stone-200 px-3 py-1 text-sm text-stone-300">Prev</span>
            @endif

            @foreach ($pagination['pages'] as $page)
                @if (!empty($page['is_current']))
                    <span class="rounded-lg border border-stone-200 bg-stone-100 px-3 py-1 text-sm font-semibold text-stone-900">{{ $page['page'] ?? '' }}</span>
                @else
                    <a href="{{ $page['url'] ?? '#' }}" class="rounded-lg border border-stone-200 px-3 py-1 text-sm font-semibold text-stone-600 hover:bg-stone-50">{{ $page['page'] ?? '' }}</a>
                @endif
            @endforeach

            @if (!empty($pagination['next_url'] ?? ''))
                <a href="{{ $pagination['next_url'] }}" class="rounded-lg border border-stone-200 px-3 py-1 text-sm font-semibold text-stone-600 hover:bg-stone-50">Next</a>
            @else
                <span class="rounded-lg border border-stone-200 px-3 py-1 text-sm text-stone-300">Next</span>
            @endif
        </div>
    </div>
@endif
