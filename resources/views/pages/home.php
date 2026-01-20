@layout('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="space-y-10">
    <div class="flex flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-semibold text-stone-900">Dashboard</h1>
        </div>
        <details class="relative lg:hidden">
            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-700 shadow-sm hover:bg-stone-50">
                <span>Filter</span>
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="none">
                    <path d="M5 7l5 5 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </summary>
            <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/40 px-4 pb-6">
                <div class="w-full max-w-sm rounded-2xl border border-stone-200 bg-white px-4 py-4 shadow-xl">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-stone-900">Filter by date</p>
                        <button type="button" class="rounded-lg border border-stone-200 px-2 py-1 text-xs text-stone-500 hover:bg-stone-50" onclick="this.closest('details').removeAttribute('open')">Close</button>
                    </div>
                    <form method="GET" action="{{ route('home') }}" class="mt-3 grid gap-3 text-xs text-stone-600">
                        <label class="grid gap-1">
                            <span class="text-stone-400">Start</span>
                            <input type="date" name="start" value="{{ $range['start'] ?? '' }}" class="rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700">
                        </label>
                        <label class="grid gap-1">
                            <span class="text-stone-400">End</span>
                            <input type="date" name="end" value="{{ $range['end'] ?? '' }}" class="rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700">
                        </label>
                        <button type="submit" class="rounded-lg bg-stone-900 px-3 py-2 text-xs font-semibold text-white hover:bg-stone-800">Apply</button>
                    </form>
                </div>
            </div>
        </details>
        <form method="GET" action="{{ route('home') }}" class="hidden lg:flex flex-wrap items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-500 shadow-sm">
            <span class="text-stone-400">
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M8 2v3M16 2v3M4 7h16M5 10h14v9H5v-9Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
            <input type="date" name="start" value="{{ $range['start'] ?? '' }}" class="w-[130px] bg-transparent text-xs text-stone-600 focus:outline-none">
            <span class="text-stone-400">-</span>
            <input type="date" name="end" value="{{ $range['end'] ?? '' }}" class="w-[130px] bg-transparent text-xs text-stone-600 focus:outline-none">
            <button type="submit" class="ml-2 rounded-lg bg-stone-900 px-3 py-1 text-xs font-semibold text-white hover:bg-stone-800">Apply</button>
        </form>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-lg font-semibold text-stone-900">Total value</p>
                <div class="mt-4 grid grid-cols-3 text-sm text-stone-500">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Draft</p>
                        <div class="mt-2 space-y-1">
                            @if (!empty($invoiceStatusTotalsCurrencyLabels['draft'] ?? []))
                                @foreach (($invoiceStatusTotalsCurrencyLabels['draft'] ?? []) as $row)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-stone-900">{{ $row['label'] ?? '' }}</span>
                                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
                                            {{ $row['currency'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <span class="font-semibold text-stone-900">{{ $invoiceStatusTotalsLabels['draft'] ?? '' }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Sent</p>
                        <div class="mt-2 space-y-1">
                            @if (!empty($invoiceStatusTotalsCurrencyLabels['sent'] ?? []))
                                @foreach (($invoiceStatusTotalsCurrencyLabels['sent'] ?? []) as $row)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-stone-900">{{ $row['label'] ?? '' }}</span>
                                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
                                            {{ $row['currency'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <span class="font-semibold text-stone-900">{{ $invoiceStatusTotalsLabels['sent'] ?? '' }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Paid</p>
                        <div class="mt-2 space-y-1">
                            @if (!empty($invoiceStatusTotalsCurrencyLabels['paid'] ?? []))
                                @foreach (($invoiceStatusTotalsCurrencyLabels['paid'] ?? []) as $row)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-stone-900">{{ $row['label'] ?? '' }}</span>
                                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
                                            {{ $row['currency'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <span class="font-semibold text-stone-900">{{ $invoiceStatusTotalsLabels['paid'] ?? '' }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-lg font-semibold text-stone-900">Total expenses</p>
                <div class="mt-4 grid grid-cols-3 text-sm text-stone-500">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Average</p>
                        <div class="mt-2 space-y-1">
                            @if (!empty($expenseAverageByCurrency ?? []))
                                @foreach (($expenseAverageByCurrency ?? []) as $row)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-stone-900">{{ $row['label'] ?? '' }}</span>
                                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
                                            {{ $row['currency'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <span class="font-semibold text-stone-900">{{ $expenseAverageLabel ?? '' }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Largest</p>
                        <div class="mt-2 space-y-1">
                            @if (!empty($expenseMaxByCurrency ?? []))
                                @foreach (($expenseMaxByCurrency ?? []) as $row)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-stone-900">{{ $row['label'] ?? '' }}</span>
                                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
                                            {{ $row['currency'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <span class="font-semibold text-stone-900">{{ $expenseMaxLabel ?? '' }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Total</p>
                        @if (!empty($expenseCurrencyBreakdown ?? []))
                            <div class="mt-2 space-y-1">
                                @foreach ($expenseCurrencyBreakdown as $row)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-stone-900">{{ $row['label'] ?? '' }}</span>
                                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
                                            {{ $row['currency'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="mt-2 block font-semibold text-stone-900">{{ $expenseTotalLabel ?? '' }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
       <p class="text-lg font-semibold text-stone-900">Cash Flow</p>
        <div class="border-t border-stone-100 pt-5">
            <div class="grid gap-6 lg:grid-cols-[1fr_200px]">
                <div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-stone-500">
                        <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Incoming</span>
                        <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-rose-400"></span>Outgoing</span>
                        <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-indigo-500"></span>Profit</span>
                    </div>
                    <div class="mt-4 rounded-xl border border-stone-100 bg-stone-50/60 p-4">
                        <svg viewBox="0 0 {{ $cashFlowChart['width'] ?? 0 }} {{ $cashFlowChart['totalHeight'] ?? 0 }}" class="h-56 w-full">
                            <line x1="0" y1="{{ $cashFlowChart['baseline'] ?? 0 }}" x2="{{ $cashFlowChart['width'] ?? 0 }}" y2="{{ $cashFlowChart['baseline'] ?? 0 }}" stroke="#d6d3d1" stroke-width="1" />
                            <line x1="0" y1="{{ ($cashFlowChart['baseline'] ?? 0) - (($cashFlowChart['height'] ?? 0) * 0.5) }}" x2="{{ $cashFlowChart['width'] ?? 0 }}" y2="{{ ($cashFlowChart['baseline'] ?? 0) - (($cashFlowChart['height'] ?? 0) * 0.5) }}" stroke="#e7e5e4" stroke-width="1" />
                            <line x1="0" y1="{{ ($cashFlowChart['baseline'] ?? 0) - ($cashFlowChart['height'] ?? 0) }}" x2="{{ $cashFlowChart['width'] ?? 0 }}" y2="{{ ($cashFlowChart['baseline'] ?? 0) - ($cashFlowChart['height'] ?? 0) }}" stroke="#e7e5e4" stroke-width="1" />
                            <line x1="0" y1="{{ ($cashFlowChart['baseline'] ?? 0) + (($cashFlowChart['negativeHeight'] ?? 0) * 0.5) }}" x2="{{ $cashFlowChart['width'] ?? 0 }}" y2="{{ ($cashFlowChart['baseline'] ?? 0) + (($cashFlowChart['negativeHeight'] ?? 0) * 0.5) }}" stroke="#e7e5e4" stroke-width="1" />
                            <line x1="0" y1="{{ ($cashFlowChart['baseline'] ?? 0) + ($cashFlowChart['negativeHeight'] ?? 0) }}" x2="{{ $cashFlowChart['width'] ?? 0 }}" y2="{{ ($cashFlowChart['baseline'] ?? 0) + ($cashFlowChart['negativeHeight'] ?? 0) }}" stroke="#e7e5e4" stroke-width="1" />

                            @foreach (($cashFlowChart['bars'] ?? []) as $bar)
                                <rect x="{{ $bar['income']['x'] ?? 0 }}" y="{{ $bar['income']['y'] ?? 0 }}" width="{{ $cashFlowChart['barWidth'] ?? 0 }}" height="{{ $bar['income']['height'] ?? 0 }}" fill="#4ade80" rx="2"></rect>
                                <rect x="{{ $bar['expense']['x'] ?? 0 }}" y="{{ $bar['expense']['y'] ?? 0 }}" width="{{ $cashFlowChart['barWidth'] ?? 0 }}" height="{{ $bar['expense']['height'] ?? 0 }}" fill="#fb7185" rx="2"></rect>
                            @endforeach

                            <polyline fill="none" stroke="#6366f1" stroke-width="2" points="{{ $cashFlowChart['profitPolyline'] ?? '' }}"></polyline>
                            @foreach (($cashFlowChart['profitPoints'] ?? []) as $point)
                                <circle cx="{{ $point['x'] ?? 0 }}" cy="{{ $point['y'] ?? 0 }}" r="3" fill="#6366f1" stroke="#fff" stroke-width="1"></circle>
                            @endforeach
                        </svg>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-stone-400">
                        @foreach (($cashFlowLabels ?? []) as $label)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="space-y-6 text-sm text-stone-600">
                    <div>
                        @if (!empty($cashFlowTotalsCurrencyLabels['income'] ?? []) && count($cashFlowTotalsCurrencyLabels['income']) > 1)
                            <div class="space-y-1">
                                @foreach ($cashFlowTotalsCurrencyLabels['income'] as $row)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-stone-900">{{ $row['label'] ?? '' }}</span>
                                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
                                            {{ $row['currency'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-lg font-semibold text-stone-900">{{ $cashFlowTotalsLabels['income'] ?? '' }}</p>
                        @endif
                        <p class="text-xs text-emerald-600">Incoming</p>
                    </div>
                    <div>
                        @if (!empty($cashFlowTotalsCurrencyLabels['expense'] ?? []) && count($cashFlowTotalsCurrencyLabels['expense']) > 1)
                            <div class="space-y-1">
                                @foreach ($cashFlowTotalsCurrencyLabels['expense'] as $row)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-stone-900">{{ $row['label'] ?? '' }}</span>
                                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
                                            {{ $row['currency'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-lg font-semibold text-stone-900">{{ $cashFlowTotalsLabels['expense'] ?? '' }}</p>
                        @endif
                        <p class="text-xs text-rose-500">Outgoing</p>
                    </div>
                    <div>
                        @if (!empty($cashFlowTotalsCurrencyLabels['profit'] ?? []) && count($cashFlowTotalsCurrencyLabels['profit']) > 1)
                            <div class="space-y-1">
                                @foreach ($cashFlowTotalsCurrencyLabels['profit'] as $row)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-stone-900">{{ $row['label'] ?? '' }}</span>
                                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
                                            {{ $row['currency'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-lg font-semibold text-stone-900">{{ $cashFlowTotalsLabels['profit'] ?? '' }}</p>
                        @endif
                        <p class="text-xs text-indigo-600">Profit</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
