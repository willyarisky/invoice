@layout('layouts.app', ['title' => 'Forgot Password'])

@section('content')
<div class="container py-5" style="max-width: 520px;">
    <h1 class="mb-4 text-center">Forgot Password</h1>

    <p class="text-muted">Enter the email associated with your account and we will send a password reset link.</p>

    @if (!empty($status ?? ''))
        <div class="alert alert-info" role="alert" x-data="{ open: true }" x-show="open">
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

    <form method="POST" action="{{ route('auth.password.email') }}" class="card shadow-sm p-4">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input
                type="email"
                class="form-control {{ isset($errors['email']) ? 'is-invalid' : '' }}"
                id="email"
                name="email"
                value="{{ $old['email'] ?? '' }}"
                required
            >
            @if (isset($errors['email']))
                <div class="invalid-feedback">
                    {{ $errors['email'] ?? '' }}
                </div>
            @endif
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Email Password Reset Link</button>
            <a href="{{ route('auth.login.show') }}" class="btn btn-link">Back to sign in</a>
        </div>
    </form>
</div>
@endsection
