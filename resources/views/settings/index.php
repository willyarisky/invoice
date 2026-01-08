@layout('layouts.app', ['title' => 'Settings - Company'])

@section('content')
<div class="grid gap-8 lg:grid-cols-[240px_1fr]">
    @include('settings/partials/sidebar', ['active' => 'company'])

    <div class="space-y-6">
        <div class="rounded-lg border border-stone-200 bg-white px-6 py-6 shadow-sm">
            <div>
                <p class="text-xs uppercase tracking-widest text-stone-400">Settings</p>
                <p class="mt-2 text-2xl font-semibold text-stone-900">Company</p>
                <p class="mt-2 text-sm text-stone-500">These details appear on invoices and client-facing documents.</p>
            </div>

            @if (!empty($status ?? ''))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ $status ?? '' }}
                </div>
            @endif

            @if (!empty($errors ?? []))
                <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="font-semibold">Please review the highlighted fields.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.company.update') }}" class="mt-6 grid gap-4 lg:grid-cols-2">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Company name
                    <input type="text" name="business_name" value="{{ $values['business_name'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['business_name']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['business_name'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Company logo URL
                    <input type="text" name="company_logo" value="{{ $values['company_logo'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="https://">
                    @if (isset($errors['company_logo']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['company_logo'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                    Company address
                    <textarea name="company_address" rows="3" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">{{ $values['company_address'] ?? '' }}</textarea>
                    @if (isset($errors['company_address']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['company_address'] ?? '' }}</span>
                    @endif
                </label>

                <div class="flex justify-end gap-3 lg:col-span-2">
                    <button type="submit" class="bg-stone-900 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-800">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
