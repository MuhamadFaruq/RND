<?php

namespace App\Repositories;

use App\Models\ProductionActivity;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityRepository
{
    /**
     * Log a production activity.
     */
    public function log(array $data): ProductionActivity
    {
        return ProductionActivity::create($data);
    }

    /**
     * Find activity for a specific division to allow updates (specifically for Knitting).
     */
    public function findForDivision(int $orderId, string $division): ?ProductionActivity
    {
        return ProductionActivity::where('marketing_order_id', $orderId)
            ->where('division_name', $division)
            ->first();
    }

    /**
     * Get production history for an operator.
     */
    public function getOperatorHistory(int $userId, ?string $role = null, ?string $search = null): LengthAwarePaginator
    {
        $divisions = [];
        if ($role === 'knitting') {
            $divisions = ['knitting'];
        } elseif (in_array($role, ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece'])) {
            $divisions = ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece'];
        } elseif ($role === 'pengujian') {
            $divisions = ['pengujian'];
        } elseif ($role === 'qe') {
            $divisions = ['qe'];
        }

        return ProductionActivity::with('marketingOrder')
            ->where(function ($query) use ($userId, $divisions) {
                $query->where('operator_id', $userId)
                      ->when(!empty($divisions), function ($q) use ($divisions) {
                          $q->orWhereIn('division_name', $divisions);
                      });
            })
            ->whereIn('id', function ($query) use ($userId, $divisions) {
                $query->selectRaw('MAX(id)')
                      ->from('production_activities')
                      ->where(function ($q2) use ($userId, $divisions) {
                          $q2->where('operator_id', $userId)
                             ->when(!empty($divisions), function ($q) use ($divisions) {
                                 $q->orWhereIn('division_name', $divisions);
                             });
                      })
                      ->whereNull('deleted_at')
                      ->groupBy('marketing_order_id');
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('marketingOrder', function ($sq) use ($search) {
                    $sq->where(function ($query) use ($search) {
                        $query->where('art_no', 'like', "%{$search}%")
                              ->orWhere('sap_no', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate(10);
    }

    /**
     * Get activities for admin monitoring with division filtering.
     */
    public function getMonitoringActivities(string $viewMode, ?string $search = null, ?string $start = null, ?string $end = null, string $unit = 'SEMUA'): LengthAwarePaginator
    {
        return ProductionActivity::with(['marketingOrder', 'operator'])
            ->when($viewMode === 'RAJUT', fn($q) => $q->where('division_name', 'knitting'))
            ->when($viewMode === 'WARNA', fn($q) => $q->whereIn('division_name', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece']))
            ->when($unit !== 'SEMUA', function ($q) use ($unit) {
                $q->whereHas('marketingOrder', fn($sq) => $sq->where('kelompok_kain', $unit));
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->whereHas('marketingOrder', function ($mo) use ($search) {
                        $mo->where('art_no', 'like', "%{$search}%")
                           ->orWhere('sap_no', 'like', "%{$search}%");
                    });
                });
            })
            ->when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('created_at', '<=', $end))
            ->latest()
            ->paginate(10);
    }
}
