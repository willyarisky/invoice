@php
    $businessName = \App\Models\Setting::getValue('business_name');
    $brandName = $businessName !== '' ? $businessName : 'Invoice App';
    $request = \Zero\Lib\Http\Request::instance();
    $path = trim($request->path(), '/');
    $currentUser = \Zero\Lib\Auth\Auth::user();
    $isAdmin = false;
    if ($currentUser && isset($currentUser->email)) {
        $isAdmin = \App\Models\Admin::query()
            ->where('email', strtolower((string) $currentUser->email))
            ->exists();
    }
    $navItems = [
        ['label' => 'Dashboard', 'href' => route('home'), 'pattern' => '/^$/'],
        ['label' => 'Invoices', 'href' => route('invoices.index'), 'pattern' => '/^invoices/'],
        ['label' => 'Clients', 'href' => route('clients.index'), 'pattern' => '/^clients/'],
        ['label' => 'Settings', 'href' => route('settings.index'), 'pattern' => '/^settings/'],
    ];
    if ($isAdmin) {
        $navItems[] = ['label' => 'Admin', 'href' => route('admin.users.create'), 'pattern' => '/^admin/'];
    }
@endphp
@include('components.head', ['title' => $title ?? $brandName])
<div class="min-h-screen bg-stone-50">
    <header class="border-b border-stone-200 bg-white text-stone-900 print:hidden">
        <div class="mx-auto flex w-full max-w-screen-2xl flex-col gap-4 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-sky-500 text-sm font-semibold text-white">
                    {{ strtoupper(substr($brandName, 0, 1)) }}
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Workspace</p>
                    <p class="text-lg font-semibold text-stone-900">{{ $brandName }}</p>
                </div>
            </div>
            <div class="flex flex-1 flex-col gap-3 lg:flex-row lg:items-center lg:justify-end">
                <div class="flex items-center gap-2">
                    @if ($currentUser)
                        <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 border border-stone-200 bg-stone-900 px-4 py-2 text-sm font-semibold rounded-lg text-white shadow-sm hover:bg-stone-800">
                            <span>New Invoice</span>
                        </a>
                        <form method="POST" action="{{ route('auth.logout') }}">
                            <button type="submit" class="inline-flex items-center gap-2 border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                                Log out
                            </button>
                        </form>
                    @else
                        <a href="{{ route('auth.login.show') }}" class="inline-flex items-center gap-2 border border-stone-200 bg-stone-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-stone-800">
                            <span>Sign in</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="border-t border-stone-100">
            <nav class="mx-auto flex w-full max-w-screen-2xl gap-6 overflow-x-auto px-6 text-sm text-stone-500">
                @foreach ($navItems as $item)
                    @php
                        $isActive = $item['pattern'] === '/^$/' ? $path === '' : preg_match($item['pattern'], $path);
                    @endphp
                    <a href="{{ $item['href'] }}" class="border-b-2 px-1 py-3 font-medium {{ $isActive ? 'border-stone-900 text-stone-900' : 'border-transparent hover:border-stone-300 hover:text-stone-800' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>
    <main class="mx-auto w-full max-w-screen-2xl px-6 py-6">
        @yield('content')
    </main>
</div>
@include('components.footer')
