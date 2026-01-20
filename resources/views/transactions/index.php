@layout('layouts.app', ['title' => 'Transactions'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Transactions</h1>
        </div>
        <div class="flex items-center gap-2 sm:w-auto sm:flex-row sm:items-center">
            <details class="relative sm:hidden">
                <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-700 shadow-sm hover:bg-stone-50" aria-label="Search transactions">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
                        <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </summary>
                <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/40 px-4 pb-6" onclick="if (event.target === this) { this.closest('details').removeAttribute('open'); }">
                    <div class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white px-4 py-4 shadow-xl">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-stone-900">Search transactions</p>
                            <button type="button" class="rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-500 hover:bg-stone-50" onclick="this.closest('details').removeAttribute('open')">Close</button>
                        </div>
                        <form method="GET" action="{{ route('transactions.index') }}" class="mt-3">
                            @if (!empty($invoiceId ?? 0))
                                <input type="hidden" name="invoice_id" value="{{ $invoiceId }}">
                            @endif
                            <div class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                                <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Vendor, customer, invoice, note" class="w-full bg-transparent text-sm text-stone-600 placeholder:text-stone-400 focus:outline-none">
                            </div>
                            <button type="submit" class="mt-3 w-full rounded-xl bg-stone-900 px-3 py-2 text-xs font-semibold text-white hover:bg-stone-800">
                                Search
                            </button>
                        </form>
                    </div>
                </div>
            </details>
            <form method="GET" action="{{ route('transactions.index') }}" class="hidden items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600 shadow-sm sm:flex">
                @if (!empty($invoiceId ?? 0))
                    <input type="hidden" name="invoice_id" value="{{ $invoiceId }}">
                @endif
                <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Search vendor, customer, invoice, or note" class="w-full bg-transparent text-sm text-stone-600 placeholder:text-stone-400 focus:outline-none sm:w-64">
            </form>
            <a href="{{ route('transactions.create') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-stone-800 text-white hover:bg-stone-700 sm:h-auto sm:w-auto sm:px-4 sm:py-2 sm:text-sm sm:font-semibold" aria-label="Add transaction">
                <svg aria-hidden="true" class="h-4 w-4 sm:hidden" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <span class="hidden sm:inline">Add transaction</span>
            </a>
        </div>
    </div>

    @include('components/alerts', [
        'status' => $status ?? null,
        'errors' => $errors ?? [],
    ])

    <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
        <div class="lg:hidden">
            <div class="divide-y divide-stone-100">
                @foreach ($transactions as $transaction)
                    <div class="px-4 py-4" onclick="window.location='{{ route('transactions.show', ['transaction' => $transaction['id']]) }}'" style="cursor: pointer;">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-stone-900">{{ $transaction['date'] ?? '' }}</span>
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-[11px] font-semibold {{ $transaction['type_badge_class'] ?? '' }}">
                                {{ $transaction['type_label'] ?? '' }}
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-stone-600">
                            @if (($transaction['source'] ?? '') === 'invoice' && !empty($transaction['invoice_id']))
                                <p class="font-medium text-stone-800">Invoice {{ $transaction['invoice_no'] ?? '' }}</p>
                                <p class="text-xs text-stone-500">Customer: {{ $transaction['customer_name'] ?? '—' }}</p>
                            @else
                                <p class="text-sm text-stone-700">{{ $transaction['description'] ?? 'Manual entry' }}</p>
                                <p class="text-xs text-stone-500">Manual entry</p>
                            @endif
                            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-stone-500">
                                <span>Vendor {{ $transaction['vendor_name'] ?? '—' }}</span>
                                <span>Category {{ $transaction['category_name'] ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm font-semibold {{ $transaction['amount_class'] ?? '' }}">{{ $transaction['amount_label'] ?? '' }}</span>
                            <div class="flex items-center gap-2" onclick="event.stopPropagation();">
                                <a href="{{ route('transactions.show', ['transaction' => $transaction['id']]) }}" class="rounded-lg border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">View</a>
                                <a href="{{ route('transactions.edit', ['transaction' => $transaction['id']]) }}" class="rounded-lg border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">Edit</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-stone-500">Add your first transaction to start tracking.</div>
                @endforeach
            </div>
        </div>
        <table class="hidden min-w-full divide-y divide-stone-100 text-sm text-stone-700 lg:table">
            <thead class="text-left text-xs font-semibold uppercase tracking-widest text-stone-500 rounded-t-xl">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Source</th>
                    <th class="px-4 py-3">Vendor</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($transactions as $transaction)
                    <tr class="hover:bg-stone-50" onclick="window.location='{{ route('transactions.show', ['transaction' => $transaction['id']]) }}'" style="cursor: pointer;">
                        <td class="px-4 py-3">{{ $transaction['date'] ?? '' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $transaction['type_badge_class'] ?? '' }}">
                                {{ $transaction['type_label'] ?? '' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if (($transaction['source'] ?? '') === 'invoice' && !empty($transaction['invoice_id']))
                                <a href="{{ route('invoices.show', ['invoice' => $transaction['invoice_id']]) }}" class="font-semibold text-stone-700 hover:text-stone-900" onclick="event.stopPropagation();">
                                    Invoice {{ $transaction['invoice_no'] ?? '' }}
                                </a>
                                <p class="text-xs text-stone-500">Customer: {{ $transaction['customer_name'] ?? '—' }}</p>
                            @else
                                <span class="text-sm text-stone-600">Manual entry</span>
                                @if (!empty($transaction['description'] ?? ''))
                                    <p class="text-xs text-stone-500">{{ $transaction['description'] ?? '' }}</p>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $transaction['vendor_name'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $transaction['category_name'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold {{ $transaction['amount_class'] ?? '' }}">{{ $transaction['amount_label'] ?? '' }}</td>
                        <td class="px-4 py-3 text-right" onclick="event.stopPropagation();">
                            <div class="relative inline-flex" x-data="{ open: false }">
                                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-stone-200 text-stone-500 hover:bg-stone-50" x-on:click="open = !open" x-bind:aria-expanded="open.toString()" aria-haspopup="true">
                                    <span class="sr-only">Open actions</span>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <circle cx="10" cy="4" r="1.5"></circle>
                                        <circle cx="10" cy="10" r="1.5"></circle>
                                        <circle cx="10" cy="16" r="1.5"></circle>
                                    </svg>
                                </button>
                                <div class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-stone-200 bg-white py-2 text-sm text-stone-700 shadow-lg" x-cloak x-show="open" x-on:click.outside="open = false">
                                    <a href="{{ route('transactions.show', ['transaction' => $transaction['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        View
                                    </a>
                                    <a href="{{ route('transactions.edit', ['transaction' => $transaction['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-stone-500">Add your first transaction to start tracking.</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('components/pagination', ['pagination' => $pagination ?? []])
    </div>
</div>
@endsection
