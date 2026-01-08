@layout('layouts.app', ['title' => 'Dashboard'])

@section('content')
@php
    $statusColors = [
        'paid' => 'bg-green-100 text-green-800',
        'sent' => 'bg-blue-100 text-blue-800',
        'draft' => 'bg-yellow-100 text-yellow-800',
    ];
@endphp
<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
            <p class="text-xs uppercase tracking-widest text-stone-400">Total invoices</p>
            <p class="mt-2 text-3xl font-semibold text-stone-900">{{ $metrics['totalInvoices'] ?? 0 }}</p>
        </div>
        <div class="rounded-3xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
            <p class="text-xs uppercase tracking-widest text-stone-400">Active clients</p>
            <p class="mt-2 text-3xl font-semibold text-stone-900">{{ $metrics['totalClients'] ?? 0 }}</p>
        </div>
        <div class="rounded-3xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
            <p class="text-xs uppercase tracking-widest text-stone-400">Outstanding</p>
            <p class="mt-2 text-3xl font-semibold text-stone-900">${{ number_format((float) ($metrics['outstanding'] ?? 0), 2) }}</p>
        </div>
    </div>
    <div class="rounded-3xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
        <p class="text-xs uppercase tracking-widest text-stone-400">Status breakdown</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            @foreach ($statusSummary as $label => $count)
                <div class="rounded-2xl border border-stone-100 bg-stone-50 px-4 py-4 text-center">
                    <p class="text-sm font-semibold text-stone-700">{{ ucfirst($label) }}</p>
                    <p class="text-2xl font-semibold text-stone-900">{{ $count }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <div class="rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-stone-100 px-6 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-lg font-semibold text-stone-900">Recent invoices</p>
                <p class="text-sm text-stone-500">A quick snapshot of the latest activity.</p>
            </div>
            <a href="{{ route('invoices.index') }}" class="text-sm text-stone-500 hover:text-stone-800">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                    <tr>
                        <th class="px-6 py-3">Invoice</th>
                        <th class="px-6 py-3">Client</th>
                        <th class="px-6 py-3">Issued</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($recentInvoices as $invoice)
                        @php
                            $status = strtolower((string) ($invoice['status'] ?? 'draft'));
                            $badge = $statusColors[$status] ?? 'bg-stone-100 text-stone-700';
                        @endphp
                        <tr>
                            <td class="px-6 py-3 font-semibold text-stone-900">
                                <a href="{{ route('invoices.show', ['invoice' => $invoice['id']]) }}" class="hover:text-stone-500">{{ $invoice['invoice_no'] ?? '—' }}</a>
                            </td>
                            <td class="px-6 py-3">{{ $invoice['client_name'] ?? 'Unknown client' }}</td>
                            <td class="px-6 py-3">{{ $invoice['date'] ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ ucfirst($status) }}</span>
                            </td>
                            <td class="px-6 py-3 text-right font-semibold">${{ number_format((float) ($invoice['total'] ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-5 text-center text-stone-500">No invoices yet. Create one to populate your dashboard!</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
