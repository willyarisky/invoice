@layout('layouts.app', ['title' => 'Transaction Details'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Transaction #{{ $transaction['id'] ?? '' }}</h1>
            <p class="mt-1 text-sm text-stone-500">Recorded on {{ $transaction['date'] ?? '—' }}</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('transactions.edit', ['transaction' => $transaction['id']]) }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                Edit transaction
            </a>
            <a href="{{ route('transactions.index') }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                Back to transactions
            </a>
        </div>
    </div>

    @if (!empty($status ?? ''))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" x-data="{ open: true }" x-show="open">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    {{ $status ?? '' }}
                </div>
                <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[260px_1fr]">
        <div class="space-y-4">
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Type</p>
                <p class="mt-2 inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $badgeClass ?? '' }}">
                    {{ $typeLabel ?? '' }}
                </p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Amount</p>
                <p class="mt-2 text-2xl font-semibold {{ $amountClass ?? '' }}">{{ $amountLabel ?? '' }}</p>
                <p class="mt-1 text-xs text-stone-500">Currency: {{ $currency ?? '' }}</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Source</p>
                @if (($source ?? '') === 'invoice' && !empty($transaction['invoice_id']))
                    <p class="mt-2 text-sm text-stone-700">Invoice payment</p>
                    <a href="{{ route('invoices.show', ['invoice' => $transaction['invoice_id']]) }}" class="mt-2 inline-flex text-sm font-semibold text-stone-900 hover:text-stone-500">
                        View invoice {{ $transaction['invoice_no'] ?? '' }}
                    </a>
                    <p class="mt-1 text-xs text-stone-500">Customer: {{ $transaction['customer_name'] ?? '—' }}</p>
                @else
                    <p class="mt-2 text-sm text-stone-700">Manual entry</p>
                @endif
            </div>

            <div class="rounded-xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Vendor</p>
                <p class="mt-2 text-sm text-stone-700">{{ $transaction['vendor_name'] ?? '—' }}</p>
            </div>

            <div class="rounded-xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Category</p>
                <p class="mt-2 text-sm text-stone-700">{{ $transaction['category_name'] ?? '—' }}</p>
            </div>

            <div class="rounded-xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Description</p>
                <p class="mt-2 text-sm text-stone-700">{{ $transaction['description'] ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
