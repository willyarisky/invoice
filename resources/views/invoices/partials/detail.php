<div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-lg">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-4">
            @if ($companyLogo !== '')
                <img src="{{ $companyLogo }}" alt="{{ $brandName }} logo" class="h-20 w-20 object-cover">
            @else
                <h1 class="text-2xl font-bold text-stone-900">{{ $brandName }}</h1>
            @endif
        </div>
        <div class="text-sm text-stone-900 text-end">
            <p class="font-semibold mb-2">{{ $brandName }}</p>
            @if ($companyAddress !== '')
                <p class="whitespace-pre-line mb-2">{{ $companyAddress }}</p>
            @endif
            @if ($companyEmail !== '')
                <p>{{ $companyEmail }}</p>
            @endif
        </div>
    </div>

    <div class="mt-6 border-t border-stone-200 pt-6">
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <p class="text-xs uppercase text-stone-400">Bill To</p>
                <p class="mt-1 text-sm font-semibold text-stone-900">{{ $invoice['customer_name'] ?? 'Customer' }}</p>
                @if ($customerAddress !== '')
                    <p class="mb-4 text-xs text-stone-500 whitespace-pre-line">{{ $customerAddress }}</p>
                @endif
            </div>
            <div class="space-y-1 text-sm text-stone-600 max-w-[60%] md:justify-self-end">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase text-stone-400 pe-5">Invoice Number</span>
                    <span class="font-semibold text-stone-900">{{ $invoiceNo }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase text-stone-400 pe-5">Invoice Date</span>
                    <span class="text-stone-700">{{ $issued }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase text-stone-400 pe-5">Due Date</span>
                    <span class="text-stone-700">{{ $due }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-2" id="invoice-items">
        <table class="min-w-full text-sm text-stone-600">
            <thead class="uppercase font-bold text-stone-900 border-b border-b-2 rounded-top-lg rounded-t-xl">
                <tr>
                    <th class="py-3 text-left font-semibold">Items</th>
                    <th class="py-3 text-center font-semibold">Quantity</th>
                    <th class="py-3 text-start font-semibold">Price</th>
                    <th class="py-3 text-right font-semibold">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @foreach ($items as $item)
                    <tr>
                        <td class="py-3">
                            <p class="font-semibold text-stone-900">{{ $item['description'] ?? '' }}</p>
                        </td>
                        <td class="py-3 text-center">{{ $item['qty'] ?? 0 }}</td>
                        <td class="py-3 text-start">{{ $item['unit_label'] ?? '' }}</td>
                        <td class="py-3 text-right font-semibold">{{ $item['subtotal_label'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 grid gap-6 pt-6 {{ $hasNotes ? 'md:grid-cols-[1fr_minmax(0,42%)]' : 'md:grid-cols-1' }}">
        @if ($hasNotes)
            <div class="md:mt-38 mt-32">
                <p class="text-sm text-stone-900 font-semibold">Notes</p>
                <p class="mt-2 text-sm text-stone-500 whitespace-pre-line">{{ $invoiceNotes }}</p>
            </div>
        @endif
        <div class="space-y-2 text-sm text-stone-600 w-full md:justify-self-end">
            <div class="flex items-center justify-between">
                <span>Subtotal</span>
                <span class="font-semibold text-stone-900">{{ $subtotalLabel }}</span>
            </div>
            @if ($hasTax)
                <div class="flex items-center justify-between">
                    <span>{{ $taxLabel }}</span>
                    <span class="font-semibold text-stone-900">{{ $taxAmountLabel }}</span>
                </div>
            @endif
            <div class="flex items-center justify-between">
                <span>Amount due</span>
                <span class="font-semibold text-stone-900">{{ $amountDueLabel }}</span>
            </div>
            <div class="flex items-center justify-between border-t border-stone-200 pt-2">
                <span class="text-sm font-semibold text-stone-900">Total</span>
                <span class="text-lg font-semibold text-stone-900">{{ $totalLabel }}</span>
            </div>
        </div>
    </div>
</div>
