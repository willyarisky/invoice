<?php

namespace App\Controllers;

use DateTime;
use App\Models\Setting;
use App\Services\ViewData;
use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;

class HomeController
{
    public function index(Request $request)
    {
        $layout = ViewData::appLayout();
        $startDate = $this->normaliseDate($request->input('start'), date('Y-01-01'));
        $endDate = $this->normaliseDate($request->input('end'), date('Y-12-31'));

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $statusRows = DBML::table('invoices')
            ->select('status', DBML::raw('COUNT(*) as total'), DBML::raw('COALESCE(SUM(total), 0) as amount'))
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->groupBy('status')
            ->get();

        $invoiceStatusCounts = ['draft' => 0, 'sent' => 0, 'paid' => 0];
        $invoiceStatusTotals = ['draft' => 0.0, 'sent' => 0.0, 'paid' => 0.0];

        foreach ($statusRows as $row) {
            $key = strtolower((string) ($row['status'] ?? ''));
            if (array_key_exists($key, $invoiceStatusCounts)) {
                $invoiceStatusCounts[$key] = (int) ($row['total'] ?? 0);
                $invoiceStatusTotals[$key] = (float) ($row['amount'] ?? 0);
            }
        }

        $series = $this->buildMonthlySeries($startDate, $endDate);
        $transactions = DBML::table('transactions')
            ->select('type', 'amount', 'date')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        foreach ($transactions as $transaction) {
            $monthKey = substr((string) ($transaction['date'] ?? ''), 0, 7);
            if ($monthKey === '' || !isset($series[$monthKey])) {
                continue;
            }

            $amount = (float) ($transaction['amount'] ?? 0);
            $type = strtolower((string) ($transaction['type'] ?? 'expense'));
            if ($type === 'income') {
                $series[$monthKey]['income'] += $amount;
                continue;
            }
            $series[$monthKey]['expense'] += $amount;
        }

        $cashFlowTotals = [
            'income' => 0.0,
            'expense' => 0.0,
            'profit' => 0.0,
        ];
        $cashFlowSeries = [];

        foreach ($series as $month => $data) {
            $profit = $data['income'] - $data['expense'];
            $cashFlowTotals['income'] += $data['income'];
            $cashFlowTotals['expense'] += $data['expense'];
            $cashFlowSeries[] = [
                'key' => $month,
                'label' => $data['label'],
                'income' => $data['income'],
                'expense' => $data['expense'],
                'profit' => $profit,
            ];
        }

        $cashFlowTotals['profit'] = $cashFlowTotals['income'] - $cashFlowTotals['expense'];

        $invoiceCountTotal = array_sum($invoiceStatusCounts);
        $invoiceAmountTotal = array_sum($invoiceStatusTotals);
        $invoiceCountPaid = (int) ($invoiceStatusCounts['paid'] ?? 0);
        $invoiceAmountPaid = (float) ($invoiceStatusTotals['paid'] ?? 0);
        $invoiceCountProgress = $invoiceCountTotal > 0 ? min(100, (int) round(($invoiceCountPaid / $invoiceCountTotal) * 100)) : 0;
        $invoiceAmountProgress = $invoiceAmountTotal > 0 ? min(100, (int) round(($invoiceAmountPaid / $invoiceAmountTotal) * 100)) : 0;

        $invoiceAmountTotalLabel = Setting::formatMoney($invoiceAmountTotal);
        $invoiceStatusTotalsLabels = [
            'draft' => Setting::formatMoney((float) ($invoiceStatusTotals['draft'] ?? 0)),
            'sent' => Setting::formatMoney((float) ($invoiceStatusTotals['sent'] ?? 0)),
            'paid' => Setting::formatMoney((float) ($invoiceStatusTotals['paid'] ?? 0)),
        ];

        $chartWidth = 600;
        $chartHeight = 170;
        $chartNegativeHeight = 70;
        $chartTotalHeight = $chartHeight + $chartNegativeHeight;
        $baseline = $chartHeight;
        $pointCount = count($cashFlowSeries);
        $step = $pointCount > 1 ? $chartWidth / ($pointCount - 1) : $chartWidth;
        $barSlot = $pointCount > 0 ? $chartWidth / $pointCount : $chartWidth;
        $barWidth = min(18, $barSlot * 0.35);
        $barGap = 4;

        $maxPositive = 1.0;
        $maxNegative = 1.0;
        foreach ($cashFlowSeries as $point) {
            $income = (float) ($point['income'] ?? 0);
            $expense = (float) ($point['expense'] ?? 0);
            $profit = (float) ($point['profit'] ?? 0);
            $maxPositive = max($maxPositive, $income, $profit);
            $maxNegative = max($maxNegative, $expense, $profit < 0 ? abs($profit) : 0);
        }

        $profitPolyline = [];
        $profitPoints = [];
        $bars = [];
        foreach ($cashFlowSeries as $index => $point) {
            $income = (float) ($point['income'] ?? 0);
            $expense = (float) ($point['expense'] ?? 0);
            $profit = (float) ($point['profit'] ?? 0);
            $x = $pointCount > 1 ? $step * $index : $chartWidth / 2;

            $incomeHeight = ($income / $maxPositive) * $chartHeight;
            $incomeY = $baseline - $incomeHeight;

            $expenseHeight = ($expense / $maxNegative) * $chartNegativeHeight;
            $expenseY = $baseline;

            if ($profit >= 0) {
                $profitY = $baseline - (($profit / $maxPositive) * $chartHeight);
            } else {
                $profitY = $baseline + ((abs($profit) / $maxNegative) * $chartNegativeHeight);
            }

            $profitPolyline[] = $x . ',' . $profitY;
            $profitPoints[] = ['x' => $x, 'y' => $profitY];

            $barCenter = $pointCount > 0 ? $barSlot * $index + $barSlot / 2 : $chartWidth / 2;
            $bars[] = [
                'income' => [
                    'x' => $barCenter - $barWidth - ($barGap / 2),
                    'y' => $incomeY,
                    'height' => $incomeHeight,
                ],
                'expense' => [
                    'x' => $barCenter + ($barGap / 2),
                    'y' => $expenseY,
                    'height' => $expenseHeight,
                ],
            ];
        }

        $labelIndexes = [];
        if ($pointCount > 0) {
            $labelIndexes[] = 0;
            if ($pointCount > 2) {
                $labelIndexes[] = (int) floor(($pointCount - 1) / 2);
            }
            if ($pointCount > 1) {
                $labelIndexes[] = $pointCount - 1;
            }
            $labelIndexes = array_values(array_unique($labelIndexes));
        }

        $cashFlowLabels = [];
        foreach ($labelIndexes as $labelIndex) {
            $cashFlowLabels[] = (string) ($cashFlowSeries[$labelIndex]['label'] ?? '');
        }

        $cashFlowTotalsLabels = [
            'income' => Setting::formatMoney((float) ($cashFlowTotals['income'] ?? 0)),
            'expense' => Setting::formatMoney((float) ($cashFlowTotals['expense'] ?? 0)),
            'profit' => Setting::formatMoney((float) ($cashFlowTotals['profit'] ?? 0)),
        ];

        return view('pages/home', array_merge($layout, [
            'range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'invoiceStatusCounts' => $invoiceStatusCounts,
            'invoiceStatusTotals' => $invoiceStatusTotals,
            'invoiceCountTotal' => $invoiceCountTotal,
            'invoiceAmountTotalLabel' => $invoiceAmountTotalLabel,
            'invoiceCountProgress' => $invoiceCountProgress,
            'invoiceAmountProgress' => $invoiceAmountProgress,
            'invoiceStatusTotalsLabels' => $invoiceStatusTotalsLabels,
            'cashFlow' => [
                'series' => $cashFlowSeries,
                'totals' => $cashFlowTotals,
            ],
            'cashFlowChart' => [
                'width' => $chartWidth,
                'height' => $chartHeight,
                'negativeHeight' => $chartNegativeHeight,
                'totalHeight' => $chartTotalHeight,
                'baseline' => $baseline,
                'barWidth' => $barWidth,
                'bars' => $bars,
                'profitPolyline' => implode(' ', $profitPolyline),
                'profitPoints' => $profitPoints,
            ],
            'cashFlowLabels' => $cashFlowLabels,
            'cashFlowTotalsLabels' => $cashFlowTotalsLabels,
        ]));
    }

    private function normaliseDate(?string $value, ?string $fallback = null): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback ?? date('Y-m-d');
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);

        if ($date === false) {
            return $fallback ?? date('Y-m-d');
        }

        return $date->format('Y-m-d');
    }

    /**
     * @return array<string, array{label: string, income: float, expense: float}>
     */
    private function buildMonthlySeries(string $startDate, string $endDate): array
    {
        $start = DateTime::createFromFormat('Y-m-d', $startDate) ?: new DateTime($startDate);
        $end = DateTime::createFromFormat('Y-m-d', $endDate) ?: new DateTime($endDate);

        $start->modify('first day of this month');
        $end->modify('first day of this month');

        $series = [];
        while ($start <= $end) {
            $key = $start->format('Y-m');
            $series[$key] = [
                'label' => $start->format('M Y'),
                'income' => 0.0,
                'expense' => 0.0,
            ];
            $start->modify('+1 month');
        }

        return $series;
    }
}
