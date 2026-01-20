@layout('layouts.app', ['title' => 'Settings - Email'])

@section('content')
<div class="grid gap-8 lg:grid-cols-[240px_1fr]">
    @include('settings/partials/sidebar', [
        'settingsActive' => $settingsActive,
        'settingsLinkBase' => $settingsLinkBase,
    ])

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Email Settings</h1>
        </div>
        </div>
        <div class="rounded-xl border border-stone-200 bg-white px-6 py-6 shadow-sm">

            @include('components/alerts', [
                'status' => $status ?? null,
                'errors' => $errors ?? [],
                'alertStatusClass' => 'mt-5',
                'alertErrorClass' => 'mt-5',
            ])

            <form method="POST" action="{{ route('settings.email.update') }}" class="mt-6 grid gap-4 lg:grid-cols-2">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    From address
                    <input type="email" name="mail_from_address" value="{{ $values['mail_from_address'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['mail_from_address']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['mail_from_address'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    From name
                    <input type="text" name="mail_from_name" value="{{ $values['mail_from_name'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['mail_from_name']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['mail_from_name'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Mailer
                    <input type="text" name="mail_mailer" value="{{ $values['mail_mailer'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="smtp">
                    @if (isset($errors['mail_mailer']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['mail_mailer'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Mail host
                    <input type="text" name="mail_host" value="{{ $values['mail_host'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['mail_host']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['mail_host'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Mail port
                    <input type="number" name="mail_port" value="{{ $values['mail_port'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" min="1" max="65535">
                    @if (isset($errors['mail_port']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['mail_port'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Mail encryption
                    <input type="text" name="mail_encryption" value="{{ $values['mail_encryption'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700" placeholder="tls">
                    @if (isset($errors['mail_encryption']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['mail_encryption'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Mail username
                    <input type="text" name="mail_username" value="{{ $values['mail_username'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['mail_username']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['mail_username'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Mail password
                    <input type="password" name="mail_password" value="{{ $values['mail_password'] ?? '' }}" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">
                    @if (isset($errors['mail_password']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['mail_password'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700 lg:col-span-2">
                    Default invoice email message
                    <textarea name="invoice_email_message" rows="6" class="mt-1 border border-stone-200 bg-white px-4 py-2 text-stone-700">{{ $values['invoice_email_message'] ?? '' }}</textarea>
                    <span class="mt-1 text-xs text-stone-500">Available tokens: {customer_name}, {invoice_no}, {total}, {due_date}, {company_name}, {invoice_public_url}. HTML is supported.</span>
                    @if (isset($errors['invoice_email_message']))
                        <span class="mt-1 text-xs text-rose-500">{{ $errors['invoice_email_message'] ?? '' }}</span>
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
