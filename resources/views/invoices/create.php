@layout('layouts.app', ['title' => 'Create Invoice'])

@section('content')
@php
    $hasClients = !empty($clients);
    $oldValues = $old ?? [];
    $selectedClient = $oldValues['client_id'] ?? '';
    $selectedStatus = strtolower((string) ($oldValues['status'] ?? 'draft'));
    $lineItems = $oldValues['items'] ?? [
        ['description' => '', 'qty' => 1, 'unit_price' => '0.00', 'subtotal' => '0.00'],
    ];
    if (empty($lineItems)) {
        $lineItems = [['description' => '', 'qty' => 1, 'unit_price' => '0.00', 'subtotal' => '0.00']];
    }
@endphp
<div class="space-y-6">
    @if ($error ?? false)
        <div class="rounded-2xl border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $error }}
        </div>
    @endif
    <div class="rounded-3xl border border-stone-200 bg-white shadow-sm">
        <form method="POST" action="{{ route('invoices.store') }}" class="space-y-6 p-6" id="invoice-form">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm text-stone-500">Complete the invoice details, add items, and ZeroPHP handles the rest.</p>
                </div>
                <div class="flex flex-col gap-4 md:flex-row">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Invoice #
                        <input type="text" name="invoice_no" value="{{ $invoiceNumber }}" readonly class="mt-1 rounded-full border border-stone-200 bg-stone-50 px-4 py-2 text-stone-700" />
                    </label>
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Status
                        <select name="status" class="mt-1 rounded-full border border-stone-200 bg-white px-4 py-2 text-stone-700">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @if ($status === $selectedStatus) selected @endif>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Bill To
                    <select name="client_id" class="mt-1 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-stone-700" @if (!$hasClients) disabled @endif>
                        <option value="">Select client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client['id'] }}" @if ((string) $client['id'] === (string) $selectedClient) selected @endif>{{ $client['name'] ?? 'Unnamed client' }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Invoice Date
                    <input type="date" name="date" value="{{ $oldValues['date'] ?? $today }}" class="mt-1 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-stone-700" />
                </label>
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Due Date
                    <input type="date" name="due_date" value="{{ $oldValues['due_date'] ?? '' }}" class="mt-1 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-stone-700" />
                </label>
                <div class="flex flex-col justify-end text-sm text-stone-500">
                    <span class="font-semibold text-stone-700">Need a new client?</span>
                    <span>Add them via database seeding or an admin tool — dropdown updates instantly.</span>
                </div>
            </div>
            <div class="rounded-2xl border border-dashed border-stone-200">
                <div class="flex items-center justify-between border-b border-stone-100 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-stone-800">Line Items</p>
                        <p class="text-xs text-stone-500">Quantity and price update the totals automatically.</p>
                    </div>
                    <button type="button" id="add-row" class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-stone-700 shadow hover:bg-stone-50">Add row</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3">Qty</th>
                                <th class="px-4 py-3">Unit Price</th>
                                <th class="px-4 py-3">Subtotal</th>
                                <th class="px-4 py-3 print:hidden">Action</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-items" data-next-index="{{ count($lineItems) }}" class="divide-y divide-stone-100 text-sm text-stone-700">
                            @foreach ($lineItems as $index => $item)
                                <tr class="item-row">
                                    <td class="px-4 py-3">
                                        <input type="text" name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" placeholder="Detail (e.g., Design work)" class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="items[{{ $index }}][qty]" value="{{ $item['qty'] ?? 1 }}" min="1" step="1" class="qty-input w-20 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? '0.00' }}" min="0" step="0.01" class="price-input w-28 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
                                    </td>
                                    <td class="px-4 py-3 font-semibold">
                                        $<span class="subtotal">{{ number_format((float) ($item['subtotal'] ?? 0), 2) }}</span>
                                        <input type="hidden" class="subtotal-input" value="{{ number_format((float) ($item['subtotal'] ?? 0), 2) }}">
                                    </td>
                                    <td class="px-4 py-3 print:hidden">
                                        <button type="button" class="remove-row text-sm text-red-500 hover:text-red-600">Remove</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2">
                <div class="text-sm text-stone-500">Invoice total</div>
                <div class="text-3xl font-semibold text-stone-900">$<span id="invoice-total">0.00</span></div>
                <input type="hidden" name="calculated_total" id="invoice-total-input" value="0.00" />
            </div>
            <div class="flex justify-end gap-3 print:hidden">
                <a href="{{ route('invoices.index') }}" class="rounded-full border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">Cancel</a>
                <button type="submit" class="rounded-full bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700" @if (!$hasClients) disabled @endif>
                    Save invoice
                </button>
            </div>
        </form>
    </div>
    @unless ($hasClients)
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
            Please seed or import a client before creating invoices.
        </div>
    @endunless
</div>
<template id="line-item-template">
    <tr class="item-row">
        <td class="px-4 py-3">
            <input type="text" name="items[__INDEX__][description]" placeholder="Detail (e.g., Design work)" class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[__INDEX__][qty]" value="1" min="1" step="1" class="qty-input w-20 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[__INDEX__][unit_price]" value="0.00" min="0" step="0.01" class="price-input w-28 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
        </td>
        <td class="px-4 py-3 font-semibold">
            $<span class="subtotal">0.00</span>
            <input type="hidden" class="subtotal-input" value="0.00">
        </td>
        <td class="px-4 py-3 print:hidden">
            <button type="button" class="remove-row text-sm text-red-500 hover:text-red-600">Remove</button>
        </td>
    </tr>
</template>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const itemsTable = document.getElementById('invoice-items');
        const template = document.getElementById('line-item-template');
        const addRowButton = document.getElementById('add-row');
        const totalLabel = document.getElementById('invoice-total');
        const totalInput = document.getElementById('invoice-total-input');

        const updateTotals = () => {
            let total = 0;
            itemsTable.querySelectorAll('.item-row').forEach((row) => {
                const subtotal = parseFloat(row.querySelector('.subtotal-input').value) || 0;
                total += subtotal;
            });
            totalLabel.textContent = total.toFixed(2);
            totalInput.value = total.toFixed(2);
        };

        const refreshRow = (row) => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = qty * price;
            row.querySelector('.subtotal').textContent = subtotal.toFixed(2);
            row.querySelector('.subtotal-input').value = subtotal.toFixed(2);
            updateTotals();
        };

        const bindRowEvents = (row) => {
            row.querySelectorAll('.qty-input, .price-input').forEach((input) =>
                input.addEventListener('input', () => refreshRow(row))
            );

            const removeBtn = row.querySelector('.remove-row');
            removeBtn?.addEventListener('click', () => {
                if (itemsTable.querySelectorAll('.item-row').length === 1) {
                    row.querySelector('.qty-input').value = '1';
                    row.querySelector('.price-input').value = '0.00';
                    refreshRow(row);
                    return;
                }

                row.remove();
                updateTotals();
            });
        };

        const addRow = () => {
            const index = Number(itemsTable.getAttribute('data-next-index')) || itemsTable.querySelectorAll('.item-row').length;
            const clone = document.createElement('tbody');
            clone.innerHTML = template.innerHTML.replace(/__INDEX__/g, index);
            const row = clone.firstElementChild;
            itemsTable.appendChild(row);
            itemsTable.setAttribute('data-next-index', index + 1);
            bindRowEvents(row);
            refreshRow(row);
        };

        itemsTable.querySelectorAll('.item-row').forEach((row) => {
            bindRowEvents(row);
            refreshRow(row);
        });

        addRowButton.addEventListener('click', addRow);
        updateTotals();
    });
</script>
@endsection
