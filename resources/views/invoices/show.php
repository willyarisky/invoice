@layout('layouts.app', ['title' => $pageTitle ?? 'Invoice'])

@section('content')
<div x-data="{ emailModalOpen: {{ $autoOpenEmailModal ? 'true' : 'false' }}, paymentModalOpen: {{ $autoOpenPaymentModal ? 'true' : 'false' }} }">
    <div class="space-y-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between print:hidden">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-semibold text-stone-900">Invoice: {{ $invoiceNo }}</h1>
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badge }}">
                        {{ $statusLabel ?? '' }}
                    </span>
                </div>
                <a href="{{ route('invoices.index') }}" class="text-sm text-stone-500 hover:text-stone-800">&larr; Back to invoices</a>
            </div>
            <div class="relative" x-data="{ open: false, publicUrl: {{ $publicUrlJson ?? '""' }}, copyPublicLink() { if (!this.publicUrl) { return; } if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(this.publicUrl).then(() => { window.alert('Public link copied to clipboard.'); }).catch(() => { window.prompt('Copy link', this.publicUrl); }); } else { window.prompt('Copy link', this.publicUrl); } } }">
                <button type="button" class="inline-flex items-center gap-2 rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-800" x-on:click="open = !open" x-bind:aria-expanded="open.toString()">
                    Actions
                    <svg class="h-4 w-4 text-white/80" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div class="absolute right-0 mt-2 w-56 rounded-xl border border-stone-200 bg-white py-2 text-sm text-stone-700 shadow-lg" x-cloak x-show="open" x-on:click.outside="open = false">
                    <form method="POST" action="{{ route('invoices.markSent', ['invoice' => $invoice['id']]) }}">
                        <button type="submit" class="flex w-full items-center px-4 py-2 text-left font-semibold text-stone-600 hover:bg-stone-50" {{ $status === 'paid' ? 'disabled' : '' }}>
                            Mark as Sent
                        </button>
                    </form>
                    <a href="{{ route('invoices.edit', ['invoice' => $invoice['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50">
                        Edit invoice
                    </a>
                    <a href="{{ route('invoices.duplicate', ['invoice' => $invoice['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50">
                        Duplicate invoice
                    </a>
                    <a href="{{ route('invoices.download', ['invoice' => $invoice['id']]) }}" class="flex items-center px-4 py-2 font-semibold text-stone-600 hover:bg-stone-50">
                        Download
                    </a>
                    <button type="button" class="flex w-full items-center px-4 py-2 text-left font-semibold text-stone-600 hover:bg-stone-50" x-on:click="copyPublicLink(); open = false">
                        Share public link
                    </button>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
            <div class="space-y-6 print:hidden">
                @if (!empty($emailStatus ?? ''))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ $emailStatus ?? '' }}
                    </div>
                @endif
                @if (!empty($paymentStatus ?? ''))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ $paymentStatus ?? '' }}
                    </div>
                @endif
                <div class="rounded-xl border border-stone-200 bg-white px-4 py-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-stone-900">Get Paid</p>
                        <span class="text-xs font-semibold {{ $status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $statusLabel ?? '' }}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">Amount due: {{ $amountDueLabel }}</p>
                    @if ($lastStatusEvent)
                        <p class="mt-1 text-xs text-stone-400">{{ $lastStatusEvent['summary'] ?? '' }} {{ $lastStatusEvent['timestamp'] ? '- ' . $lastStatusEvent['timestamp'] : '' }}</p>
                    @endif
                    @if ($hasPaymentTransaction)
                        <p class="mt-2 text-xs text-stone-500">
                            Payment recorded: {{ $paymentTransactionLabel }}
                            @if ($paymentTransactionDate !== '')
                                - {{ $paymentTransactionDate }}
                            @endif
                        </p>
                        @if ($paymentTransactionDescription !== '')
                            <p class="mt-1 text-xs text-stone-400">{{ $paymentTransactionDescription }}</p>
                        @endif
                    @endif
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        @if ($hasPaymentTransaction)
                            <a href="{{ route('transactions.show', ['transaction' => $paymentTransaction['id']]) }}" class="text-xs font-semibold text-stone-600 hover:text-stone-900">
                                View transaction
                            </a>
                            <button type="button" class="rounded-full border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50" x-on:click="paymentModalOpen = true">
                                Edit payment
                            </button>
                        @elseif ($status !== 'paid')
                            <button type="button" class="rounded-full border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-50" x-on:click="paymentModalOpen = true">
                                Record payment
                            </button>
                        @else
                            <span class="text-xs text-stone-400">Invoice marked as paid.</span>
                        @endif
                    </div>
                </div>
                <div class="rounded-xl border border-stone-200 bg-white px-4 py-4 shadow-sm" x-data="{ showAll: false }">
                    <p class="text-xs uppercase tracking-widest text-stone-400">Timeline</p>
                    @if (!empty($timelineItems))
                        <div class="mt-3 space-y-3">
                            @foreach ($timelineItems as $index => $event)
                                <div class="flex gap-3" x-show="showAll || {{ $index }} < 3">
                                    <span class="mt-2 h-2 w-2 rounded-full bg-stone-400"></span>
                                    <div>
                                        <p class="text-sm font-semibold text-stone-800">{{ $event['summary'] ?? '' }}</p>
                                        @if (!empty($event['detail'] ?? ''))
                                            <p class="text-xs text-stone-500">{{ $event['detail'] }}</p>
                                        @endif
                                        <p class="text-xs text-stone-400">{{ $event['timestamp'] ?? '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($timelineCount > 3)
                            <button type="button" class="mt-3 text-xs font-semibold text-stone-600 hover:text-stone-900" x-on:click="showAll = !showAll" x-text="showAll ? 'Show less' : 'Show all {{ $timelineCount }}'"></button>
                        @endif
                    @else
                        <p class="mt-2 text-sm text-stone-500">No activity recorded yet.</p>
                    @endif
                </div>
            </div>

            @include('invoices/partials/detail', $detailView ?? [])
        </div>
    </div>

    <div id="invoice-payment-modal" class="fixed inset-0 z-50 print:hidden" x-cloak x-show="paymentModalOpen" x-on:click.self="paymentModalOpen = false">
        <div class="absolute inset-0 bg-stone-900/60"></div>
        <div class="relative mx-auto mt-10 w-full max-w-xl px-6 pb-10">
            <div class="rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">{{ $paymentModalTitle }}</p>
                        <p class="mt-2 text-2xl font-semibold text-stone-900">{{ $paymentModalTitle }}</p>
                        <p class="mt-2 text-sm text-stone-500">{{ $paymentModalSubtitle }}</p>
                    </div>
                    <button type="button" class="text-sm font-semibold text-stone-500 hover:text-stone-800" x-on:click="paymentModalOpen = false">
                        Close
                    </button>
                </div>

                @if (!empty($paymentErrors ?? []))
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                        <p class="font-semibold">Please review the payment details.</p>
                    </div>
                @endif

                <form method="POST" action="{{ $paymentFormAction }}" class="mt-4 space-y-4">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Amount
                        <input type="number" name="amount" value="{{ $paymentAmount }}" min="0.01" step="0.01" class="mt-1 rounded-xl border border-stone-200 bg-white px-3 py-2 text-stone-700">
                        @if (isset($paymentErrors['amount']))
                            <span class="mt-1 text-xs text-rose-500">{{ $paymentErrors['amount'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Date
                        <input type="date" name="date" value="{{ $paymentDate }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-3 py-2 text-stone-700">
                        @if (isset($paymentErrors['date']))
                            <span class="mt-1 text-xs text-rose-500">{{ $paymentErrors['date'] ?? '' }}</span>
                        @endif
                    </label>

                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        Description (optional)
                        <input type="text" name="description" value="{{ $paymentDescription }}" class="mt-1 rounded-xl border border-stone-200 bg-white px-3 py-2 text-stone-700">
                    </label>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" class="rounded-xl border border-stone-200 px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-50" x-on:click="paymentModalOpen = false">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-stone-800 px-6 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                            {{ $paymentActionLabel }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="invoice-email-modal" class="fixed inset-0 z-50 print:hidden" x-cloak x-show="emailModalOpen" x-on:click.self="emailModalOpen = false">
        <div class="absolute inset-0 bg-stone-900/60"></div>
        <div class="relative mx-auto mt-10 w-full max-w-2xl px-6 pb-10">
            <div class="rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Invoice email</p>
                        <p class="mt-2 text-2xl font-semibold text-stone-900">Send invoice</p>
                        <p class="mt-2 text-sm text-stone-500">This will be sent to {{ $invoice['client_email'] ?? '—' }}.</p>
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

                <form method="POST" action="{{ route('invoices.email', ['invoice' => $invoice['id']]) }}" class="mt-4 space-y-4">
                    <label class="flex flex-col text-sm font-medium text-stone-700">
                        To
                        <input type="email" value="{{ $invoice['client_email'] ?? '' }}" class="mt-1 rounded-xl border border-stone-200 bg-stone-50 px-3 py-2 text-stone-700" readonly>
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
