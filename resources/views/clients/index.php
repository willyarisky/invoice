@layout('layouts.app', ['title' => 'Clients'])

@section('content')
<div class="space-y-8" x-data="{ search: '', get term() { return this.search.toLowerCase(); } }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Clients</h1>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input type="search" placeholder="Search client" class="w-full sm:w-64 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm focus:border-stone-400 focus:outline-none" x-model="search">
            <a href="{{ route('clients.create') }}" class="inline-flex items-center justify-center rounded-xl bg-stone-800 px-5 py-2 text-sm font-semibold text-white hover:bg-stone-700">New Clients</a>
        </div>
    </div>

    @if (!empty($status ?? ''))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ $status ?? '' }}
        </div>
    @endif

    <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700" id="clients-table">
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
                @foreach ($clients as $client)
                    <tr class="client-row hover:bg-stone-50" x-show="term === '' || $el.dataset.search.includes(term)" data-search="{{ $client['search'] ?? '' }}" onclick="window.location='{{ route('clients.show', ['client' => $client['id']]) }}'" style="cursor: pointer;">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-stone-900">{{ $client['name'] ?? 'Client' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-stone-700">{{ $client['email'] ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right text-stone-700">{{ $client['total_label'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-stone-700">{{ $client['paid_label'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-stone-700">{{ $client['overdue_label'] ?? '—' }}</td>
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
                                    <a href="{{ route('clients.show', ['client' => $client['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        View
                                    </a>
                                    <a href="{{ route('clients.edit', ['client' => $client['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        Edit
                                    </a>
                                </div>
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
