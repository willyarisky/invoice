@layout('layouts.app', ['title' => 'Transactions'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Transactions</h1>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600">
                {{ $transactionCount ?? 0 }} transactions recorded
            </div>
            <a href="{{ route('transactions.create') }}" class="rounded-xl bg-stone-800 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                Add transaction
            </a>
        </div>
    </div>

    @if (!empty($status ?? ''))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ $status ?? '' }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Source</th>
                    <th class="px-4 py-3">Vendor</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-right">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($transactions as $transaction)
                    <tr>
                        <td class="px-4 py-3">{{ $transaction['date'] ?? '' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $transaction['type_badge_class'] ?? '' }}">
                                {{ $transaction['type_label'] ?? '' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if (($transaction['source'] ?? '') === 'invoice' && !empty($transaction['invoice_id']))
                                <a href="{{ route('invoices.show', ['invoice' => $transaction['invoice_id']]) }}" class="font-semibold text-stone-700 hover:text-stone-900">
                                    Invoice {{ $transaction['invoice_no'] ?? '' }}
                                </a>
                                <p class="text-xs text-stone-500">Client: {{ $transaction['client_name'] ?? '—' }}</p>
                            @else
                                <span class="text-sm text-stone-600">Manual entry</span>
                                @if (!empty($transaction['description'] ?? ''))
                                    <p class="text-xs text-stone-500">{{ $transaction['description'] ?? '' }}</p>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $transaction['vendor_name'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold {{ $transaction['amount_class'] ?? '' }}">{{ $transaction['amount_label'] ?? '' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('transactions.show', ['transaction' => $transaction['id']]) }}" class="text-sm font-semibold text-stone-600 hover:text-stone-900">
                                    View
                                </a>
                                <a href="{{ route('transactions.edit', ['transaction' => $transaction['id']]) }}" class="text-sm font-semibold text-stone-600 hover:text-stone-900">
                                    Edit
                                </a>
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
    </div>
</div>
@endsection
