<?php

declare(strict_types=1);

namespace App\Controllers\Concerns;

use Zero\Lib\Support\Paginator;

trait BuildsPagination
{
    /**
     * @param array<string, mixed> $queryParams
     * @return array<string, mixed>
     */
    private function buildPaginationData(Paginator $paginator, string $baseUrl, array $queryParams = []): array
    {
        unset($queryParams['page']);

        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $total = $paginator->total();
        $perPage = $paginator->perPage();

        $from = $total > 0 ? (($current - 1) * $perPage + 1) : 0;
        $to = $total > 0 ? min($total, $current * $perPage) : 0;

        $pages = $this->paginationWindow($current, $last, 5);
        $pageLinks = [];
        foreach ($pages as $page) {
            $pageLinks[] = [
                'page' => $page,
                'url' => $this->buildPageUrl($baseUrl, $queryParams, $page),
                'is_current' => $page === $current,
            ];
        }

        return [
            'current' => $current,
            'last' => $last,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'prev_url' => $current > 1 ? $this->buildPageUrl($baseUrl, $queryParams, $current - 1) : '',
            'next_url' => $current < $last ? $this->buildPageUrl($baseUrl, $queryParams, $current + 1) : '',
            'pages' => $pageLinks,
        ];
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    private function buildPageUrl(string $baseUrl, array $queryParams, int $page): string
    {
        $params = $queryParams;
        $params['page'] = $page;
        $query = http_build_query($params);

        return $query === '' ? $baseUrl : $baseUrl . '?' . $query;
    }

    /**
     * @return array<int, int>
     */
    private function paginationWindow(int $current, int $last, int $max): array
    {
        if ($last <= $max) {
            return range(1, $last);
        }

        $half = (int) floor($max / 2);
        $start = max(1, $current - $half);
        $end = $start + $max - 1;

        if ($end > $last) {
            $end = $last;
            $start = max(1, $end - $max + 1);
        }

        return range($start, $end);
    }
}
