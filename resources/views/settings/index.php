@layout('layouts.app', ['title' => 'Settings - Company'])

@section('content')
<div class="grid w-full min-w-0 gap-8 lg:grid-cols-[240px_1fr]">
    @include('settings/partials/sidebar', [
        'settingsActive' => $settingsActive,
        'settingsLinkBase' => $settingsLinkBase,
    ])

    <div class="w-full min-w-0 space-y-6">
        <div class="flex items-center justify-between gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-stone-900">Company</h1>
            </div>
        </div>

        @if($status || $errors)
            <div>
            @include('components/alerts', [
                'status' => $status ?? null,
                'errors' => $errors ?? [],
            ])
            </div>
        @endif
        <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
            <div class="border-t border-stone-100 px-4 py-5 sm:px-6 sm:py-6">
                <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="grid gap-4 lg:grid-cols-2">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Company name
                    <input type="text" name="business_name" value="{{ $values['business_name'] ?? '' }}" class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['business_name']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['business_name'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Company logo
                    <input type="file" name="company_logo" accept="image/*" class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    <span class="mt-2 text-xs text-stone-400">Max file size 47KB.</span>
                    @if (isset($errors['company_logo']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['company_logo'] ?? '' }}</span>
                    @endif
                    @if (!empty($values['company_logo'] ?? ''))
                        <span class="mt-3 text-xs text-stone-500">Current logo</span>
                        <img src="{{ $values['company_logo'] }}" alt="Company logo" class="mt-2 h-12 w-12 object-cover">
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-stone-500">
                            <input type="checkbox" name="remove_logo" value="1" class="h-4 w-4 border border-stone-300 text-stone-900">
                            Remove logo
                        </label>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                    Company address
                    <textarea name="company_address" rows="3" class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">{{ $values['company_address'] ?? '' }}</textarea>
                    @if (isset($errors['company_address']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['company_address'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                    Company email
                    <input type="email" name="company_email" value="{{ $values['company_email'] ?? '' }}" class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['company_email']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['company_email'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                    Company phone
                    <input type="text" name="company_phone" value="{{ $values['company_phone'] ?? '' }}" class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['company_phone']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['company_phone'] ?? '' }}</span>
                    @endif
                </label>

                <div class="flex justify-end gap-3 lg:col-span-2">
                    <button type="submit" class="w-full bg-stone-900 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-800 sm:w-auto">
                        Save changes
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
