@layout('layouts.app', ['title' => 'Settings - Taxes'])

@section('content')
<div
    x-data='{
        addOpen: {{ $autoOpenAddModal ? 'true' : 'false' }},
        editOpen: {{ $autoOpenEditModal ? 'true' : 'false' }},
        editAction: {!! $editActionJson ?? '""' !!},
        editName: {!! $editNameJson ?? '""' !!},
        editRate: {!! $editRateJson ?? '""' !!},
        openAdd() {
            this.addOpen = true;
        },
        closeAdd() {
            this.addOpen = false;
        },
        openEdit(action, name, rate) {
            this.editAction = action || "";
            this.editName = name || "";
            this.editRate = rate || "";
            this.editOpen = true;
        },
        closeEdit() {
            this.editOpen = false;
        },
    }'
>
<div class="grid gap-8 lg:grid-cols-[240px_1fr]">
    @include('settings/partials/sidebar', [
        'settingsActive' => $settingsActive,
        'settingsLinkBase' => $settingsLinkBase,
    ])

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3 sm:gap-4 lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">Available taxes</h1>
            </div>
            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-stone-900 text-white hover:bg-stone-800 sm:h-auto sm:w-auto sm:px-4 sm:py-2 sm:text-sm sm:font-semibold" x-on:click="openAdd()" aria-label="Add tax">
                <svg aria-hidden="true" class="h-4 w-4 sm:hidden" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <span class="hidden sm:inline">Add tax</span>
            </button>
        </div>
        @if($status || $errors)
            <div>
            @include('components/alerts', [
                'status' => $status ?? null,
                'errors' => $errors ?? [],
            ])
            </div>
        @endif
        <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
            <div class="border-t border-stone-100">
                <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                <thead class="text-left text-xs font-semibold uppercase tracking-widest text-stone-500 rounded-t-xl">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Rate</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($taxes as $tax)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-stone-900">{{ $tax['name'] ?? '' }}</td>
                            <td class="px-4 py-3">{{ $tax['rate_label'] ?? '' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-4">
                                    <button
                                        type="button"
                                        class="text-sm font-semibold text-stone-500 hover:text-stone-800"
                                        data-action="{{ $tax['edit_action'] ?? '' }}"
                                        data-name="{{ $tax['name'] ?? '' }}"
                                        data-rate="{{ $tax['rate_value'] ?? '' }}"
                                        x-on:click="openEdit($el.dataset.action, $el.dataset.name, $el.dataset.rate)"
                                    >
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('settings.taxes.delete', ['tax' => $tax['id']]) }}">
                                        <button type="submit" class="text-sm font-semibold text-rose-500 hover:text-rose-600">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-stone-500">No taxes created yet.</td>
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="tax-modal" class="fixed inset-0 z-50" x-cloak x-show="addOpen" x-on:click.self="closeAdd()">
    <div class="absolute inset-0 bg-stone-900/60"></div>
    <div class="relative mx-auto mt-10 w-full max-w-xl px-6 pb-10">
        <div class="rounded-xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Add tax</p>
                    <p class="mt-2 text-sm text-stone-500">Create a new tax rate for invoice items.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" x-on:click="closeAdd()">
                    Close
                </button>
            </div>

            <form method="POST" action="{{ route('settings.taxes.store') }}" class="mt-6 space-y-5">
                <div class="grid gap-4 lg:grid-cols-[1fr_160px]">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Tax name
                        <input type="text" name="name" value="{{ $addOld['name'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                        @if (isset($errors['name']) && empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Rate (%)
                        <input type="number" name="rate" value="{{ $addOld['rate'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" min="0" max="100" step="0.01">
                        @if (isset($errors['rate']) && empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['rate'] ?? '' }}</span>
                        @endif
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-stone-100 pt-4">
                    <button type="button" class="border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" x-on:click="closeAdd()">
                        Cancel
                    </button>
                    <button type="submit" class="bg-stone-900 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-800">
                        Add tax
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div
    id="tax-edit-modal"
    class="fixed inset-0 z-50"
    x-cloak
    x-show="editOpen"
    x-on:click.self="closeEdit()"
>
    <div class="absolute inset-0 bg-stone-900/60"></div>
    <div class="relative mx-auto mt-10 w-full max-w-xl px-6 pb-10">
        <div class="rounded-xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Edit tax</p>
                    <p class="mt-2 text-sm text-stone-500">Update the tax rate and label.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" x-on:click="closeEdit()">
                    Close
                </button>
            </div>

            <form method="POST" x-bind:action="editAction" class="mt-6 space-y-5">
                <div class="grid gap-4 lg:grid-cols-[1fr_160px]">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Tax name
                        <input type="text" name="name" x-model="editName" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                        @if (isset($errors['name']) && !empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Rate (%)
                        <input type="number" name="rate" x-model="editRate" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" min="0" max="100" step="0.01">
                        @if (isset($errors['rate']) && !empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['rate'] ?? '' }}</span>
                        @endif
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-stone-100 pt-4">
                    <button type="button" class="border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" x-on:click="closeEdit()">
                        Cancel
                    </button>
                    <button type="submit" class="bg-stone-900 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-800">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
