@php
    $active = $active ?? '';
    $linkBase = 'flex items-center rounded-lg px-3 py-2 text-sm';
@endphp
<aside class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <p class="text-sm font-semibold text-stone-800">Settings</p>
    </div>
    <nav class="mt-6 space-y-1">
        <a href="{{ route('settings.index') }}" class="{{ $linkBase }} {{ $active === 'company' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Company</a>
        <a href="{{ route('settings.currency.index') }}" class="{{ $linkBase }} {{ $active === 'currency' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Currency</a>
        <a href="{{ route('settings.email.index') }}" class="{{ $linkBase }} {{ $active === 'email' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Email Settings</a>
        <a href="{{ route('settings.categories.index') }}" class="{{ $linkBase }} {{ $active === 'categories' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Categories</a>
        <a href="{{ route('settings.taxes.index') }}" class="{{ $linkBase }} {{ $active === 'taxes' ? 'bg-stone-100 text-stone-900 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}">Taxes</a>
    </nav>
</aside>
