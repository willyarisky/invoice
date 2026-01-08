@layout('layouts.app', ['title' => 'Clients'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm text-stone-500">Keep your client directory close and understand their value.</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600">
            {{ count($clients ?? []) }} clients tracked
        </div>
    </div>
    <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
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
                        <td class="px-4 py-3 text-right font-semibold">${{ number_format((float) ($client['lifetime_value'] ?? 0), 2) }}</td>
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
