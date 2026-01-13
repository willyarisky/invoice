@layout('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Dashboard</h1>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('invoices.create') }}" class="rounded-xl bg-stone-800 px-5 py-2 text-sm font-semibold text-white hover:bg-stone-700">New Invoice</a>
        </div>
    </div>
    <div class="rounded-xl border border-stone-200 bg-white px-6 py-6 text-sm text-stone-600 shadow-sm">
        Welcome back, {{ $user->name ?? 'friend' }}. Invoice insights now live on the main dashboard &mdash; head <a href="{{ route('home') }}" class="text-stone-900 font-semibold hover:underline">there</a> for charts and recent activity.
        <form method="POST" action="{{ route('auth.logout') }}" class="mt-4 inline-flex">
            <button type="submit" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">Log out</button>
        </form>
    </div>
</div>
@endsection
