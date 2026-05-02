<?php

namespace App\Services;

use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class MarketingOrderService
{
    /**
     * Get filtered marketing orders query.
     */
    public function getFilteredQuery(array $filters): Builder
    {
        $query = MarketingOrder::query();

        // Time Filters
        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('tanggal', [$filters['startDate'], $filters['endDate']]);
        } elseif (!empty($filters['dateRange']) && $filters['dateRange'] !== 'semua') {
            $this->applyTimeRangeFilter($query, $filters['dateRange']);
        }

        // Status Filter
        if (!empty($filters['statusFilter'])) {
            $query->where('status', $filters['statusFilter']);
        }

        // Search Filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('sap_no', 'like', "%{$search}%")
                  ->orWhere('pelanggan', 'like', "%{$search}%")
                  ->orWhere('art_no', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Apply predefined time ranges to query.
     */
    protected function applyTimeRangeFilter(Builder $query, string $range): void
    {
        match ($range) {
            'harian'   => $query->whereDate('tanggal', today()),
            'mingguan' => $query->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()]),
            'bulanan'  => $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year),
            'tahunan'  => $query->whereYear('tanggal', now()->year),
            default    => null,
        };
    }

    /**
     * Load tracking logs grouped by division.
     */
    public function getTrackingLogs(string $sapNo): array
    {
        return ProductionActivity::with('operator')
            ->whereHas('marketingOrder', fn($q) => $q->where('sap_no', $sapNo))
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('division_name')
            ->toArray();
    }

    /**
     * Get summary counts for the dashboard.
     */
    public function getStatusSummary(): array
    {
        return [
            'knitting'  => MarketingOrder::where('status', 'knitting')->count(),
            'active'    => MarketingOrder::whereNotIn('status', ['knitting', 'finished'])->count(),
            'completed' => MarketingOrder::where('status', 'finished')->count(),
            'total'     => MarketingOrder::count(),
        ];
    }
}
