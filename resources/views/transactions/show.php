@layout('layouts.app', ['title' => 'Transaction Details'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Transaction #{{ $transaction['id'] ?? '' }}</h1>
            <a href="{{ route('transactions.index') }}" class="mt-1 inline-flex text-sm text-stone-500 hover:text-stone-800">&larr; Back to transactions</a>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('transactions.edit', ['transaction' => $transaction['id']]) }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                Edit transaction
            </a>
        </div>
    </div>

    @include('components/alerts', [
        'status' => $status ?? null,
        'errors' => $errors ?? [],
    ])

    <div class="rounded-2xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
        <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
            <div class="space-y-6">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Recorded on</p>
                    <p class="mt-2 text-sm font-semibold text-stone-900">{{ $transaction['date'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Type</p>
                    <p class="mt-2 inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $badgeClass ?? '' }}">
                        {{ $typeLabel ?? '' }}
                    </p>
                </div>
                <div class="border-t border-stone-200 pt-4">
                    <p class="text-xs uppercase tracking-widest text-stone-400">Amount</p>
                    <p class="mt-2 text-2xl font-semibold {{ $amountClass ?? '' }}">{{ $amountLabel ?? '' }}</p>
                    <p class="mt-1 text-xs text-stone-500">Currency: {{ $currency ?? '' }}</p>
                </div>
            </div>

            <div class="space-y-6 lg:border-l lg:border-stone-200 lg:pl-8">
                <div>
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

                <div class="border-t border-stone-200 pt-4">
                    <p class="text-xs uppercase tracking-widest text-stone-400">Vendor</p>
                    <p class="mt-2 text-sm text-stone-700">{{ $transaction['vendor_name'] ?? '—' }}</p>
                </div>

                <div class="border-t border-stone-200 pt-4">
                    <p class="text-xs uppercase tracking-widest text-stone-400">Category</p>
                    <p class="mt-2 text-sm text-stone-700">{{ $transaction['category_name'] ?? '—' }}</p>
                </div>

                <div class="border-t border-stone-200 pt-4">
                    <p class="text-xs uppercase tracking-widest text-stone-400">Description</p>
                    <p class="mt-2 text-sm text-stone-700">{{ $transaction['description'] ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
