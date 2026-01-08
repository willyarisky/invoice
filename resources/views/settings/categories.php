@layout('layouts.app', ['title' => 'Settings - Categories'])

@section('content')
@php
    $autoOpenAddModal = isset($errors['name']) && empty($editId);
    $autoOpenEditModal = !empty($editId);
    $addOld = empty($editId) ? ($old ?? []) : [];
    $editOld = !empty($editId) ? ($old ?? []) : [];
@endphp
<div class="grid gap-8 lg:grid-cols-[240px_1fr]">
    @include('settings/partials/sidebar', ['active' => 'categories'])

    <div class="space-y-6">
        <div class="rounded-lg border border-stone-200 bg-white px-6 py-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-col gap-2">
                    <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                    <p class="text-lg font-semibold text-stone-900">Available categories</p>
                    <p class="text-sm text-stone-500">Keep track of the services or products you invoice for.</p>
                </div>
                <button type="button" class="bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-800" id="open-category-modal">
                    Add category
                </button>
            </div>

            @if (!empty($status ?? ''))
                <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ $status ?? '' }}
                </div>
            @endif

            @if (!empty($errors ?? []))
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="font-semibold">Please review the category fields.</p>
                </div>
            @endif

            <div class="mt-6 overflow-hidden rounded-lg border border-stone-200">
                <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($categories as $category)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-stone-900">{{ $category['name'] ?? '' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-4">
                                        <button
                                            type="button"
                                            class="text-sm font-semibold text-stone-500 hover:text-stone-800"
                                            data-edit-category
                                            data-action="{{ route('settings.categories.update', ['category' => $category['id']]) }}"
                                            data-name="{{ $category['name'] ?? '' }}"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('settings.categories.delete', ['category' => $category['id']]) }}">
                                            <button type="submit" class="text-sm font-semibold text-rose-500 hover:text-rose-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-stone-500">No categories created yet.</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="category-modal" class="fixed inset-0 z-50 hidden" data-auto-open="{{ $autoOpenAddModal ? 'true' : 'false' }}">
    <div class="absolute inset-0 bg-stone-900/60"></div>
    <div class="relative mx-auto mt-10 w-full max-w-xl px-6 pb-10">
        <div class="rounded-lg bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Add category</p>
                    <p class="mt-2 text-sm text-stone-500">Add a new category for your invoice items.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" data-close-category-modal>
                    Close
                </button>
            </div>

            <form method="POST" action="{{ route('settings.categories.store') }}" class="mt-6 space-y-5">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Category name
                    <input type="text" name="name" value="{{ $addOld['name'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['name']) && empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                    @endif
                </label>

                <div class="flex items-center justify-end gap-2 border-t border-stone-100 pt-4">
                    <button type="button" class="border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" data-close-category-modal>
                        Cancel
                    </button>
                    <button type="submit" class="bg-stone-900 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-800">
                        Add category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div
    id="category-edit-modal"
    class="fixed inset-0 z-50 hidden"
    data-auto-open="{{ $autoOpenEditModal ? 'true' : 'false' }}"
    data-auto-action="{{ $autoOpenEditModal ? route('settings.categories.update', ['category' => $editId]) : '' }}"
    data-auto-name="{{ $editOld['name'] ?? '' }}"
>
    <div class="absolute inset-0 bg-stone-900/60"></div>
    <div class="relative mx-auto mt-10 w-full max-w-xl px-6 pb-10">
        <div class="rounded-lg bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Edit category</p>
                    <p class="mt-2 text-sm text-stone-500">Update the category name.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" data-close-category-edit-modal>
                    Close
                </button>
            </div>

            <form method="POST" action="" class="mt-6 space-y-5" data-category-edit-form>
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Category name
                    <input type="text" name="name" value="{{ $editOld['name'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" data-category-edit-name>
                    @if (isset($errors['name']) && !empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                    @endif
                </label>

                <div class="flex items-center justify-end gap-2 border-t border-stone-100 pt-4">
                    <button type="button" class="border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" data-close-category-edit-modal>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('category-modal');
        const editModal = document.getElementById('category-edit-modal');
        const openBtn = document.getElementById('open-category-modal');
        const closeBtns = document.querySelectorAll('[data-close-category-modal]');
        const closeEditBtns = document.querySelectorAll('[data-close-category-edit-modal]');
        const editButtons = document.querySelectorAll('[data-edit-category]');
        const editForm = editModal?.querySelector('[data-category-edit-form]');
        const editName = editModal?.querySelector('[data-category-edit-name]');

        const openModal = () => modal?.classList.remove('hidden');
        const closeModal = () => modal?.classList.add('hidden');
        const openEditModal = (payload) => {
            if (!editModal || !editForm || !editName) {
                return;
            }

            editForm.action = payload.action || '';
            editName.value = payload.name || '';
            editModal.classList.remove('hidden');
        };
        const closeEditModal = () => editModal?.classList.add('hidden');

        openBtn?.addEventListener('click', openModal);
        closeBtns.forEach((btn) => btn.addEventListener('click', closeModal));
        closeEditBtns.forEach((btn) => btn.addEventListener('click', closeEditModal));
        editButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                openEditModal({
                    action: btn.dataset.action,
                    name: btn.dataset.name,
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
                name: editModal.dataset.autoName,
            });
        }
    });
</script>
@endsection
