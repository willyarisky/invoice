@layout('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="space-y-10">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-semibold text-stone-900">Dashboard</h1>
            <span class="text-stone-400">:</span>
        </div>
        <form method="GET" action="{{ route('home') }}" class="flex flex-wrap items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm text-stone-500 shadow-sm">
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
                <p class="text-lg font-semibold text-stone-900">Total invoices: <span class="font-semibold text-stone-900">{{ $invoiceCountTotal ?? 0 }}</span></p>
                <div class="mt-3 h-2 rounded-full bg-stone-200">
                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $invoiceCountProgress ?? 0 }}%"></div>
                </div>
                <div class="mt-4 grid grid-cols-3 text-sm text-stone-500">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Draft</p>
                        <p class="mt-1 font-semibold text-stone-900">{{ $invoiceStatusCounts['draft'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Sent</p>
                        <p class="mt-1 font-semibold text-stone-900">{{ $invoiceStatusCounts['sent'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Paid</p>
                        <p class="mt-1 font-semibold text-stone-900">{{ $invoiceStatusCounts['paid'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white px-6 py-5 shadow-sm">
            <div>
                <p class="text-lg font-semibold text-stone-900">Total value: <span class="font-semibold text-stone-900">{{ $invoiceAmountTotalLabel ?? '' }}</span></p>
                <div class="mt-3 h-2 rounded-full bg-stone-200">
                    <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $invoiceAmountProgress ?? 0 }}%"></div>
                </div>
                <div class="mt-4 grid grid-cols-3 text-sm text-stone-500">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Draft</p>
                        <p class="mt-1 font-semibold text-stone-900">{{ $invoiceStatusTotalsLabels['draft'] ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Sent</p>
                        <p class="mt-1 font-semibold text-stone-900">{{ $invoiceStatusTotalsLabels['sent'] ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-stone-400">Paid</p>
                        <p class="mt-1 font-semibold text-stone-900">{{ $invoiceStatusTotalsLabels['paid'] ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-stone-200 bg-white px-6 py-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-lg font-semibold text-stone-900">Cash Flow</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-stone-500">
                <button type="button" class="text-stone-400">...</button>
            </div>
        </div>
        <div class="mt-5 border-t border-stone-100 pt-5">
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
                        <p class="text-lg font-semibold text-stone-900">{{ $cashFlowTotalsLabels['income'] ?? '' }}</p>
                        <p class="text-xs text-emerald-600">Incoming</p>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-stone-900">{{ $cashFlowTotalsLabels['expense'] ?? '' }}</p>
                        <p class="text-xs text-rose-500">Outgoing</p>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-stone-900">{{ $cashFlowTotalsLabels['profit'] ?? '' }}</p>
                        <p class="text-xs text-indigo-600">Profit</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
