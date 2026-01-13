@layout('layouts.app', ['title' => 'Settings - Company'])

@section('content')
<div class="grid gap-8 lg:grid-cols-[240px_1fr]">
    @include('settings/partials/sidebar', [
        'settingsActive' => $settingsActive,
        'settingsLinkBase' => $settingsLinkBase,
        'isAdmin' => $isAdmin,
    ])

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Company</h1>
        </div>
        </div>
        <div class="rounded-xl border border-stone-200 bg-white px-6 py-6 shadow-sm">

            @if (!empty($status ?? ''))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" x-data="{ open: true }" x-show="open">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            {{ $status ?? '' }}
                        </div>
                        <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if (!empty($errors ?? []))
                <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" x-data="{ open: true }" x-show="open">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <p class="font-semibold">Please review the highlighted fields.</p>
                        </div>
                        <button type="button" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-current hover:bg-black/5" x-on:click="open = false" aria-label="Dismiss message">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 lg:grid-cols-2">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Company name
                    <input type="text" name="business_name" value="{{ $values['business_name'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['business_name']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['business_name'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Company logo
                    <input type="file" name="company_logo" accept="image/*" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
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
                    <textarea name="company_address" rows="3" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">{{ $values['company_address'] ?? '' }}</textarea>
                    @if (isset($errors['company_address']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['company_address'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                    Company email
                    <input type="email" name="company_email" value="{{ $values['company_email'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['company_email']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['company_email'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                    Company phone
                    <input type="text" name="company_phone" value="{{ $values['company_phone'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['company_phone']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['company_phone'] ?? '' }}</span>
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
