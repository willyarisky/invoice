@layout('layouts.app', ['title' => 'Settings - Currency'])

@section('content')
@php
    $currencyOptions = \App\Models\Setting::currencyOptions();
    $autoOpenAddModal = (isset($errors['code']) || isset($errors['name']) || isset($errors['symbol'])) && empty($editId);
    $autoOpenEditModal = !empty($editId);
    $addOld = empty($editId) ? ($old ?? []) : [];
    $editOld = !empty($editId) ? ($old ?? []) : [];
@endphp
<div class="grid gap-8 lg:grid-cols-[240px_1fr]">
    @include('settings/partials/sidebar', ['active' => 'currency'])

    <div class="space-y-6">
        <div class="rounded-lg border border-stone-200 bg-white px-6 py-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-col gap-2">
                    <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                    <p class="text-lg font-semibold text-stone-900">Available currencies</p>
                    <p class="text-sm text-stone-500">Select the default or remove currencies you no longer use.</p>
                </div>
                <button type="button" class="bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-800" id="open-currency-modal-secondary">
                    Add currency
                </button>
            </div>

            @if (!empty($status ?? ''))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ $status ?? '' }}
                </div>
            @endif

            @if (!empty($errors ?? []))
                <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="font-semibold">Please review the highlighted fields.</p>
                </div>
            @endif

            <div class="mt-4 overflow-hidden rounded-lg border border-stone-200">
                <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                        <tr>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Symbol</th>
                            <th class="px-4 py-3">Default</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($currencies as $currency)
                            @php
                                $code = strtoupper((string) ($currency['code'] ?? ''));
                                $isDefault = !empty($currency['is_default']) || (($defaultCurrency ?? '') === $code);
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-semibold text-stone-900">{{ $code }}</td>
                                <td class="px-4 py-3">{{ $currency['name'] ?? '' }}</td>
                                <td class="px-4 py-3">{{ $currency['symbol'] ?? '' }}</td>
                                <td class="px-4 py-3">
                                    @if ($isDefault)
                                        <span class="inline-flex items-center rounded-lg bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Default</span>
                                    @else
                                        <form method="POST" action="{{ route('settings.currency.update') }}">
                                            <input type="hidden" name="default_currency" value="{{ $code }}">
                                            <button type="submit" class="text-xs font-semibold text-stone-500 hover:text-stone-800">Set default</button>
                                        </form>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-4">
                                        <button
                                            type="button"
                                            class="text-sm font-semibold text-stone-500 hover:text-stone-800"
                                            data-edit-currency
                                            data-action="{{ route('settings.currency.entry.update', ['currency' => $currency['id']]) }}"
                                            data-code="{{ $code }}"
                                            data-name="{{ $currency['name'] ?? '' }}"
                                            data-symbol="{{ $currency['symbol'] ?? '' }}"
                                            data-default="{{ $isDefault ? '1' : '0' }}"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('settings.currency.delete', ['currency' => $currency['id']]) }}">
                                            <button type="submit" class="text-sm font-semibold text-rose-500 hover:text-rose-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-stone-500">No currencies added yet.</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="currency-modal" class="fixed inset-0 z-50 hidden" data-auto-open="{{ $autoOpenAddModal ? 'true' : 'false' }}">
    <div class="absolute inset-0 bg-stone-900/60"></div>
    <div class="relative mx-auto mt-10 w-full max-w-2xl px-6 pb-10">
        <div class="rounded-lg bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Add currency</p>
                    <p class="mt-2 text-sm text-stone-500">Add more currencies for invoice creation.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" data-close-currency-modal>
                    Close
                </button>
            </div>

            <form method="POST" action="{{ route('settings.currency.store') }}" class="mt-6 space-y-5">
                <div class="grid gap-4 lg:grid-cols-[120px_1fr_120px]">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Code
                        <input type="text" name="code" value="{{ $addOld['code'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="USD">
                        @if (isset($errors['code']) && empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['code'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Name
                        <input type="text" name="name" value="{{ $addOld['name'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="US Dollar">
                        @if (isset($errors['name']) && empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Symbol
                        <input type="text" name="symbol" value="{{ $addOld['symbol'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="$">
                        @if (isset($errors['symbol']) && empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['symbol'] ?? '' }}</span>
                        @endif
                    </label>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-4">
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="set_default" value="1" class="h-4 w-4 border-stone-300">
                        Set as default
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" class="border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" data-close-currency-modal>
                            Cancel
                        </button>
                        <button type="submit" class="bg-stone-900 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-800">
                            Add currency
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div
    id="currency-edit-modal"
    class="fixed inset-0 z-50 hidden"
    data-auto-open="{{ $autoOpenEditModal ? 'true' : 'false' }}"
    data-auto-action="{{ $autoOpenEditModal ? route('settings.currency.entry.update', ['currency' => $editId]) : '' }}"
    data-auto-code="{{ $editOld['code'] ?? '' }}"
    data-auto-name="{{ $editOld['name'] ?? '' }}"
    data-auto-symbol="{{ $editOld['symbol'] ?? '' }}"
    data-auto-default="{{ !empty($editOld['set_default']) ? '1' : '0' }}"
>
    <div class="absolute inset-0 bg-stone-900/60"></div>
    <div class="relative mx-auto mt-10 w-full max-w-2xl px-6 pb-10">
        <div class="rounded-lg bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Edit currency</p>
                    <p class="mt-2 text-sm text-stone-500">Update the currency details.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" data-close-currency-edit-modal>
                    Close
                </button>
            </div>

            <form method="POST" action="" class="mt-6 space-y-5" data-currency-edit-form>
                <div class="grid gap-4 lg:grid-cols-[120px_1fr_120px]">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Code
                        <input type="text" name="code" value="{{ $editOld['code'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="USD" data-currency-edit-code>
                        @if (isset($errors['code']) && !empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['code'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Name
                        <input type="text" name="name" value="{{ $editOld['name'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="US Dollar" data-currency-edit-name>
                        @if (isset($errors['name']) && !empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Symbol
                        <input type="text" name="symbol" value="{{ $editOld['symbol'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="$" data-currency-edit-symbol>
                        @if (isset($errors['symbol']) && !empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['symbol'] ?? '' }}</span>
                        @endif
                    </label>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-4">
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="set_default" value="1" class="h-4 w-4 border-stone-300" data-currency-edit-default>
                        Set as default
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" class="border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" data-close-currency-edit-modal>
                            Cancel
                        </button>
                        <button type="submit" class="bg-stone-900 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-800">
                            Save changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('currency-modal');
        const editModal = document.getElementById('currency-edit-modal');
        const openBtnSecondary = document.getElementById('open-currency-modal-secondary');
        const closeBtns = document.querySelectorAll('[data-close-currency-modal]');
        const closeEditBtns = document.querySelectorAll('[data-close-currency-edit-modal]');
        const editButtons = document.querySelectorAll('[data-edit-currency]');
        const editForm = editModal?.querySelector('[data-currency-edit-form]');
        const editCode = editModal?.querySelector('[data-currency-edit-code]');
        const editName = editModal?.querySelector('[data-currency-edit-name]');
        const editSymbol = editModal?.querySelector('[data-currency-edit-symbol]');
        const editDefault = editModal?.querySelector('[data-currency-edit-default]');

        const openModal = () => modal?.classList.remove('hidden');
        const closeModal = () => modal?.classList.add('hidden');
        const openEditModal = (payload) => {
            if (!editModal || !editForm || !editCode || !editName || !editSymbol || !editDefault) {
                return;
            }

            editForm.action = payload.action || '';
            editCode.value = payload.code || '';
            editName.value = payload.name || '';
            editSymbol.value = payload.symbol || '';
            editDefault.checked = payload.isDefault === '1';
            editModal.classList.remove('hidden');
        };
        const closeEditModal = () => editModal?.classList.add('hidden');

        openBtnSecondary?.addEventListener('click', openModal);
        closeBtns.forEach((btn) => btn.addEventListener('click', closeModal));
        closeEditBtns.forEach((btn) => btn.addEventListener('click', closeEditModal));
        editButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                openEditModal({
                    action: btn.dataset.action,
                    code: btn.dataset.code,
                    name: btn.dataset.name,
                    symbol: btn.dataset.symbol,
                    isDefault: btn.dataset.default,
                });
            });
        });

        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        editModal?.addEventListener('click', (event) => {
            if (event.target === editModal) {
                closeEditModal();
            }
        });

        if (modal?.dataset.autoOpen === 'true') {
            openModal();
        }

        if (editModal?.dataset.autoOpen === 'true') {
            openEditModal({
                action: editModal.dataset.autoAction,
                code: editModal.dataset.autoCode,
                name: editModal.dataset.autoName,
                symbol: editModal.dataset.autoSymbol,
                isDefault: editModal.dataset.autoDefault,
            });
        }
    });
</script>
@endsection
