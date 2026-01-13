@layout('layouts.auth', ['title' => 'Sign In'])

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs uppercase tracking-[0.4em] text-stone-400">Welcome back</p>
        <h2 class="mt-3 font-display text-3xl text-stone-900">Sign in to your workspace</h2>
        <p class="mt-2 text-sm text-stone-500">Use your team email to continue.</p>
    </div>

    @if (!empty($status ?? ''))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" x-data="{ open: true }" x-show="open">
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

    @if (!empty($errorList))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" x-data="{ open: true }" x-show="open">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <p class="font-semibold">We could not sign you in.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-4">
                        @foreach ($errorList as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

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

        <div class="flex items-center justify-between text-sm text-stone-500">
            <span>Need access? Ask your admin.</span>
            <a href="{{ route('auth.password.forgot') }}" class="text-stone-700 hover:text-stone-900">Forgot password?</a>
        </div>

        <button type="submit" class="w-full rounded-xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-stone-900/20 transition hover:-translate-y-0.5 hover:bg-stone-800">
            Sign in
        </button>

    </form>
</div>
@endsection
