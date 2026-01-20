@layout('layouts.app', ['title' => 'Customers'])

@section('content')
<div class="space-y-8" x-data="{ search: '', get term() { return this.search.toLowerCase(); } }">
    <div class="flex items-center justify-between gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Customers</h1>
        </div>
        <div class="flex items-center gap-2 sm:w-auto sm:flex-row sm:items-center">
            <details class="relative sm:hidden">
                <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-700 shadow-sm hover:bg-stone-50" aria-label="Search customers">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
                        <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </summary>
                <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/40 px-4 pb-6" onclick="if (event.target === this) { this.closest('details').removeAttribute('open'); }">
                    <div class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white px-4 py-4 shadow-xl">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-stone-900">Search customers</p>
                            <button type="button" class="rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-500 hover:bg-stone-50" onclick="this.closest('details').removeAttribute('open')">Close</button>
                        </div>
                        <div class="mt-3 flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                            <input type="search" placeholder="Customer name or email" class="w-full bg-transparent text-sm text-stone-600 placeholder:text-stone-400 focus:outline-none" x-model="search">
                        </div>
                        <button type="button" class="mt-3 w-full rounded-xl bg-stone-900 px-3 py-2 text-xs font-semibold text-white hover:bg-stone-800" onclick="this.closest('details').removeAttribute('open')">
                            Search
                        </button>
                    </div>
                </div>
            </details>
            <div class="hidden items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600 shadow-sm sm:flex">
                <input type="search" placeholder="Search customer" class="w-full bg-transparent text-sm text-stone-600 placeholder:text-stone-400 focus:outline-none sm:w-64" x-model="search">
            </div>
            <a href="{{ route('customers.create') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-stone-800 text-white hover:bg-stone-700 sm:h-auto sm:w-auto sm:px-5 sm:py-2 sm:text-sm sm:font-semibold" aria-label="New Customer">
                <svg aria-hidden="true" class="h-4 w-4 sm:hidden" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <span class="hidden sm:inline">New Customer</span>
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
                @foreach ($customers as $customer)
                    <div class="customer-row px-4 py-4" x-show="term === '' || $el.dataset.search.includes(term)" data-search="{{ $customer['search'] ?? '' }}">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('customers.show', ['customer' => $customer['id']]) }}" class="text-base font-semibold text-stone-900">
                                {{ $customer['name'] ?? 'Customer' }}
                            </a>
                            <span class="text-sm font-semibold text-stone-900">{{ $customer['total_label'] ?? '—' }}</span>
                        </div>
                        <div class="mt-2 text-sm text-stone-600">
                            <p class="text-stone-700">{{ $customer['email'] ?? '—' }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-stone-500">
                                <span>Paid {{ $customer['paid_label'] ?? '—' }}</span>
                                <span>Overdue {{ $customer['overdue_label'] ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <a href="{{ route('customers.show', ['customer' => $customer['id']]) }}" class="rounded-lg border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">View</a>
                            <a href="{{ route('customers.edit', ['customer' => $customer['id']]) }}" class="rounded-lg border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">Edit</a>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-stone-500">Add your first customer to start invoicing.</div>
                @endforeach
            </div>
        </div>
        <table class="hidden min-w-full divide-y divide-stone-100 text-sm text-stone-700 lg:table" id="customers-table">
            <thead class="text-left text-xs font-semibold uppercase tracking-widest text-stone-500 rounded-t-xl">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3 text-right">Total invoice</th>
                    <th class="px-4 py-3 text-right">Paid</th>
                    <th class="px-4 py-3 text-right">Overdue</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($customers as $customer)
                    <tr class="customer-row hover:bg-stone-50" x-show="term === '' || $el.dataset.search.includes(term)" data-search="{{ $customer['search'] ?? '' }}" onclick="window.location='{{ route('customers.show', ['customer' => $customer['id']]) }}'" style="cursor: pointer;">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-stone-900">{{ $customer['name'] ?? 'Customer' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-stone-700">{{ $customer['email'] ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right text-stone-700">{{ $customer['total_label'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-stone-700">{{ $customer['paid_label'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-stone-700">{{ $customer['overdue_label'] ?? '—' }}</td>
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
                                    <a href="{{ route('customers.show', ['customer' => $customer['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        View
                                    </a>
                                    <a href="{{ route('customers.edit', ['customer' => $customer['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('customers.delete', ['customer' => $customer['id']]) }}" @if (!empty($customer['can_delete'] ?? false)) data-confirm="Delete this customer?" @endif>
                                        <button
                                            type="submit"
                                            class="flex w-full items-center px-4 py-2 font-semibold {{ !empty($customer['can_delete'] ?? false) ? 'text-rose-600 hover:bg-rose-50' : 'cursor-not-allowed text-stone-300' }}"
                                            @if (empty($customer['can_delete'] ?? false)) disabled title="Cannot delete while invoices or transactions exist" @endif
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-stone-500">Add your first customer to start invoicing.</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('components/pagination', ['pagination' => $pagination ?? []])
    </div>
</div>
@endsection
