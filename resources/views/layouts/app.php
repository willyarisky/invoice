@include('components.head', ['title' => $title ?? $brandName])
<div class="min-h-screen bg-stone-50 ">
    <header class="border-b border-stone-200 bg-white text-stone-900 print:hidden">
        <div class="mx-auto flex w-full max-w-[1200px] flex-col gap-4 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div>
                    <p class="text-lg font-semibold text-stone-900">{{ $brandName }}</p>
                </div>
            </div>
            <div class="flex flex-1 flex-col gap-3 lg:flex-row lg:items-center lg:justify-end">
                <div class="flex items-center gap-2">
                    @if ($currentUser)
                        <a href="{{ route('settings.index') }}" class="inline-flex items-center justify-center rounded-xl border border-stone-200 bg-white p-2 text-stone-700 hover:bg-stone-50 {{ !empty($settingsActive) ? 'bg-stone-100 text-stone-900 border-stone-300' : '' }}" aria-label="Settings">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                        </a>
                        <form method="POST" action="{{ route('auth.logout') }}">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white p-2 text-sm font-semibold text-stone-700 hover:bg-stone-50" aria-label="Log out">
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M10 17l5-5-5-5M4 12h10M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
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
        <nav class="mx-auto flex w-full max-w-[1200px] gap-6 overflow-x-auto px-6 text-sm text-stone-500">
            @foreach ($navItems ?? [] as $item)
                <a href="{{ $item['href'] ?? '#' }}" class="border-b-2 px-1 py-3 font-medium {{ !empty($item['isActive']) ? 'border-stone-900 text-stone-900' : 'border-transparent hover:border-stone-300 hover:text-stone-800' }}">
                    {{ $item['label'] ?? '' }}
                </a>
            @endforeach
        </nav>
    </header>
    <main class="mx-auto w-full max-w-[1200px] px-6 py-6">
        @yield('content')
    </main>
</div>
@include('components.footer')
