@layout('layouts.app', ['title' => 'Settings - Admin Users'])

@section('content')
<div x-data='{
    editOpen: {{ $autoOpenEditModal ? 'true' : 'false' }},
    editAction: {!! $editActionJson ?? '""' !!},
    editName: {!! $editNameJson ?? '""' !!},
    editEmail: {!! $editEmailJson ?? '""' !!},
    openEdit(action, name, email) {
        this.editAction = action || "";
        this.editName = name || "";
        this.editEmail = email || "";
        this.editOpen = true;
    },
    closeEdit() {
        this.editOpen = false;
    },
}'>
<div class="grid gap-8 lg:grid-cols-[240px_1fr]">
    @include('settings/partials/sidebar', [
        'settingsActive' => $settingsActive,
        'settingsLinkBase' => $settingsLinkBase,
        'isAdmin' => $isAdmin,
    ])

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Add a new user</h1>
        </div>
        </div>
        <div class="rounded-xl border border-stone-200 bg-white px-6 py-6 shadow-sm">

            @if (!empty($status ?? ''))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ $status ?? '' }}
                </div>
            @endif

            <form method="POST" action="{{ route('settings.admin.users.store') }}" class="mt-6 grid gap-4 lg:grid-cols-2">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Full name
                    <input type="text" name="name" value="{{ $createOld['name'] ?? '' }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['name']) && empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Email address
                    <input type="email" name="email" value="{{ $createOld['email'] ?? '' }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['email']) && empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['email'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Temporary password
                    <input type="password" name="password" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['password']) && empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['password'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Confirm password
                    <input type="password" name="password_confirmation" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['password_confirmation']) && empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['password_confirmation'] ?? '' }}</span>
                    @endif
                </label>

                <div class="flex justify-end gap-3 lg:col-span-2">
                    <button type="submit" class="rounded-xl bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                        Create user
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-stone-100 px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-lg font-semibold text-stone-900">Recent users</p>
                    <p class="text-sm text-stone-500">Showing the 25 most recent accounts.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Verified</th>
                            <th class="px-6 py-3 text-right">Created</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-6 py-3 font-semibold text-stone-900">{{ $user['name'] ?? 'User' }}</td>
                                <td class="px-6 py-3">{{ $user['email'] ?? 'N/A' }}</td>
                                <td class="px-6 py-3">
                                    @if (!empty($user['email_verified_at']))
                                        <span class="inline-flex items-center rounded-xl bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Verified</span>
                                    @else
                                        <span class="inline-flex items-center rounded-xl bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right text-stone-500">{{ $user['created_at'] ?? '' }}</td>
                                <td class="px-6 py-3 text-right">
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
                                        <form method="POST" action="{{ route('settings.admin.users.delete', ['user' => $user['id']]) }}">
                                            <button type="submit" class="text-sm font-semibold text-rose-500 hover:text-rose-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-stone-500">No users found yet.</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div
    id="admin-user-edit-modal"
    class="fixed inset-0 z-50"
    x-cloak
    x-show="editOpen"
    x-on:click.self="closeEdit()"
>
    <div class="absolute inset-0 bg-stone-900/60"></div>
    <div class="relative mx-auto mt-10 w-full max-w-2xl px-6 pb-10">
        <div class="rounded-xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Administration</p>
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Edit user</p>
                    <p class="mt-2 text-sm text-stone-500">Update account details and optionally reset the password.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" x-on:click="closeEdit()">
                    Close
                </button>
            </div>

            <form method="POST" x-bind:action="editAction" class="mt-6 grid gap-4 lg:grid-cols-2">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Full name
                    <input type="text" name="name" x-model="editName" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['name']) && !empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Email address
                    <input type="email" name="email" x-model="editEmail" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['email']) && !empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['email'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    New password
                    <input type="password" name="password" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="Leave blank to keep current">
                    @if (isset($errors['password']) && !empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['password'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Confirm new password
                    <input type="password" name="password_confirmation" class="mt-1 rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['password_confirmation']) && !empty($editId))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['password_confirmation'] ?? '' }}</span>
                    @endif
                </label>

                <div class="flex items-center justify-end gap-3 lg:col-span-2">
                    <button type="button" class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" x-on:click="closeEdit()">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
