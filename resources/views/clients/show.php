@layout('layouts.app', ['title' => 'Client Details'])

@section('content')
<div x-data="{ emailModalOpen: {{ $autoOpenEmailModal ? 'true' : 'false' }} }">
<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-stone-200 text-sm font-semibold text-stone-700">
                {{ $initials }}
            </div>
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">{{ $clientName }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $client['email'] ?? '—' }}</p>
            </div>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('invoices.create') }}" class="rounded-xl bg-stone-800 px-5 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                New invoice
            </a>
            <a href="{{ route('clients.edit', ['client' => $client['id']]) }}" class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                Edit
            </a>
            <a href="{{ route('clients.index') }}" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                Back
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[220px_1fr]">
        <div class="space-y-6">
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Address</p>
                <p class="mt-2 text-sm text-stone-700 whitespace-pre-line">{{ $client['address'] ?? 'No mailing address' }}</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Client portal</p>
                <p class="mt-2 text-sm text-stone-500">Not configured</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-400">Email</p>
                <p class="mt-2 text-sm text-stone-500">Send a message to the customer.</p>

                @if (!empty($emailStatus ?? ''))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                        {{ $emailStatus ?? '' }}
                    </div>
                @endif

                <button type="button" class="mt-4 w-full rounded-xl bg-stone-800 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700" x-on:click="emailModalOpen = true">
                    Send email
                </button>
            </div>
        </div>

        <div class="space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 text-center shadow-sm">
                    <p class="text-2xl font-semibold text-stone-900">
                        {{ $totalsLabels['overdue'] ?? '' }}
                    </p>
                    <p class="mt-1 text-xs uppercase tracking-widest text-stone-400">Overdue</p>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 text-center shadow-sm">
                    <p class="text-2xl font-semibold text-stone-900">
                        {{ $totalsLabels['open'] ?? '' }}
                    </p>
                    <p class="mt-1 text-xs uppercase tracking-widest text-stone-400">Open</p>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white px-5 py-4 text-center shadow-sm">
                    <p class="text-2xl font-semibold text-stone-900">
                        {{ $totalsLabels['paid'] ?? '' }}
                    </p>
                    <p class="mt-1 text-xs uppercase tracking-widest text-stone-400">Paid</p>
                </div>
            </div>

            <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="flex items-center gap-6 border-b border-stone-100 px-6 py-4 text-sm">
                    <span class="border-b-2 border-stone-900 pb-2 font-semibold text-stone-900">Invoices</span>
                    <span class="pb-2 text-stone-400">Transactions</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 text-sm text-stone-700">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-widest text-stone-500">
                            <tr>
                                <th class="px-6 py-3">Due date</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Invoice</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($invoiceRows as $invoice)
                                <tr>
                                    <td class="px-6 py-3">
                                        <p class="font-semibold text-stone-900">{{ $invoice['due_date'] ?? '—' }}</p>
                                        <p class="text-xs text-stone-500">Invoice date {{ $invoice['date'] ?? '—' }}</p>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center rounded-xl px-3 py-1 text-xs font-semibold {{ $invoice['badge_class'] ?? '' }}">
                                            {{ $invoice['status_label'] ?? '' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <a href="{{ route('invoices.show', ['invoice' => $invoice['id']]) }}" class="font-semibold text-stone-900 hover:text-stone-500">
                                            {{ $invoice['invoice_no'] ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3 text-right font-semibold">
                                        {{ $invoice['total_label'] ?? '' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-stone-500">No invoices for this client yet.</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="client-email-modal" class="fixed inset-0 z-50 print:hidden" x-cloak x-show="emailModalOpen" x-on:click.self="emailModalOpen = false">
    <div class="absolute inset-0 bg-stone-900/60"></div>
    <div class="relative mx-auto mt-10 w-full max-w-2xl px-6 pb-10">
        <div class="rounded-xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-400">Client email</p>
                    <p class="mt-2 text-2xl font-semibold text-stone-900">Send message</p>
                    <p class="mt-2 text-sm text-stone-500">This will be sent to {{ $client['email'] ?? '—' }}.</p>
                </div>
                <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" x-on:click="emailModalOpen = false">
                    Close
                </button>
            </div>

            @if (!empty($emailErrors ?? []))
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                    <p class="font-semibold">Please review the email fields.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('clients.email', ['client' => $client['id']]) }}" class="mt-4 space-y-4">
                <label class="flex flex-col text-sm font-medium text-stone-700">
                    To
                    <input type="email" value="{{ $client['email'] ?? '' }}" class="mt-1 rounded-xl border border-stone-200 bg-stone-50 px-3 py-2 text-stone-700" readonly>
                    @if (isset($emailErrors['email']))
                        <span class="mt-1 text-xs text-rose-500">{{ $emailErrors['email'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Subject
                    <input type="text" name="subject" value="{{ $emailSubject }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-3 py-2 text-stone-700">
                    @if (isset($emailErrors['subject']))
                        <span class="mt-1 text-xs text-rose-500">{{ $emailErrors['subject'] ?? '' }}</span>
                    @endif
                </label>

                <label class="flex flex-col text-sm font-medium text-stone-700">
                    Message
                    <textarea name="message" rows="5" class="mt-1 rounded-xl border border-stone-200 bg-white px-3 py-2 text-stone-700">{{ $emailMessage }}</textarea>
                    @if (isset($emailErrors['message']))
                        <span class="mt-1 text-xs text-rose-500">{{ $emailErrors['message'] ?? '' }}</span>
                    @endif
                </label>

                @if ($ccAdminDefault)
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="cc_admin" value="1" class="h-4 w-4 rounded-xl border-stone-300 text-stone-700" @if (!empty($emailOld['cc_admin'])) checked @endif>
                        CC admin ({{ $adminEmail }})
                    </label>
                @endif
                @if ($currentUserEmail !== '')
                    <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="cc_myself" value="1" class="h-4 w-4 rounded-xl border-stone-300 text-stone-700" @if (!empty($emailOld['cc_myself'])) checked @endif>
                        CC myself ({{ $currentUserEmail }})
                    </label>
                @endif

                <div class="flex items-center justify-end gap-3">
                    <button type="button" class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" x-on:click="emailModalOpen = false">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                        Send email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
