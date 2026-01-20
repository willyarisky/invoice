@layout('layouts.auth', ['title' => 'Sign In'])

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs uppercase tracking-widest text-stone-400">Welcome back</p>
        <h2 class="mt-2 text-2xl font-semibold text-stone-900">Sign in</h2>
    </div>

    @include('components/alerts', [
        'status' => $status ?? null,
    ])

    @include('components/alerts', [
        'alertErrorList' => $errorList ?? [],
        'alertErrorTitle' => 'We could not sign you in.',
        'alertErrorClass' => '',
    ])

    <form method="POST" action="{{ route('auth.login.attempt') }}" class="space-y-4">
        <label class="flex flex-col text-sm font-medium text-stone-700">
            Email address
            <input
                type="email"
                name="email"
                value="{{ $old['email'] ?? '' }}"
                class="mt-1 rounded-xl border {{ $emailError ? 'border-rose-300 bg-rose-50' : 'border-stone-200 bg-white' }} px-4 py-3 text-stone-700 focus:border-stone-400 focus:outline-none"
                autocomplete="email"
                required
            >
            @if ($emailError)
                <span class="mt-1 text-xs text-rose-500">{{ $emailError }}</span>
            @endif
        </label>

        <label class="flex flex-col text-sm font-medium text-stone-700">
            Password
            <input
                type="password"
                name="password"
                class="mt-1 rounded-xl border {{ $passwordError ? 'border-rose-300 bg-rose-50' : 'border-stone-200 bg-white' }} px-4 py-3 text-stone-700 focus:border-stone-400 focus:outline-none"
                autocomplete="current-password"
                required
            >
            @if ($passwordError)
                <span class="mt-1 text-xs text-rose-500">{{ $passwordError }}</span>
            @endif
        </label>

        <button type="submit" class="w-full rounded-xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white hover:bg-stone-800">
            Sign in
        </button>

    </form>
</div>
@endsection
