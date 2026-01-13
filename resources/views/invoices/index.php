@layout('layouts.app', ['title' => 'Invoices'])

@section('content')
<div class="space-y-6" x-data="{ search: '', get term() { return this.search.toLowerCase(); } }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Invoices</h1>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input type="search" placeholder="Search by client or invoice #" class="w-full sm:w-64 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm focus:border-stone-400 focus:outline-none" x-model="search">
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center rounded-xl bg-stone-800 px-5 py-2 text-sm font-semibold text-white hover:bg-stone-700">New Invoice</a>
        </div>
    </div>
    @if (!empty($filterClient))
        <div class="flex flex-wrap items-center gap-3 text-sm text-stone-600">
            <span class="rounded-xl border border-stone-200 bg-white px-3 py-1">Filtered by {{ $filterClient['name'] ?? 'Client' }}</span>
            <a href="{{ route('invoices.index') }}" class="font-semibold text-stone-600 hover:text-stone-900">Clear filter</a>
        </div>
    @endif
    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-100" id="invoice-table">
            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Issued</th>
                    <th class="px-4 py-3">Due</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 text-sm text-stone-700">
                @foreach ($invoices as $invoice)
                    <tr class="invoice-row hover:bg-stone-50" x-show="term === '' || $el.dataset.search.includes(term)" data-search="{{ $invoice['search'] ?? '' }}" onclick="window.location='{{ $invoice['show_url'] ?? '#' }}'" style="cursor: pointer;">
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                <a href="{{ $invoice['show_url'] ?? '#' }}" class="font-semibold text-stone-900 hover:text-stone-500">
                                    {{ $invoice['invoice_no'] ?? '—' }}
                                </a>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $invoice['client_name'] ?? 'Unknown client' }}</td>
                        <td class="px-4 py-3">{{ $invoice['date'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $invoice['due_date'] ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $invoice['badge_class'] ?? '' }}">
                                {{ $invoice['status_label'] ?? '' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $invoice['total_label'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-stone-500">No invoices just yet. Create one to get started!</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
