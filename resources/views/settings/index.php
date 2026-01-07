@layout('layouts.app', ['title' => 'Settings'])

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($settings as $setting)
            <div class="rounded-2xl border border-stone-200 bg-white px-4 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">{{ $setting['label'] }}</p>
                <p class="text-lg font-semibold text-stone-900 mt-1">{{ $setting['value'] }}</p>
            </div>
        @endforeach
    </div>
    <div class="rounded-3xl border border-dashed border-stone-300 bg-white/70 px-6 py-8 text-center text-sm text-stone-500">
        Settings are stored in configuration files for now. Wire them to a database table whenever edits should be persisted.
    </div>
</div>
@endsection
