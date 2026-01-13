@layout('layouts.app', ['title' => 'Edit Transaction'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Edit transaction</h1>
            <p class="mt-1 text-sm text-stone-500">Transaction #{{ $transaction['id'] ?? '' }}</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('transactions.show', ['transaction' => $transaction['id']]) }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                Back to transaction
            </a>
        </div>
    </div>

    @if ($linkedInvoice)
        <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700" x-data="{ open: true }" x-show="open">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    This transaction is linked to invoice {{ $transaction['invoice_no'] ?? '' }}.
                </div>
                <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div class="rounded-xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
        @if (!empty($errors ?? []))
            <div class="mt-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" x-data="{ open: true }" x-show="open">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <p class="font-semibold">Please review the transaction fields.</p>
                    </div>
                    <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('transactions.update', ['transaction' => $transaction['id']]) }}" class="mt-6 grid gap-4 lg:grid-cols-2">
            <label class="flex flex-col text-sm font-medium text-stone-700">
                Type
                <select name="type" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @foreach ($typeOptions as $type)
                        <option value="{{ $type['value'] ?? '' }}" @if (($currentType ?? '') === ($type['value'] ?? '')) selected @endif>
                            {{ $type['label'] ?? '' }}
                        </option>
                    @endforeach
                </select>
                @if (isset($errors['type']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['type'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Amount
                <input type="number" name="amount" value="{{ $currentAmount }}" min="0.01" step="0.01" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['amount']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['amount'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Currency
                <select name="currency" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @foreach ($currencyOptions as $code => $label)
                        <option value="{{ $code }}" @if ($currentCurrency === $code) selected @endif>
                            {{ $code }} - {{ $label }}
                        </option>
                    @endforeach
                </select>
                @if (isset($errors['currency']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['currency'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Date
                <input type="date" name="date" value="{{ $currentDate }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['date']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['date'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Vendor (optional)
                <select name="vendor_id" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    <option value="">No vendor</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor['id'] }}" @if ((string) $currentVendor === (string) $vendor['id']) selected @endif>
                            {{ $vendor['name'] ?? 'Vendor' }}
                        </option>
                    @endforeach
                </select>
                @if (isset($errors['vendor_id']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['vendor_id'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Category (optional)
                <select name="category_id" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    <option value="">No category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['id'] }}" @if ((string) $currentCategory === (string) $category['id']) selected @endif>
                            {{ $category['name'] ?? 'Category' }}
                        </option>
                    @endforeach
                </select>
                @if (isset($errors['category_id']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['category_id'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                Description
                <textarea name="description" rows="3" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">{{ $currentDescription }}</textarea>
                @if (isset($errors['description']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['description'] ?? '' }}</span>
                @endif
            </label>

            <div class="flex items-center justify-end gap-3 lg:col-span-2">
                <a href="{{ route('transactions.show', ['transaction' => $transaction['id']]) }}" class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
