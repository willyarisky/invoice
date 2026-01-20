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

        $defaultCurrency = Setting::getValue('default_currency');

        $statusRows = DBML::table('invoices')
            ->select('status', DBML::raw('COUNT(*) as total'), DBML::raw('COALESCE(SUM(total), 0) as amount'))
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->groupBy('status')
            ->get();

        $statusCurrencyRows = DBML::table('invoices')
            ->select('status', 'currency', DBML::raw('COALESCE(SUM(total), 0) as amount'))
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->groupBy('status', 'currency')
            ->get();

        $invoiceCurrencyRows = DBML::table('invoices')
            ->select('currency', DBML::raw('COALESCE(SUM(total), 0) as amount'))
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->groupBy('currency')
            ->get();

        $invoiceCurrencyCodes = DBML::table('invoices')
            ->select('currency')
            ->groupBy('currency')
            ->get();

        $invoiceCurrencyOrder = $this->normalizeCurrencyCodes($invoiceCurrencyCodes, $defaultCurrency);
        $invoiceCurrencyBreakdown = $this->buildCurrencyBreakdown(
            $invoiceCurrencyRows,
            $invoiceCurrencyOrder
        );

        $invoiceStatusCounts = ['draft' => 0, 'sent' => 0, 'paid' => 0];
        $invoiceStatusTotals = ['draft' => 0.0, 'sent' => 0.0, 'paid' => 0.0];
        $invoiceStatusTotalsByCurrency = ['draft' => [], 'sent' => [], 'paid' => []];

        foreach ($statusRows as $row) {
            $key = strtolower((string) ($row['status'] ?? ''));
            if (array_key_exists($key, $invoiceStatusCounts)) {
                $invoiceStatusCounts[$key] = (int) ($row['total'] ?? 0);
                $invoiceStatusTotals[$key] = (float) ($row['amount'] ?? 0);
            }
        }

        foreach ($statusCurrencyRows as $row) {
            $status = strtolower((string) ($row['status'] ?? ''));
            if (!array_key_exists($status, $invoiceStatusTotalsByCurrency)) {
                continue;
            }
            $currency = strtoupper(trim((string) ($row['currency'] ?? '')));
            if ($currency === '') {
                $currency = strtoupper($defaultCurrency);
            }
            $amount = (float) ($row['amount'] ?? 0);
            $invoiceStatusTotalsByCurrency[$status][$currency] = ($invoiceStatusTotalsByCurrency[$status][$currency] ?? 0) + $amount;
        }

        $series = $this->buildMonthlySeries($startDate, $endDate);
        $transactions = DBML::table('transactions')
            ->select('type', 'amount', 'date', 'currency')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        $cashFlowCurrencyCodes = DBML::table('transactions')
            ->select('currency')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->groupBy('currency')
            ->get();

        $cashFlowCurrencyOrder = $this->normalizeCurrencyCodes($cashFlowCurrencyCodes, $defaultCurrency);

        $expenseCurrencyRows = DBML::table('transactions')
            ->select('currency', DBML::raw('COALESCE(SUM(amount), 0) as amount'))
            ->where('type', 'expense')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->groupBy('currency')
            ->get();

        $expenseCurrencyStats = DBML::table('transactions')
            ->select(
                'currency',
                DBML::raw('COUNT(*) as total_count'),
                DBML::raw('COALESCE(SUM(amount), 0) as amount'),
                DBML::raw('COALESCE(MAX(amount), 0) as max_amount')
            )
            ->where('type', 'expense')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->groupBy('currency')
            ->get();

        $expenseCurrencyCodes = DBML::table('transactions')
            ->select('currency')
            ->where('type', 'expense')
            ->groupBy('currency')
            ->get();

        $expenseCurrencyOrder = $this->normalizeCurrencyCodes($expenseCurrencyCodes, $defaultCurrency);
        $expenseCurrencyBreakdown = $this->buildCurrencyBreakdown(
            $expenseCurrencyRows,
            $expenseCurrencyOrder
        );

        $expenseTotalsByCurrency = [];
        $expenseCountsByCurrency = [];
        $expenseMaxByCurrency = [];
        foreach ($expenseCurrencyStats as $row) {
            $currency = strtoupper(trim((string) ($row['currency'] ?? '')));
            if ($currency === '') {
                $currency = strtoupper($defaultCurrency);
            }
            $expenseTotalsByCurrency[$currency] = (float) ($row['amount'] ?? 0);
            $expenseCountsByCurrency[$currency] = (int) ($row['total_count'] ?? 0);
            $expenseMaxByCurrency[$currency] = (float) ($row['max_amount'] ?? 0);
        }

        $invoiceStatusTotalsCurrencyLabels = [];
        foreach ($invoiceStatusTotalsByCurrency as $status => $totals) {
            $invoiceStatusTotalsCurrencyLabels[$status] = $this->formatCurrencyTotals(
                $totals,
                $invoiceCurrencyOrder
            );
        }

        $expenseAverageByCurrency = $this->formatCurrencyAverages(
            $expenseTotalsByCurrency,
            $expenseCountsByCurrency,
            $expenseCurrencyOrder
        );
        $expenseMaxByCurrencyLabels = $this->formatCurrencyTotals(
            $expenseMaxByCurrency,
            $expenseCurrencyOrder
        );

        $expenseCountTotal = 0;
        $expenseAmountTotal = 0.0;
        $expenseMaxAmount = 0.0;
        $cashFlowTotalsByCurrency = [
            'income' => [],
            'expense' => [],
        ];

        foreach ($transactions as $transaction) {
            $monthKey = substr((string) ($transaction['date'] ?? ''), 0, 7);
            if ($monthKey === '' || !isset($series[$monthKey])) {
                continue;
            }

            $amount = (float) ($transaction['amount'] ?? 0);
            $type = strtolower((string) ($transaction['type'] ?? 'expense'));
            $currency = strtoupper(trim((string) ($transaction['currency'] ?? '')));
            if ($currency === '') {
                $currency = strtoupper($defaultCurrency);
            }
            if ($type === 'income') {
                $series[$monthKey]['income'] += $amount;
                $cashFlowTotalsByCurrency['income'][$currency] = ($cashFlowTotalsByCurrency['income'][$currency] ?? 0) + $amount;
                continue;
            }
            $series[$monthKey]['expense'] += $amount;
            $cashFlowTotalsByCurrency['expense'][$currency] = ($cashFlowTotalsByCurrency['expense'][$currency] ?? 0) + $amount;

            $expenseCountTotal += 1;
            $expenseAmountTotal += $amount;
            if ($amount > $expenseMaxAmount) {
                $expenseMaxAmount = $amount;
            }
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
        $cashFlowTotalsByCurrency['profit'] = [];
        foreach ($cashFlowCurrencyOrder as $currency) {
            $income = (float) ($cashFlowTotalsByCurrency['income'][$currency] ?? 0);
            $expense = (float) ($cashFlowTotalsByCurrency['expense'][$currency] ?? 0);
            $cashFlowTotalsByCurrency['profit'][$currency] = $income - $expense;
        }

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

        $expenseAverage = $expenseCountTotal > 0 ? $expenseAmountTotal / $expenseCountTotal : 0.0;
        $expenseTotalLabel = Setting::formatMoney($expenseAmountTotal);
        $expenseAverageLabel = Setting::formatMoney($expenseAverage);
        $expenseMaxLabel = Setting::formatMoney($expenseMaxAmount);
        $expenseProgress = 0;
        $cashFlowTotal = $cashFlowTotals['income'] + $cashFlowTotals['expense'];
        if ($cashFlowTotal > 0) {
            $expenseProgress = min(100, (int) round(($cashFlowTotals['expense'] / $cashFlowTotal) * 100));
        }

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
        $cashFlowTotalsCurrencyLabels = [
            'income' => $this->formatCurrencyTotals($cashFlowTotalsByCurrency['income'], $cashFlowCurrencyOrder),
            'expense' => $this->formatCurrencyTotals($cashFlowTotalsByCurrency['expense'], $cashFlowCurrencyOrder),
            'profit' => $this->formatCurrencyTotals($cashFlowTotalsByCurrency['profit'], $cashFlowCurrencyOrder),
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
            'invoiceStatusTotalsCurrencyLabels' => $invoiceStatusTotalsCurrencyLabels,
            'invoiceCurrencyBreakdown' => $invoiceCurrencyBreakdown,
            'expenseCountTotal' => $expenseCountTotal,
            'expenseTotalLabel' => $expenseTotalLabel,
            'expenseAverageLabel' => $expenseAverageLabel,
            'expenseMaxLabel' => $expenseMaxLabel,
            'expenseProgress' => $expenseProgress,
            'expenseAverageByCurrency' => $expenseAverageByCurrency,
            'expenseMaxByCurrency' => $expenseMaxByCurrencyLabels,
            'expenseCurrencyBreakdown' => $expenseCurrencyBreakdown,
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
            'cashFlowTotalsCurrencyLabels' => $cashFlowTotalsCurrencyLabels,
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

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param string[] $currencyOrder
     * @return array<int, array{currency: string, label: string}>
     */
    private function buildCurrencyBreakdown(array $rows, array $currencyOrder = []): array
    {
        $totals = [];
        $defaultCurrency = Setting::getValue('default_currency');

        foreach ($rows as $row) {
            $currency = strtoupper(trim((string) ($row['currency'] ?? '')));
            if ($currency === '') {
                $currency = strtoupper($defaultCurrency);
            }
            $amount = (float) ($row['amount'] ?? 0);
            $totals[$currency] = ($totals[$currency] ?? 0) + $amount;
        }

        $currencyOrder = $currencyOrder !== [] ? $currencyOrder : array_keys($totals);
        $currencyOrder = array_values(array_unique($currencyOrder));

        if (count($currencyOrder) <= 1) {
            return [];
        }

        $breakdown = [];
        foreach ($currencyOrder as $currency) {
            $amount = (float) ($totals[$currency] ?? 0);
            $breakdown[] = [
                'currency' => $currency,
                'label' => Setting::formatMoney($amount, $currency),
            ];
        }

        return $breakdown;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return string[]
     */
    private function normalizeCurrencyCodes(array $rows, string $defaultCurrency): array
    {
        $currencyCodes = [];

        foreach ($rows as $row) {
            $currency = strtoupper(trim((string) ($row['currency'] ?? '')));
            if ($currency === '') {
                $currency = strtoupper($defaultCurrency);
            }
            $currencyCodes[] = $currency;
        }

        return array_values(array_unique($currencyCodes));
    }

    /**
     * @param array<string, float> $totals
     * @param string[] $currencyOrder
     * @return array<int, array{currency: string, label: string}>
     */
    private function formatCurrencyTotals(array $totals, array $currencyOrder): array
    {
        if ($currencyOrder === []) {
            $currencyOrder = array_keys($totals);
        }

        $labels = [];
        foreach ($currencyOrder as $currency) {
            $labels[] = [
                'currency' => $currency,
                'label' => Setting::formatMoney((float) ($totals[$currency] ?? 0), $currency),
            ];
        }

        return $labels;
    }

    /**
     * @param array<string, float> $totals
     * @param array<string, int> $counts
     * @param string[] $currencyOrder
     * @return array<int, array{currency: string, label: string}>
     */
    private function formatCurrencyAverages(array $totals, array $counts, array $currencyOrder): array
    {
        if ($currencyOrder === []) {
            $currencyOrder = array_keys($totals);
        }

        $labels = [];
        foreach ($currencyOrder as $currency) {
            $count = (int) ($counts[$currency] ?? 0);
            $average = $count > 0 ? ((float) ($totals[$currency] ?? 0) / $count) : 0.0;
            $labels[] = [
                'currency' => $currency,
                'label' => Setting::formatMoney($average, $currency),
            ];
        }

        return $labels;
    }
}
