@layout('layouts.app', ['title' => $pageTitle ?? 'Create Invoice'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">{{ $pageTitle ?? 'Create Invoice' }}</h1>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('invoices.index') }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                Back to invoices
            </a>
        </div>
    </div>
    @if ($error ?? false)
        <div class="rounded-xl border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $error }}
        </div>
    @endif
    <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
        <form
            method="POST"
            action="{{ $formAction ?? route('invoices.store') }}"
            class="space-y-6 p-6"
            x-data='{
                clientId: {!! $selectedClientJson ?? '""' !!},
                clientAddresses: {!! $clientAddressJson ?? '{}' !!},
                currency: {!! $selectedCurrencyJson ?? '""' !!},
                taxId: {!! $selectedTaxJson ?? '""' !!},
                taxes: {!! $taxesJson ?? '{}' !!},
                items: {!! $lineItemsJson ?? '[]' !!},
                init() {
                    if (!Array.isArray(this.items) || this.items.length === 0) {
                        this.items = [{ description: "", qty: 1, unit_price: "0.00" }];
                    }
                },
                get clientAddress() {
                    return this.clientAddresses[this.clientId] || "";
                },
                currencyPrefixFor(value) {
                    const code = String(value || "").trim().toUpperCase();
                    if (code === "" || code === "USD" || code === "$") {
                        return "$ ";
                    }
                    if (code === "EUR" || code === "€") {
                        return "€ ";
                    }
                    return code + " ";
                },
                sanitizeQty(value) {
                    const qty = parseInt(value, 10);
                    return Number.isFinite(qty) && qty > 0 ? qty : 1;
                },
                sanitizePrice(value) {
                    const price = parseFloat(value);
                    return Number.isFinite(price) && price > 0 ? price : 0;
                },
                itemSubtotal(item) {
                    return this.sanitizeQty(item.qty) * this.sanitizePrice(item.unit_price);
                },
                formatMoney(value) {
                    const amount = Number.isFinite(value) ? value : 0;
                    return amount.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                get currencyPrefix() {
                    return this.currencyPrefixFor(this.currency);
                },
                get subtotal() {
                    return this.items.reduce((sum, item) => sum + this.itemSubtotal(item), 0);
                },
                get taxRate() {
                    const entry = this.taxes[this.taxId];
                    const rate = entry ? parseFloat(entry.rate) : 0;
                    return Number.isFinite(rate) ? rate : 0;
                },
                get taxLabel() {
                    const entry = this.taxes[this.taxId];
                    return entry && entry.label ? entry.label : "Tax";
                },
                get taxAmount() {
                    return this.subtotal * (this.taxRate / 100);
                },
                get totalWithTax() {
                    return this.subtotal + this.taxAmount;
                },
                get hasTax() {
                    return this.taxId !== "";
                },
                addRow() {
                    this.items.push({ description: "", qty: 1, unit_price: "0.00" });
                },
                removeRow(index) {
                    if (this.items.length === 1) {
                        this.items[0] = { description: "", qty: 1, unit_price: "0.00" };
                        return;
                    }
                    this.items.splice(index, 1);
                },
            }'
            x-init="init()"
        >
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-col gap-4 md:flex-row">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Invoice #
                        <input type="text" name="invoice_no" value="{{ $invoiceNumber }}" readonly class="mt-1 rounded-xl border border-stone-200 bg-stone-50 px-4 py-2 text-stone-700" />
                    </label>
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Currency
                        <select name="currency" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700" x-model="currency">
                            @foreach ($currencyOptions as $code => $label)
                                <option value="{{ $code }}">{{ $code }} - {{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col text-sm font-medium text-stone-700 md:col-span-2">
                    Bill To
                    <select name="client_id" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700 w-1/4 mb-2" x-model="clientId" @if (!$hasClients) disabled @endif>
                        <option value="">Select client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client['id'] }}" @if ((string) $client['id'] === (string) $selectedClient) selected @endif>{{ $client['name'] ?? 'Unnamed client' }}</option>
                        @endforeach
                    </select>
                    <div class="mt-2 text-sm text-stone-500 whitespace-pre-line" x-cloak x-show="clientId !== ''" x-text="clientAddress || 'No address on file'"></div>
                </label>
                <div class="flex gap-4">
                    <label class="flex w-1/3 flex-col text-sm font-medium text-stone-700">
                        Invoice Date
                        <input type="date" name="date" value="{{ $formValues['date'] ?? '' }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700" />
                    </label>
                    <label class="flex w-1/3 flex-col text-sm font-medium text-stone-700">
                        Due Date
                        <input type="date" name="due_date" value="{{ $formValues['due_date'] ?? '' }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700" />
                    </label>
                </div>
            </div>
            <div class="rounded-xl border border-dashed border-stone-200">
                <div class="flex items-center justify-between border-b border-stone-100 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-stone-800">Line Items</p>
                        <p class="text-xs text-stone-500">Quantity and price update the totals automatically.</p>
                    </div>
                    <button type="button" class="rounded-xl bg-white px-3 py-1 text-sm font-semibold text-stone-700 shadow hover:bg-stone-50" x-on:click="addRow()">Add row</button>
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
                        <tbody class="divide-y divide-stone-100 text-sm text-stone-700">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="item-row">
                                    <td class="px-4 py-3">
                                        <input type="text" x-model="item.description" x-bind:name="`items[${index}][description]`" placeholder="Detail (e.g., Design work)" class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" x-model.number="item.qty" x-bind:name="`items[${index}][qty]`" min="1" step="1" class="w-20 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" x-model="item.unit_price" x-bind:name="`items[${index}][unit_price]`" min="0" step="0.01" class="w-28 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
                                    </td>
                                    <td class="px-4 py-3 font-semibold">
                                        <span x-text="currencyPrefix"></span><span x-text="formatMoney(itemSubtotal(item))"></span>
                                    </td>
                                    <td class="px-4 py-3 print:hidden">
                                        <button type="button" class="text-sm text-red-500 hover:text-red-600" x-on:click="removeRow(index)">Remove</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Tax
                    <select name="tax_id" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700" x-model="taxId">
                        <option value="">No tax</option>
                        @foreach ($taxOptions as $tax)
                            <option value="{{ $tax['id'] }}" @if ((string) ($tax['id'] ?? '') === ($selectedTaxId ?? '')) selected @endif>
                                {{ $tax['label'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="w-1/2">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Notes
                    <textarea name="notes" rows="5" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">{{ $formValues['notes'] ?? '' }}</textarea>
                </label>
            </div>
            <div class="flex flex-col items-end gap-2">
                <div class="flex w-full max-w-sm items-center justify-between text-sm text-stone-500">
                    <span>Subtotal</span>
                    <span class="font-semibold text-stone-800"><span x-text="currencyPrefix"></span><span x-text="formatMoney(subtotal)"></span></span>
                </div>
                <div class="flex w-full max-w-sm items-center justify-between text-sm text-stone-500" x-cloak x-show="hasTax">
                    <span x-text="taxLabel"></span>
                    <span class="font-semibold text-stone-800"><span x-text="currencyPrefix"></span><span x-text="formatMoney(taxAmount)"></span></span>
                </div>
                <div class="text-sm text-stone-500">Invoice total</div>
                <div class="text-3xl font-semibold text-stone-900"><span x-text="currencyPrefix"></span><span x-text="formatMoney(totalWithTax)"></span></div>
                <input type="hidden" name="calculated_total" x-bind:value="totalWithTax.toFixed(2)" />
            </div>
            <div class="flex justify-end gap-3 print:hidden">
                <a href="{{ route('invoices.index') }}" class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">Cancel</a>
                <button type="submit" class="rounded-xl bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700" @if (!$hasClients) disabled @endif>
                    {{ $submitLabel ?? 'Save invoice' }}
                </button>
            </div>
        </form>
    </div>
    @if (! $hasClients)
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
            Add a client first, then return here to finish your invoice.
        </div>
    @endif
</div>
@endsection
