@layout('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="rounded-3xl border border-stone-200 bg-white px-6 py-6 text-sm text-stone-600 shadow-sm">
    Welcome back, {{ $user->name ?? 'friend' }}. Invoice insights now live on the main dashboard &mdash; head <a href="{{ route('home') }}" class="text-stone-900 font-semibold hover:underline">there</a> for charts and recent activity.
    <form method="POST" action="{{ route('auth.logout') }}" class="mt-4 inline-flex">
        <button type="submit" class="rounded-full border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">Log out</button>
    </form>
</div>
@endsection
