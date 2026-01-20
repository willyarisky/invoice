<aside class="w-full min-w-0 py-4 lg:py-5">
    <div class="flex items-center justify-between lg:hidden">
        <p class="text-2xl font-semibold text-stone-900">Settings</p>
        <details class="relative">
            <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-700 shadow-sm hover:bg-stone-50" aria-label="Open settings menu">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            </summary>
            <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/40 px-4 pb-6" onclick="if (event.target === this) { this.closest('details').removeAttribute('open'); }">
                <div class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white px-4 py-4 shadow-xl">
                    <div class="flex items-center justify-between">
                        <p class="text-2xl font-semibold text-stone-900">Settings</p>
                        <button type="button" class="rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-500 hover:bg-stone-50" onclick="this.closest('details').removeAttribute('open')">Close</button>
                    </div>
                    <div class="md:mt-3 mt-1 space-y-1">
                        <a href="{{ route('settings.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium {{ ($settingsActive ?? '') === 'company' ? 'bg-stone-100 text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-800' }}">Company</a>
                        <a href="{{ route('settings.currency.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium {{ ($settingsActive ?? '') === 'currency' ? 'bg-stone-100 text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-800' }}">Currency</a>
                        <a href="{{ route('settings.email.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium {{ ($settingsActive ?? '') === 'email' ? 'bg-stone-100 text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-800' }}">Email Settings</a>
                        <a href="{{ route('settings.categories.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium {{ ($settingsActive ?? '') === 'categories' ? 'bg-stone-100 text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-800' }}">Categories</a>
                        <a href="{{ route('settings.taxes.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium {{ ($settingsActive ?? '') === 'taxes' ? 'bg-stone-100 text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-800' }}">Taxes</a>
                        <a href="{{ route('settings.users.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium {{ ($settingsActive ?? '') === 'users' ? 'bg-stone-100 text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-800' }}">Users</a>
                    </div>
                </div>
            </div>
        </details>
    </div>
    <div class="hidden items-center justify-between lg:flex">
        <p class="text-sm font-semibold text-stone-800">Settings</p>
    </div>
    <nav class="mt-6 space-y-1 hidden lg:block">
        <a href="{{ route('settings.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'company' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Company</a>
        <a href="{{ route('settings.currency.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'currency' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Currency</a>
        <a href="{{ route('settings.email.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'email' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Email Settings</a>
        <a href="{{ route('settings.categories.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'categories' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Categories</a>
        <a href="{{ route('settings.taxes.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'taxes' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Taxes</a>
        <a href="{{ route('settings.users.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'users' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Users</a>
    </nav>
</aside>
