@include('components.head', ['title' => $title ?? 'Simple Invoice App'])
@php
    $request = \Zero\Lib\Http\Request::instance();
    $path = trim($request->path(), '/');
    $navItems = [
        ['label' => 'Dashboard', 'href' => route('home'), 'pattern' => '/^$/'],
        ['label' => 'Invoices', 'href' => route('invoices.index'), 'pattern' => '/^invoices/'],
        ['label' => 'Clients', 'href' => route('clients.index'), 'pattern' => '/^clients/'],
        ['label' => 'Settings', 'href' => route('settings.index'), 'pattern' => '/^settings/'],
    ];
@endphp
<div class="min-h-screen flex bg-stone-50">
    <aside class="hidden md:flex md:w-64 lg:w-72 flex-col bg-white border-r border-stone-200 print:hidden">
        <div class="px-6 py-5 border-b border-stone-100">
            <p class="text-xs uppercase tracking-widest text-stone-400">Simple</p>
            <p class="text-2xl font-semibold text-stone-900">Invoice App</p>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-1">
            @foreach ($navItems as $item)
                @php
                    $isActive = $item['pattern'] === '/^$/' ? $path === '' : preg_match($item['pattern'], $path);
                @endphp
                <a href="{{ $item['href'] }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium {{ $isActive ? 'bg-stone-100 text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="px-6 py-4 border-t border-stone-100 text-sm text-stone-500">
            <p>Need help? Email <span class="font-semibold text-stone-700">support@example.com</span></p>
        </div>
    </aside>
    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-stone-200 px-4 sm:px-6 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <div>
                <p class="text-xs uppercase tracking-widest text-stone-400">Akaunting Lite</p>
                <h1 class="text-xl font-semibold text-stone-900">{{ $title ?? 'Simple Invoice App' }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <span class="hidden sm:inline-block text-sm text-stone-500">Today {{ date('M j, Y') }}</span>
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 rounded-full bg-stone-800 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-stone-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-800">
                    <span>New Invoice</span>
                </a>
            </div>
        </header>
        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-10">
            @yield('content')
        </main>
    </div>
</div>
@include('components.footer')
