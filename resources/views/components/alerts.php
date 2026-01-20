@php
    $alertStatus = $status ?? ($alertStatus ?? null);
    $alertErrors = $errors ?? ($alertErrors ?? []);
    $alertErrorMessage = $alertErrorMessage ?? 'Please review the highlighted fields.';
    $alertStatusClass = $alertStatusClass ?? '';
    $alertErrorClass = $alertErrorClass ?? 'mt-4';
    $alertInfoMessage = $alertInfoMessage ?? null;
    $alertInfoClass = $alertInfoClass ?? '';
    $alertErrorList = $alertErrorList ?? [];
    $alertErrorTitle = $alertErrorTitle ?? 'There was a problem.';
    $hasErrorList = !empty($alertErrorList);
@endphp

@if (!empty($alertInfoMessage))
    <div class="{{ trim('rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700 ' . $alertInfoClass) }}" x-data="{ open: true }" x-show="open">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                {{ $alertInfoMessage }}
            </div>
            <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
@endif

@if (!empty($alertStatus))
    <div class="{{ trim('rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ' . $alertStatusClass) }}" x-data="{ open: true }" x-show="open">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                {{ $alertStatus }}
            </div>
            <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
@endif

@if ($hasErrorList)
    <div class="{{ trim('rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 ' . $alertErrorClass) }}" x-data="{ open: true }" x-show="open">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <p class="font-semibold">{{ $alertErrorTitle }}</p>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    @foreach ($alertErrorList as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
@elseif (!empty($alertErrors))
    <div class="{{ trim('rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 ' . $alertErrorClass) }}" x-data="{ open: true }" x-show="open">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <p class="font-semibold">{{ $alertErrorMessage }}</p>
            </div>
            <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
@endif
