@layout('layouts.app', ['title' => 'Clients'])

@section('content')
<div class="space-y-6">
    <div class="rounded-lg border border-stone-200 bg-white px-6 py-6 shadow-sm">
        <div>
            <p class="text-xs uppercase tracking-widest text-stone-400">Clients</p>
            <p class="mt-2 text-2xl font-semibold text-stone-900">Add a client</p>
            <p class="mt-2 text-sm text-stone-500">Store client details to unlock invoice creation.</p>
        </div>

        @if (!empty($status ?? ''))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ $status ?? '' }}
            </div>
        @endif

        <form method="POST" action="{{ route('clients.store') }}" class="mt-6 grid gap-4 lg:grid-cols-2">
            <label class="flex flex-col text-sm font-medium text-stone-700">
                Client name
                <input type="text" name="name" value="{{ $old['name'] ?? '' }}" class="mt-1 rounded-lg border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['name']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Email address
                <input type="email" name="email" value="{{ $old['email'] ?? '' }}" class="mt-1 rounded-lg border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['email']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['email'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                Mailing address
                <textarea name="address" rows="3" class="mt-1 rounded-lg border border-stone-200 bg-white px-4 py-2 text-stone-700">{{ $old['address'] ?? '' }}</textarea>
                @if (isset($errors['address']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['address'] ?? '' }}</span>
                @endif
            </label>

            <div class="flex justify-end gap-3 lg:col-span-2">
                <button type="submit" class="rounded-lg bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                    Save client
                </button>
            </div>
        </form>
    </div>

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm text-stone-500">Keep your client directory close and understand their value.</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600">
            {{ count($clients ?? []) }} clients tracked
        </div>
    </div>
    <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                <tr>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Invoices</th>
                    <th class="px-4 py-3 text-right">Lifetime value</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($clients as $client)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-stone-900">{{ $client['name'] ?? 'Client' }}</p>
                            <p class="text-xs text-stone-500">{{ $client['address'] ?? 'No mailing address' }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $client['email'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $client['invoice_count'] ?? 0 }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ \App\Models\Setting::formatMoney((float) ($client['lifetime_value'] ?? 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-stone-500">Add your first client to start invoicing.</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
