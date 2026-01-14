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
            <tbody class="divide-y divide-stone-200 border-b border-stone-200">
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

        <div class="mt-6 flex justify-end">
            <div class="w-full overflow-hidden rounded-2xl border border-stone-200 bg-stone-100 text-sm text-stone-600 md:max-w-[45%]">
                <div class="flex items-center justify-between px-4 py-2">
                    <span class="font-semibold text-stone-900">Subtotal</span>
                    <span class="font-semibold text-stone-900">{{ $subtotalLabel }}</span>
                </div>
                @if ($hasTax)
                    <div class="flex items-center justify-between border-t border-stone-200 px-4 py-2">
                        <span class="font-semibold text-stone-900">{{ $taxLabel }}</span>
                        <span class="font-semibold text-stone-900">{{ $taxAmountLabel }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between border-t border-stone-200 px-4 py-2">
                    <span class="font-semibold text-stone-900">Amount due</span>
                    <span class="font-semibold text-stone-900">{{ $amountDueLabel }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-stone-200 px-4 py-2">
                    <span class="text-base font-semibold text-stone-900">Total</span>
                    <span class="text-base font-semibold text-stone-900">{{ $totalLabel }}</span>
                </div>
            </div>
        </div>
    </div>

    @if ($hasNotes)
        <div class="mt-8">
            <p class="text-sm text-stone-900 font-semibold">Notes</p>
            <p class="mt-2 text-sm text-stone-500 whitespace-pre-line">{{ $invoiceNotes }}</p>
        </div>
    @endif
</div>
