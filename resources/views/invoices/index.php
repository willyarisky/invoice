@layout('layouts.app', ['title' => 'Invoices'])

@section('content')
<div class="space-y-6" x-data="{ search: '', get term() { return this.search.toLowerCase(); } }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Invoices</h1>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600 shadow-sm">
                <input type="search" placeholder="Search by customer or invoice #" class="w-64 bg-transparent text-sm text-stone-600 placeholder:text-stone-400 focus:outline-none" x-model="search">
            </div>
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center rounded-xl bg-stone-800 px-5 py-2 text-sm font-semibold text-white hover:bg-stone-700">New Invoice</a>
        </div>
    </div>
    @if (!empty($filterCustomer))
        <div class="flex flex-wrap items-center gap-3 text-sm text-stone-600">
            <span class="rounded-xl border border-stone-200 bg-white px-3 py-1">Filtered by {{ $filterCustomer['name'] ?? 'Customer' }}</span>
            <a href="{{ route('invoices.index') }}" class="font-semibold text-stone-600 hover:text-stone-900">Clear filter</a>
        </div>
    @endif
    <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-100" id="invoice-table">
            <thead class="text-left text-xs font-semibold uppercase tracking-wider text-stone-500 rounded-t-xl">
                <tr>
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Issued</th>
                    <th class="px-4 py-3">Due</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 text-sm text-stone-700">
                @foreach ($invoices as $invoice)
                    <tr class="invoice-row hover:bg-stone-50" x-show="term === '' || $el.dataset.search.includes(term)" data-search="{{ $invoice['search'] ?? '' }}" onclick="window.location='{{ $invoice['show_url'] ?? '#' }}'" style="cursor: pointer;">
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                <a href="{{ $invoice['show_url'] ?? '#' }}" class="font-semibold text-stone-900 hover:text-stone-500" onclick="event.stopPropagation();">
                                    {{ $invoice['invoice_no'] ?? '—' }}
                                </a>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $invoice['customer_name'] ?? 'Unknown customer' }}</td>
                        <td class="px-4 py-3">{{ $invoice['date'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $invoice['due_date'] ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $invoice['badge_class'] ?? '' }}">
                                {{ $invoice['status_label'] ?? '' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $invoice['total_label'] ?? '' }}</td>
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
                                    <a href="{{ $invoice['show_url'] ?? '#' }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        View
                                    </a>
                                    <a href="{{ route('invoices.edit', ['invoice' => $invoice['id'] ?? 0]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        Edit
                                    </a>
                                    <a href="{{ route('invoices.download', ['invoice' => $invoice['id'] ?? 0]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        Download
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-stone-500">No invoices just yet. Create one to get started!</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('components/pagination', ['pagination' => $pagination ?? []])
    </div>
</div>
@endsection
