@layout('layouts.app', ['title' => 'Customer Details'])

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-stone-200 text-sm font-semibold text-stone-700">
                {{ $initials }}
            </div>
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">{{ $customerName }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $customer['email'] ?? '—' }}</p>
            </div>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('invoices.create') }}" class="rounded-xl bg-stone-800 px-5 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                New invoice
            </a>
            <a href="{{ route('customers.edit', ['customer' => $customer['id']]) }}" class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                Edit
            </a>
            <form method="POST" action="{{ route('customers.delete', ['customer' => $customer['id']]) }}" @if (!empty($canDelete)) data-confirm="Delete this customer?" @endif>
                <button
                    type="submit"
                    class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold {{ !empty($canDelete) ? 'text-rose-600 hover:bg-rose-50' : 'cursor-not-allowed text-stone-300' }}"
                    @if (empty($canDelete)) disabled title="Cannot delete while invoices or transactions exist" @endif
                >
                    Delete
                </button>
            </form>
            <a href="{{ route('customers.index') }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                Back
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

    <div class="grid items-start gap-6 lg:grid-cols-[220px_1fr]">
        <div class="space-y-6">
            <div>
                <p class="text-xs uppercase tracking-widest text-stone-400">Address</p>
                <p class="mt-2 text-sm text-stone-700 whitespace-pre-line">{{ $customer['address'] ?? 'No mailing address' }}</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 text-center shadow-sm">
                    <p class="text-2xl font-semibold text-stone-900">
                        {{ $totalsLabels['overdue'] ?? '' }}
                    </p>
                    <p class="mt-1 text-xs uppercase tracking-widest text-stone-400">Overdue</p>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 text-center shadow-sm">
                    <p class="text-2xl font-semibold text-stone-900">
                        {{ $totalsLabels['open'] ?? '' }}
                    </p>
                    <p class="mt-1 text-xs uppercase tracking-widest text-stone-400">Open</p>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 text-center shadow-sm">
                    <p class="text-2xl font-semibold text-stone-900">
                        {{ $totalsLabels['paid'] ?? '' }}
                    </p>
                    <p class="mt-1 text-xs uppercase tracking-widest text-stone-400">Paid</p>
                </div>
            </div>

            <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100">
                        <thead class="text-left text-xs font-semibold uppercase tracking-wider text-stone-500 rounded-t-xl">
                            <tr>
                                <th class="px-4 py-3">Due date</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Invoice</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-sm text-stone-700">
                            @foreach ($invoiceRows as $invoice)
                                <tr class="invoice-row hover:bg-stone-50" onclick="window.location='{{ route('invoices.show', ['invoice' => $invoice['id']]) }}'" style="cursor: pointer;">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-stone-900">{{ $invoice['due_date'] ?? '—' }}</p>
                                        <p class="text-xs text-stone-500">Invoice date {{ $invoice['date'] ?? '—' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $invoice['badge_class'] ?? '' }}">
                                            {{ $invoice['status_label'] ?? '' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('invoices.show', ['invoice' => $invoice['id']]) }}" class="font-semibold text-stone-900 hover:text-stone-500" onclick="event.stopPropagation();">
                                            {{ $invoice['invoice_no'] ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold">
                                        {{ $invoice['total_label'] ?? '' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-stone-500">No invoices for this customer yet.</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
