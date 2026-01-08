@layout('layouts.app', ['title' => 'Invoices'])

@section('content')
@php
    $statusColors = [
        'paid' => 'bg-green-100 text-green-800',
        'sent' => 'bg-blue-100 text-blue-800',
        'draft' => 'bg-yellow-100 text-yellow-800',
    ];
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm text-stone-500">Monitor every invoice from draft to payment.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input type="search" placeholder="Search by client or invoice #" class="w-full sm:w-64 rounded-lg border border-stone-200 bg-white px-4 py-2 text-sm focus:border-stone-400 focus:outline-none" oninput="filterInvoiceTable(this.value)">
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center rounded-lg bg-stone-800 px-5 py-2 text-sm font-semibold text-white hover:bg-stone-700">Add invoice</a>
        </div>
    </div>
    <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-stone-100" id="invoice-table">
            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Issued</th>
                    <th class="px-4 py-3">Due</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 text-sm text-stone-700">
                @foreach ($invoices as $invoice)
                    @php
                        $status = strtolower((string) ($invoice['status'] ?? 'draft'));
                        $badge = $statusColors[$status] ?? 'bg-stone-100 text-stone-700';
                    @endphp
                    <tr class="invoice-row hover:bg-stone-50">
                        <td class="px-4 py-3 font-semibold text-stone-900">
                            <a href="{{ route('invoices.show', ['invoice' => $invoice['id']]) }}" class="hover:text-stone-500">
                                {{ $invoice['invoice_no'] ?? '—' }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $invoice['client_name'] ?? 'Unknown client' }}</td>
                        <td class="px-4 py-3">{{ $invoice['date'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $invoice['due_date'] ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-semibold {{ $badge }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ \App\Models\Setting::formatMoney((float) ($invoice['total'] ?? 0), $invoice['currency'] ?? null) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-stone-500">No invoices just yet. Create one to get started!</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    function filterInvoiceTable(value) {
        const rows = document.querySelectorAll('#invoice-table .invoice-row');
        const term = value.toLowerCase();
        rows.forEach((row) => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    }
</script>
@endsection
