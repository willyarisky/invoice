@layout('layouts.app', ['title' => 'Vendor Details'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">{{ $vendor['name'] ?? 'Vendor' }}</h1>
            <p class="mt-1 text-sm text-stone-500">Vendor details &amp; transactions</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('vendors.index') }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                Back to vendors
            </a>
        </div>
    </div>

    @if (!empty($status ?? ''))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ $status ?? '' }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
        <div class="space-y-4">
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Contact</p>
                <p class="mt-2 text-sm font-semibold text-stone-900">{{ $vendor['name'] ?? 'Vendor' }}</p>
                <p class="mt-1 text-xs text-stone-500">{{ $vendor['email'] ?? 'No email on file' }}</p>
                <p class="mt-1 text-xs text-stone-500">{{ $vendor['phone'] ?? 'No phone on file' }}</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Address</p>
                <p class="mt-2 text-sm text-stone-700 whitespace-pre-line">{{ $vendor['address'] ?? 'No address on file' }}</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Summary</p>
                <p class="mt-2 text-sm text-stone-500">Transactions</p>
                <p class="text-xl font-semibold text-stone-900">{{ $transactionCount ?? 0 }}</p>
                <p class="mt-3 text-sm text-stone-500">Total spent</p>
                <p class="text-xl font-semibold text-stone-900">{{ $totalSpentLabel ?? '' }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-stone-100 px-6 py-4">
                <div>
                    <p class="text-lg font-semibold text-stone-900">Transactions</p>
                    <p class="text-sm text-stone-500">All transactions linked to this vendor.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Category</th>
                            <th class="px-6 py-3">Reference</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                            <th class="px-6 py-3 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-3">{{ $transaction['date'] ?? '' }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $transaction['type_badge_class'] ?? '' }}">
                                        {{ $transaction['type_label'] ?? '' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">{{ $transaction['category_name'] ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    @if (!empty($transaction['reference_url']))
                                        <a href="{{ $transaction['reference_url'] }}" class="font-semibold text-stone-700 hover:text-stone-900">
                                            {{ $transaction['reference_label'] ?? '' }}
                                        </a>
                                    @else
                                        <span class="text-sm text-stone-600">{{ $transaction['reference_label'] ?? '' }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right font-semibold {{ $transaction['amount_class'] ?? '' }}">{{ $transaction['amount_label'] ?? '' }}</td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('transactions.show', ['transaction' => $transaction['id']]) }}" class="text-sm font-semibold text-stone-600 hover:text-stone-900">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-stone-500">No transactions recorded for this vendor.</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
