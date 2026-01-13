@layout('layouts.app', ['title' => 'Vendors'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Vendors</h1>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600">
                {{ $vendorCount ?? 0 }} vendors tracked
            </div>
            <a href="{{ route('vendors.create') }}" class="rounded-xl bg-stone-800 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                Add vendor
            </a>
        </div>
    </div>

    @if (!empty($status ?? ''))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ $status ?? '' }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                <tr>
                    <th class="px-4 py-3">Vendor</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Phone</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($vendors as $vendor)
                    <tr>
                        <td class="px-4 py-3">
                            <a href="{{ route('vendors.show', ['vendor' => $vendor['id']]) }}" class="font-semibold text-stone-900 hover:text-stone-500">
                                {{ $vendor['name'] ?? 'Vendor' }}
                            </a>
                            <p class="text-xs text-stone-500">{{ $vendor['address'] ?? 'No address on file' }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $vendor['email'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $vendor['phone'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-stone-500">Add a vendor to track expenses.</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
