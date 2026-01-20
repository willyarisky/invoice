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
                customerId: {!! $selectedCustomerJson ?? '""' !!},
                customerAddresses: {!! $customerAddressJson ?? '{}' !!},
                currency: {!! $selectedCurrencyJson ?? '""' !!},
                taxId: {!! $selectedTaxJson ?? '""' !!},
                taxes: {!! $taxesJson ?? '{}' !!},
                items: {!! $lineItemsJson ?? '[]' !!},
                init() {
                    if (!Array.isArray(this.items) || this.items.length === 0) {
                        this.items = [{ description: "", qty: 1, unit_price: "0.00" }];
                    }
                },
                get customerAddress() {
                    return this.customerAddresses[this.customerId] || "";
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
            <div class="grid gap-6 lg:grid-cols-[1.1fr_1.4fr]">
                <div class="space-y-1">
                    <div class="flex items-center gap-1 text-sm font-semibold text-stone-700">
                        <span>Customer</span>
                        <span class="text-rose-500">*</span>
                    </div>
                    <div class="rounded-xl border border-stone-200 bg-white p-4">
                        <select name="customer_id" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700" x-model="customerId" @if (!$hasCustomers) disabled @endif>
                            <option value="">Select customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer['id'] }}" @if ((string) $customer['id'] === (string) $selectedCustomer) selected @endif>{{ $customer['name'] ?? 'Unnamed customer' }}</option>
                            @endforeach
                        </select>
                        <div class="mt-4 flex flex-col items-center justify-center rounded-xl border border-dashed border-stone-200 px-4 py-8 text-center text-sm text-stone-500 max-h-[70px]" x-cloak x-show="customerId === ''">
                            <span class="mt-2 text-base font-semibold text-stone-600">Add a customer</span>
                            <span class="mt-1 text-xs text-stone-400">Choose from your list to attach details.</span>
                        </div>
                        <div class="mt-4 text-sm text-stone-500 whitespace-pre-line" x-cloak x-show="customerId !== ''" x-text="customerAddress || 'No address on file'"></div>
                    </div>
                </div>
                <div class="flex flex-col gap-4">
                    <label class="flex items-center font-medium text-stone-700 pb-2 mb-2 border-b border-stone-200">
                        <span>Invoice Number</span>
                        <input type="text" name="invoice_no" value="{{ $invoiceNumber ?? '' }}" class="border-none px-2" />
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex flex-col text-sm font-medium text-stone-700">
                            <span class="flex items-center gap-1">Invoice Date <span class="text-rose-500">*</span></span>
                            <input type="date" name="date" value="{{ $formValues['date'] ?? $today ?? '' }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2" />
                        </label>

                        <label class="flex flex-col text-sm font-medium text-stone-700">
                            <span class="flex items-center gap-1">Due Date <span class="text-rose-500">*</span></span>
                            <input type="date" name="due_date" value="{{ ($formValues['due_date'] ?? '') !== '' ? $formValues['due_date'] : ($formValues['date'] ?? $today ?? '') }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2" />
                        </label>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-stone-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100">
                        <thead class="text-left text-xs font-semibold uppercase tracking-wider text-stone-500 rounded-t-xl">
                            <tr>
                                <th class="px-4 py-3">Items</th>
                                <th class="px-4 py-3">Quantity</th>
                                <th class="px-4 py-3">Price</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3 text-right print:hidden">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-sm text-stone-700">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-center">
                                    <button type="button" class="mx-auto inline-flex items-center gap-2 text-sm font-semibold text-stone-600 hover:text-stone-800" x-on:click="addRow()">
                                        <span class="text-lg leading-none">+</span>
                                        Add an item
                                    </button>
                                </td>
                            </tr>
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="item-row">
                                    <td class="px-4 py-3">
                                        <input type="text" x-model="item.description" x-bind:name="`items[${index}][description]`" placeholder="Item description" class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" x-model.number="item.qty" x-bind:name="`items[${index}][qty]`" min="1" step="1" class="w-24 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" x-model="item.unit_price" x-bind:name="`items[${index}][unit_price]`" min="0" step="0.01" class="w-28 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm" />
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold">
                                        <span x-text="currencyPrefix"></span><span x-text="formatMoney(itemSubtotal(item))"></span>
                                    </td>
                                    <td class="px-4 py-3 text-right print:hidden">
                                        <button type="button" class="text-sm text-stone-500 hover:text-rose-600" x-on:click="removeRow(index)">Remove</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Notes
                    <textarea name="notes" rows="8" class="mt-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700 max-w-[70%]">{{ $formValues['notes'] ?? '' }}</textarea>
                </label>
                <div class="space-y-4">
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
                    <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-4">
                        <div class="flex items-center justify-between text-sm text-stone-500">
                            <span>Subtotal</span>
                            <span class="font-semibold text-stone-800"><span x-text="currencyPrefix"></span><span x-text="formatMoney(subtotal)"></span></span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-sm text-stone-500">
                            <span x-text="taxLabel"></span>
                            <span class="font-semibold text-stone-800"><span x-text="currencyPrefix"></span><span x-text="formatMoney(taxAmount)"></span></span>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-base font-semibold text-stone-700">Total</span>
                            <div class="flex items-center gap-3">
                                <select name="currency" class="rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700" x-model="currency">
                                    @foreach ($currencyOptions as $code => $label)
                                        <option value="{{ $code }}">{{ $code }}</option>
                                    @endforeach
                                </select>
                                <span class="text-2xl font-semibold text-stone-900"><span x-text="currencyPrefix"></span><span x-text="formatMoney(totalWithTax)"></span></span>
                            </div>
                        </div>
                        <input type="hidden" name="calculated_total" x-bind:value="totalWithTax.toFixed(2)" />
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 print:hidden">
                <a href="{{ route('invoices.index') }}" class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">Cancel</a>
                <button type="submit" class="rounded-xl bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700" @if (!$hasCustomers) disabled @endif>
                    {{ $submitLabel ?? 'Save invoice' }}
                </button>
            </div>
        </form>
    </div>
    @if (! $hasCustomers)
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
            Add a customer first, then return here to finish your invoice.
        </div>
    @endif
</div>
@endsection
