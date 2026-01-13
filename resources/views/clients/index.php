@layout('layouts.app', ['title' => 'Customers'])

@section('content')
<div class="space-y-8" x-data="{ search: '', get term() { return this.search.toLowerCase(); } }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Customers</h1>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input type="search" placeholder="Search client" class="w-full sm:w-64 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm focus:border-stone-400 focus:outline-none" x-model="search">
            <a href="{{ route('clients.create') }}" class="inline-flex items-center justify-center rounded-xl bg-stone-800 px-5 py-2 text-sm font-semibold text-white hover:bg-stone-700">New Customer</a>
        </div>
    </div>

    @if (!empty($status ?? ''))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ $status ?? '' }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700" id="clients-table">
            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" class="h-4 w-4 rounded-xl border-stone-300 text-stone-700">
                    </th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3 text-right">Total invoice</th>
                    <th class="px-4 py-3 text-right">Paid</th>
                    <th class="px-4 py-3 text-right">Overdue</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($clients as $client)
                    <tr class="client-row" x-show="term === '' || $el.dataset.search.includes(term)" data-search="{{ $client['search'] ?? '' }}">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="h-4 w-4 rounded-xl border-stone-300 text-stone-700">
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-stone-900">{{ $client['name'] ?? 'Client' }}</p>
                            <p class="text-xs text-stone-500">Tax Number</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-stone-700">{{ $client['email'] ?? '—' }}</p>
                            <p class="text-xs text-stone-500">Phone</p>
                        </td>
                        <td class="px-4 py-3 text-right text-stone-700">{{ $client['total_label'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-stone-700">{{ $client['paid_label'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-stone-700">{{ $client['overdue_label'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('clients.show', ['client' => $client['id']]) }}" class="rounded-xl border border-stone-200 px-2 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">
                                    View
                                </a>
                                <a href="{{ route('clients.edit', ['client' => $client['id']]) }}" class="rounded-xl border border-stone-200 px-2 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-stone-500">Add your first client to start invoicing.</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
