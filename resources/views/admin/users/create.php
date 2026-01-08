@layout('layouts.app', ['title' => 'User Admin'])

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
        <div>
            <p class="text-xs uppercase tracking-widest text-stone-400">Administration</p>
            <p class="mt-2 text-2xl font-semibold text-stone-900">Add a new user</p>
            <p class="mt-2 text-sm text-stone-500">Create accounts for your team and share access securely.</p>
        </div>

        @if (!empty($status ?? ''))
            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ $status ?? '' }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 grid gap-4 lg:grid-cols-2">
            <label class="flex flex-col text-sm font-medium text-stone-700">
                Full name
                <input type="text" name="name" value="{{ $old['name'] ?? '' }}" class="mt-1 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['name']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['name'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Email address
                <input type="email" name="email" value="{{ $old['email'] ?? '' }}" class="mt-1 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['email']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['email'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Temporary password
                <input type="password" name="password" class="mt-1 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['password']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['password'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex flex-col text-sm font-medium text-stone-700">
                Confirm password
                <input type="password" name="password_confirmation" class="mt-1 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                @if (isset($errors['password_confirmation']))
                    <span class="mt-1 text-xs text-rose-500">{{ $errors['password_confirmation'] ?? '' }}</span>
                @endif
            </label>

            <label class="flex items-center gap-3 text-sm text-stone-600 lg:col-span-2">
                <input type="checkbox" name="send_verification" value="1" class="h-4 w-4 rounded border-stone-300 text-stone-700" @if (!empty($old['send_verification'])) checked @endif>
                Send a verification email instead of marking the user as verified.
            </label>

            <div class="flex justify-end gap-3 lg:col-span-2">
                <button type="submit" class="rounded-full bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                    Create user
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-6 py-3 font-semibold text-stone-900">{{ $user['name'] ?? 'User' }}</td>
                            <td class="px-6 py-3">{{ $user['email'] ?? 'N/A' }}</td>
                            <td class="px-6 py-3">
                                @if (!empty($user['email_verified_at']))
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Verified</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right text-stone-500">{{ $user['created_at'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-6 text-center text-stone-500">No users found yet.</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
