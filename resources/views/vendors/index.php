@layout('layouts.app', ['title' => 'Vendors'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Vendors</h1>
        </div>
        <div class="flex items-center gap-2 sm:w-auto sm:flex-row sm:items-center">
            <details class="relative sm:hidden">
                <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-700 shadow-sm hover:bg-stone-50" aria-label="Search vendors">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
                        <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </summary>
                <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/40 px-4 pb-6" onclick="if (event.target === this) { this.closest('details').removeAttribute('open'); }">
                    <div class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white px-4 py-4 shadow-xl">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-stone-900">Search vendors</p>
                            <button type="button" class="rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-500 hover:bg-stone-50" onclick="this.closest('details').removeAttribute('open')">Close</button>
                        </div>
                        <form method="GET" action="{{ route('vendors.index') }}" class="mt-3">
                            <div class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-600">
                                <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Vendor, email, or phone" class="w-full bg-transparent text-sm text-stone-600 placeholder:text-stone-400 focus:outline-none">
                            </div>
                            <button type="submit" class="mt-3 w-full rounded-xl bg-stone-900 px-3 py-2 text-xs font-semibold text-white hover:bg-stone-800">
                                Search
                            </button>
                        </form>
                    </div>
                </div>
            </details>
            <form method="GET" action="{{ route('vendors.index') }}" class="hidden items-center rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600 shadow-sm sm:flex">
                <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Search vendor, email, or phone" class="w-full bg-transparent text-sm text-stone-600 placeholder:text-stone-400 focus:outline-none sm:w-64">
            </form>
            <a href="{{ route('vendors.create') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-stone-800 text-white hover:bg-stone-700 sm:h-auto sm:w-auto sm:px-4 sm:py-2 sm:text-sm sm:font-semibold" aria-label="Add vendor">
                <svg aria-hidden="true" class="h-4 w-4 sm:hidden" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <span class="hidden sm:inline">Add vendor</span>
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
                @foreach ($vendors as $vendor)
                    <div class="px-4 py-4" onclick="window.location='{{ route('vendors.show', ['vendor' => $vendor['id']]) }}'" style="cursor: pointer;">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('vendors.show', ['vendor' => $vendor['id']]) }}" class="text-base font-semibold text-stone-900" onclick="event.stopPropagation();">
                                {{ $vendor['name'] ?? 'Vendor' }}
                            </a>
                            <span class="text-sm font-semibold text-stone-900">{{ $vendor['total_spent_label'] ?? '' }}</span>
                        </div>
                        <div class="mt-2 text-sm text-stone-600">
                            <p class="text-stone-700">{{ $vendor['email'] ?? '—' }}</p>
                            <p class="text-xs text-stone-500">{{ $vendor['phone'] ?? '—' }}</p>
                        </div>
                        <div class="mt-3 flex items-center justify-between" onclick="event.stopPropagation();">
                            <a href="{{ route('vendors.show', ['vendor' => $vendor['id']]) }}" class="rounded-lg border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">View</a>
                            <a href="{{ route('vendors.edit', ['vendor' => $vendor['id']]) }}" class="rounded-lg border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50">Edit</a>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-stone-500">Add a vendor to track expenses.</div>
                @endforeach
            </div>
        </div>
        <table class="hidden min-w-full divide-y divide-stone-100 text-sm text-stone-700 lg:table">
            <thead class="text-left text-xs font-semibold uppercase tracking-widest text-stone-500 rounded-t-xl">
                <tr>
                    <th class="px-4 py-3">Vendor</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3 text-right">Total spent</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($vendors as $vendor)
                    <tr class="hover:bg-stone-50" onclick="window.location='{{ route('vendors.show', ['vendor' => $vendor['id']]) }}'" style="cursor: pointer;">
                        <td class="px-4 py-3">
                            <a href="{{ route('vendors.show', ['vendor' => $vendor['id']]) }}" class="font-semibold text-stone-900 hover:text-stone-500" onclick="event.stopPropagation();">
                                {{ $vendor['name'] ?? 'Vendor' }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $vendor['email'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $vendor['phone'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $vendor['total_spent_label'] ?? '' }}</td>
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
                                    <a href="{{ route('vendors.show', ['vendor' => $vendor['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        View
                                    </a>
                                    <a href="{{ route('vendors.edit', ['vendor' => $vendor['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50" onclick="event.stopPropagation();">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('vendors.delete', ['vendor' => $vendor['id']]) }}" @if (!empty($vendor['can_delete'] ?? false)) data-confirm="Delete this vendor?" @endif>
                                        <button
                                            type="submit"
                                            class="flex w-full items-center px-4 py-2 font-semibold {{ !empty($vendor['can_delete'] ?? false) ? 'text-rose-600 hover:bg-rose-50' : 'cursor-not-allowed text-stone-300' }}"
                                            @if (empty($vendor['can_delete'] ?? false)) disabled title="Cannot delete while transactions exist" @endif
                                            onclick="event.stopPropagation();"
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
                        <td colspan="5" class="px-4 py-6 text-center text-stone-500">Add a vendor to track expenses.</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('components/pagination', ['pagination' => $pagination ?? []])
    </div>
</div>
@endsection
