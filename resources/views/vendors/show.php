@layout('layouts.app', ['title' => 'Vendor Details'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">{{ $vendor['name'] ?? 'Vendor' }}</h1>
            <a href="{{ route('vendors.index') }}" class="mt-1 inline-flex text-sm text-stone-500 hover:text-stone-800">&larr; Back to vendors</a>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('vendors.edit', ['vendor' => $vendor['id']]) }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                Edit vendor
            </a>
            <form method="POST" action="{{ route('vendors.delete', ['vendor' => $vendor['id']]) }}" @if (!empty($canDelete)) data-confirm="Delete this vendor?" @endif>
                <button
                    type="submit"
                    class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold {{ !empty($canDelete) ? 'text-rose-600 hover:bg-rose-50' : 'cursor-not-allowed text-stone-300' }}"
                    @if (empty($canDelete)) disabled title="Cannot delete while transactions exist" @endif
                >
                    Delete vendor
                </button>
            </form>
        </div>
    </div>

    @include('components/alerts', [
        'status' => $status ?? null,
        'errors' => $errors ?? [],
    ])

    <div class="grid items-start gap-6 lg:grid-cols-[280px_1fr]">
        <div class="space-y-4">
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Contact</p>
                <p class="mt-2 text-sm font-semibold text-stone-900">{{ $vendor['name'] ?? 'Vendor' }}</p>
                <p class="mt-1 text-xs text-stone-500">{{ $vendor['email'] ?? 'No email on file' }}</p>
                @if (!empty($vendor['phone'] ?? ''))
                    <p class="mt-1 text-xs text-stone-500">{{ $vendor['phone'] }}</p>
                @endif
            </div>
            @php
                $addressValue = trim((string) ($vendor['address'] ?? ''));
                $addressLines = array_values(array_filter(
                    preg_split('/\R/', $addressValue) ?: [],
                    static fn (string $line): bool => trim($line) !== ''
                ));
                $singleLine = count($addressLines) === 1;
                $addressLine = $addressLines[0] ?? '';
                $wordCount = str_word_count($addressLine);
                $hasDigits = preg_match('/\d/', $addressLine) === 1;
                $hasComma = str_contains($addressLine, ',');
                $showAddress = $addressValue !== '' && !($singleLine && ! $hasDigits && ! $hasComma && $wordCount <= 3);
            @endphp
            @if ($showAddress)
                <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                    <p class="text-xs uppercase tracking-widest text-stone-400">Address</p>
                    <p class="mt-2 text-sm text-stone-700 whitespace-pre-line">{{ $vendor['address'] }}</p>
                </div>
            @endif
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Summary</p>
                <p class="mt-2 text-sm text-stone-500">Transactions</p>
                <p class="text-xl font-semibold text-stone-900">{{ $transactionCount ?? 0 }}</p>
                <p class="mt-3 text-sm text-stone-500">Total spent</p>
                <p class="text-xl font-semibold text-stone-900">{{ $totalSpentLabel ?? '' }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
            <div class="divide-y divide-stone-100 lg:hidden">
                @foreach ($transactions as $transaction)
                    <div class="px-4 py-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-stone-900">{{ $transaction['date'] ?? '' }}</span>
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-[11px] font-semibold {{ $transaction['type_badge_class'] ?? '' }}">
                                {{ $transaction['type_label'] ?? '' }}
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-stone-600">
                            <p class="text-xs uppercase tracking-widest text-stone-400">Category</p>
                            <p class="mt-1 text-sm text-stone-700">{{ $transaction['category_name'] ?? '—' }}</p>
                            <p class="mt-3 text-xs uppercase tracking-widest text-stone-400">Reference</p>
                            @if (!empty($transaction['reference_url']))
                                <a href="{{ $transaction['reference_url'] }}" class="mt-1 inline-flex text-sm font-semibold text-stone-700 hover:text-stone-900">
                                    {{ $transaction['reference_label'] ?? '' }}
                                </a>
                            @else
                                <p class="mt-1 text-sm text-stone-600">{{ $transaction['reference_label'] ?? '' }}</p>
                            @endif
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm font-semibold {{ $transaction['amount_class'] ?? '' }}">{{ $transaction['amount_label'] ?? '' }}</span>
                            <a href="{{ route('transactions.show', ['transaction' => $transaction['id']]) }}" class="text-xs font-semibold text-stone-600 hover:text-stone-900">
                                View details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-stone-500">No transactions recorded for this vendor.</div>
                @endforeach
            </div>
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                    <thead class="text-left text-xs font-semibold uppercase tracking-widest text-stone-500 rounded-t-xl">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($transactions as $transaction)
                            <tr class="hover:bg-stone-50">
                                <td class="px-4 py-3">{{ $transaction['date'] ?? '' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $transaction['type_badge_class'] ?? '' }}">
                                        {{ $transaction['type_label'] ?? '' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $transaction['category_name'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if (!empty($transaction['reference_url']))
                                        <a href="{{ $transaction['reference_url'] }}" class="font-semibold text-stone-700 hover:text-stone-900">
                                            {{ $transaction['reference_label'] ?? '' }}
                                        </a>
                                    @else
                                        <span class="text-sm text-stone-600">{{ $transaction['reference_label'] ?? '' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold {{ $transaction['amount_class'] ?? '' }}">{{ $transaction['amount_label'] ?? '' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('transactions.show', ['transaction' => $transaction['id']]) }}" class="text-sm font-semibold text-stone-600 hover:text-stone-900">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-stone-500">No transactions recorded for this vendor.</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
