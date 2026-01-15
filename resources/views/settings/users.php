@layout('layouts.app', ['title' => 'Settings - Users'])

@section('content')
<div
    x-data='{
        addOpen: {{ $autoOpenAddModal ? 'true' : 'false' }},
        editOpen: {{ $autoOpenEditModal ? 'true' : 'false' }},
        editAction: {!! $editActionJson ?? '""' !!},
        editName: {!! $editNameJson ?? '""' !!},
        editEmail: {!! $editEmailJson ?? '""' !!},
        openAdd() {
            this.addOpen = true;
        },
        closeAdd() {
            this.addOpen = false;
        },
        openEdit(action, name, email) {
            this.editAction = action || "";
            this.editName = name || "";
            this.editEmail = email || "";
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
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">User management</h1>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="button" class="bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-800" x-on:click="openAdd()">
                    Add user
                </button>
            </div>
        </div>
        @if (!empty($status ?? ''))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" x-data="{ open: true }" x-show="open">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            {{ $status ?? '' }}
                        </div>
                        <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if (!empty($errors ?? []))
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" x-data="{ open: true }" x-show="open">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <p class="font-semibold">Please review the user fields.</p>
                        </div>
                        <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

        <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                <thead class="text-left text-xs font-semibold uppercase tracking-widest text-stone-500 rounded-t-xl">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-stone-900">{{ $user['name'] ?? 'User' }}</td>
                            <td class="px-4 py-3 text-stone-700">{{ $user['email'] ?? '' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $user['verified_class'] ?? 'bg-stone-100 text-stone-600' }}">
                                    {{ $user['verified_label'] ?? 'Pending' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-stone-500">{{ $user['created_at'] ?? '' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-4">
                                    <button
                                        type="button"
                                        class="text-sm font-semibold text-stone-500 hover:text-stone-800"
                                        data-action="{{ $user['edit_action'] ?? '' }}"
                                        data-name="{{ $user['name'] ?? '' }}"
                                        data-email="{{ $user['email'] ?? '' }}"
                                        x-on:click="openEdit($el.dataset.action, $el.dataset.name, $el.dataset.email)"
                                    >
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('settings.users.delete', ['user' => $user['id']]) }}" data-confirm="Remove this user?">
                                        <button type="submit" class="text-sm font-semibold text-rose-500 hover:text-rose-600">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-stone-500">No users have been added yet.</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="user-modal" class="fixed inset-0 z-50" x-cloak x-show="addOpen" x-on:click.self="closeAdd()">
    <div class="absolute inset-0 bg-stone-900/60"></div>
    <div class="relative mx-auto mt-10 w-full max-w-xl px-6 pb-10">
        <div class="rounded-xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Add user</p>
                    <p class="mt-2 text-sm text-stone-500">Create a new login for your workspace.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" x-on:click="closeAdd()">
                    Close
                </button>
            </div>

            <form method="POST" action="{{ route('settings.users.store') }}" class="mt-6 space-y-5">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Full name
                    <input type="text" name="name" value="{{ $addOld['name'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['name']) && empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Email address
                    <input type="email" name="email" value="{{ $addOld['email'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['email']) && empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['email'] ?? '' }}</span>
                    @endif
                </label>

                <div class="grid gap-4 lg:grid-cols-2">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Password
                        <input type="password" name="password" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                        @if (isset($errors['password']) && empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['password'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Confirm password
                        <input type="password" name="password_confirmation" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                        @if (isset($errors['password_confirmation']) && empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['password_confirmation'] ?? '' }}</span>
                        @endif
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-stone-100 pt-4">
                    <button type="button" class="border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" x-on:click="closeAdd()">
                        Cancel
                    </button>
                    <button type="submit" class="bg-stone-900 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-800">
                        Add user
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div
    id="user-edit-modal"
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
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Edit user</p>
                    <p class="mt-2 text-sm text-stone-500">Update account details or reset a password.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" x-on:click="closeEdit()">
                    Close
                </button>
            </div>

            <form method="POST" x-bind:action="editAction" class="mt-6 space-y-5">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Full name
                    <input type="text" name="name" x-model="editName" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['name']) && !empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Email address
                    <input type="email" name="email" x-model="editEmail" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['email']) && !empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['email'] ?? '' }}</span>
                    @endif
                </label>

                <div class="grid gap-4 lg:grid-cols-2">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        New password (optional)
                        <input type="password" name="password" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                        @if (isset($errors['password']) && !empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['password'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Confirm password
                        <input type="password" name="password_confirmation" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                        @if (isset($errors['password_confirmation']) && !empty($editId))
                            <span class="mt-1 text-xs text-rose-500">{{ $errors['password_confirmation'] ?? '' }}</span>
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
