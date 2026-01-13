@layout('layouts.auth', ['title' => 'Registration Closed'])

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs uppercase tracking-[0.4em] text-stone-400">Registration</p>
        <h2 class="mt-3 font-display text-3xl text-stone-900">Registration is closed</h2>
        <p class="mt-2 text-sm text-stone-500">Ask your administrator for access to the workspace.</p>
    </div>

    <a href="{{ route('auth.login.show') }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 px-5 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50">
        Back to sign in
    </a>
</div>
@endsection
