@layout('layouts.app', ['title' => 'Invoices'])

@section('content')
<div class="space-y-6" x-data="{ search: '', get term() { return this.search.toLowerCase(); } }">
    <div class="flex items-center justify-between gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Invoices</h1>
        </div>
        <div class="flex items-center gap-2 sm:w-auto sm:flex-row sm:items-center">
            <details class="relative sm:hidden">
                <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-700 shadow-sm hover:bg-stone-50" aria-label="Search invoices">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
                        <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </summary>
                <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/40 px-4 pb-6" onclick="if (event.target === this) { this.closest('details').removeAttribute('open'); }">
                    <div class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white px-4 py-4 shadow-xl">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-stone-900">Search invoices</p>
                            <button type="button" class="rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-500 hover:bg-stone-50" onclick="this.closest('details').removeAttribute('open')">Close</button>
                        </div>
                        <div class="mt-3 flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                            <input type="search" placeholder="Customer or invoice #" class="w-full bg-transparent text-sm text-stone-600 placeholder:text-stone-400 focus:outline-none" x-model="search">
                        </div>
                        <button type="button" class="mt-3 w-full rounded-xl bg-stone-900 px-3 py-2 text-xs font-semibold text-white hover:bg-stone-800" onclick="this.closest('details').removeAttribute('open')">
                            Search
                        </button>
                    </div>
                </div>
            </details>
            <div class="hidden items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600 shadow-sm sm:flex">
                <input type="search" placeholder="Search by customer or invoice #" class="w-full bg-transparent text-sm text-stone-600 placeholder:text-stone-400 focus:outline-none sm:w-64" x-model="search">
            </div>
            <a href="{{ route('invoices.create') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-stone-800 text-white hover:bg-stone-700 sm:h-auto sm:w-auto sm:px-5 sm:py-2 sm:text-sm sm:font-semibold" aria-label="New Invoice">
                <svg aria-hidden="true" class="h-4 w-4 sm:hidden" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <span class="hidden sm:inline">New Invoice</span>
            </a>
        </div>
    </div>
    @if (!empty($filterCustomer))
        <div class="flex flex-wrap items-center gap-3 text-sm text-stone-600">
            <span class="rounded-xl border border-stone-200 bg-white px-3 py-1">Filtered by {{ $filterCustomer['name'] ?? 'Customer' }}</span>
            <a href="{{ route('invoices.index') }}" class="font-semibold text-stone-600 hover:text-stone-900">Clear filter</a>
        </div>
    @endif
    <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
        <div class="lg:hidden">
            <div class="divide-y divide-stone-100">
                @foreach ($invoices as $invoice)
                    <div class="invoice-row px-4 py-4" x-show="term === '' || $el.dataset.search.includes(term)" data-search="{{ $invoice['search'] ?? '' }}">
                        <div class="flex items-center justify-between">
                            <a href="{{ $invoice['show_url'] ?? '#' }}" class="text-base font-semibold text-stone-900">
                                {{ $invoice['invoice_no'] ?? '—' }}
                            </a>
                            <span class="inline-flex items-center rounded-xl px-3 py-1 text-[11px] font-semibold {{ $invoice['badge_class'] ?? '' }}">
                                {{ $invoice['status_label'] ?? '' }}
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-stone-600">
                            <p class="font-medium text-stone-800">{{ $invoice['customer_name'] ?? 'Unknown customer' }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-stone-500">
                                <span>Issued {{ $invoice['date'] ?? '—' }}</span>
                                <span>Due {{ $invoice['due_date'] ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm font-semibold text-stone-900">{{ $invoice['total_label'] ?? '' }}</span>
                            <div class="flex items-center gap-2">
                                <a href="{{ $invoice['show_url'] ?? '#' }}" class="rounded-lg border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">View</a>
                                <a href="{{ route('invoices.edit', ['invoice' => $invoice['id'] ?? 0]) }}" class="rounded-lg border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">Edit</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-stone-500">No invoices just yet. Create one to get started!</div>
                @endforeach
            </div>
        </div>
        <table class="hidden min-w-full divide-y divide-stone-100 lg:table" id="invoice-table">
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
