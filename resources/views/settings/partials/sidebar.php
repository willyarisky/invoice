<aside class="p-5">
    <div class="flex items-center justify-between">
        <p class="text-sm font-semibold text-stone-800">Settings</p>
    </div>
    <nav class="mt-6 space-y-1">
        <a href="{{ route('settings.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'company' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Company</a>
        <a href="{{ route('settings.currency.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'currency' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Currency</a>
        <a href="{{ route('settings.email.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'email' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Email Settings</a>
        <a href="{{ route('settings.categories.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'categories' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Categories</a>
        <a href="{{ route('settings.taxes.index') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'taxes' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Taxes</a>
        <a href="{{ route('settings.admin.users') }}" class="{{ $settingsLinkBase }} {{ ($settingsActive ?? '') === 'admin' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Admin Users</a>
    </nav>
</aside>
