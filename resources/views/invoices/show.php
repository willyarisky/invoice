@layout('layouts.app', ['title' => 'Invoice ' . ($invoice['invoice_no'] ?? '')])

@section('content')
@php
    $status = strtolower((string) ($invoice['status'] ?? 'draft'));
    $statusColors = [
        'paid' => 'bg-green-100 text-green-800 border-green-200',
        'sent' => 'bg-blue-100 text-blue-800 border-blue-200',
        'draft' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    ];
    $badge = $statusColors[$status] ?? 'bg-stone-100 text-stone-700 border-stone-200';
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <a href="{{ route('invoices.index') }}" class="text-sm text-stone-500 hover:text-stone-800">&larr; Back to invoices</a>
        <div class="flex gap-3">
            <button type="button" onclick="window.print()" class="rounded-lg border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50 print:hidden">Print invoice</button>
            <a href="{{ route('invoices.create') }}" class="rounded-lg bg-stone-800 px-5 py-2 text-sm font-semibold text-white hover:bg-stone-700">Duplicate</a>
        </div>
    </div>
    <div class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 border-b border-stone-100 pb-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-stone-400">Invoice</p>
                <p class="text-3xl font-semibold text-stone-900">{{ $invoice['invoice_no'] ?? 'N/A' }}</p>
                <div class="mt-2 flex flex-wrap gap-3 text-sm text-stone-600">
                    <span>Issued {{ $invoice['date'] ?? '—' }}</span>
                    <span>Due {{ $invoice['due_date'] ?? '—' }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-lg border px-4 py-1 text-sm font-semibold {{ $badge }}">
                    {{ ucfirst($status) }}
                </span>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-widest text-stone-400">Balance</p>
                    <p class="text-3xl font-semibold text-stone-900">{{ \App\Models\Setting::formatMoney((float) ($invoice['total'] ?? 0), $invoice['currency'] ?? null) }}</p>
                </div>
            </div>
        </div>
        <div class="grid gap-6 py-6 md:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-widest text-stone-400">Bill to</p>
                <p class="text-lg font-semibold text-stone-900">{{ $invoice['client_name'] ?? 'Client' }}</p>
                <p class="text-sm text-stone-600">{{ $invoice['client_email'] ?? 'No email set' }}</p>
                <p class="mt-2 text-sm text-stone-500 whitespace-pre-line">{{ $invoice['client_address'] ?? '—' }}</p>
            </div>
            <div class="print:hidden">
                <p class="text-xs uppercase tracking-widest text-stone-400">Notes</p>
                <p class="text-sm text-stone-600">Tailwind-ready layout keeps everything crisp when exported to PDF.</p>
            </div>
        </div>
        <div class="overflow-hidden rounded-lg border border-stone-200">
            <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-widest text-stone-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Unit price</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($items as $item)
                        <tr>
                            <td class="px-4 py-3">{{ $item['description'] ?? '' }}</td>
                            <td class="px-4 py-3 text-right">{{ $item['qty'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-right">{{ \App\Models\Setting::formatMoney((float) ($item['unit_price'] ?? 0), $invoice['currency'] ?? null) }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ \App\Models\Setting::formatMoney((float) ($item['subtotal'] ?? 0), $invoice['currency'] ?? null) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6 flex flex-col items-end gap-2 text-right">
            <div class="text-sm text-stone-500">Total due</div>
            <div class="text-4xl font-semibold text-stone-900">{{ \App\Models\Setting::formatMoney((float) ($invoice['total'] ?? 0), $invoice['currency'] ?? null) }}</div>
        </div>
    </div>
</div>
@endsection
