<?php

namespace App\Repositories;

use App\Models\MarketingOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * OrderRepository
 * 
 * Repository ini adalah pusat akses data untuk MarketingOrder.
 * 
 * CATATAN UNTUK DEVELOPER:
 * Walaupun database memiliki kolom 'sap_no', operasional Duniatex saat ini sepenuhnya
 * menggunakan 'art_no' (Nomor Artikel) sebagai identitas utama di lapangan.
 * Oleh karena itu, semua fungsi pencarian di sini dirancang untuk mendukung keduanya
 * dengan prioritas pada Nomor Artikel untuk menjamin kenyamanan operator.
 */
class OrderRepository
{
    /**
     * Find an order by Article Number or SAP Number.
     */
    public function findByIdentifier(string $identifier): ?MarketingOrder
    {
        return MarketingOrder::where('art_no', $identifier)
            ->orWhere('sap_no', $identifier)
            ->first();
    }

    /**
     * Get WIP orders for a specific division/mode.
     */
    public function getWipOrders(string $viewMode, ?string $search = null): Collection
    {
        return MarketingOrder::with('processingBy')
            ->whereNotNull('processing_by')
            ->when($viewMode === 'RAJUT', function ($q) {
                $q->where('status', 'knitting');
            })
            ->when($viewMode === 'WARNA', function ($q) {
                $q->whereIn('status', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'finishing', 'stenter', 'tumbler', 'fleece']);
            })
            ->when($search, function ($q) use ($search) {
                $sanitizedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
                $q->where(function ($sq) use ($sanitizedSearch) {
                    $sq->where('art_no', 'like', "%{$sanitizedSearch}%")
                        ->orWhere('sap_no', 'like', "%{$sanitizedSearch}%");
                });
            })
            ->get();
    }

    /**
     * Get pending orders (queue) for a specific division.
     */
    public function getQueue(string $role, ?string $search = null): LengthAwarePaginator
    {
        $query = MarketingOrder::query();

        if ($role === 'knitting') {
            $query->where('status', 'knitting');
        } elseif ($role === 'dyeing') {
            $query->whereIn('status', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece']);
        } elseif ($role === 'pengujian') {
            $query->where('status', 'pengujian');
        } elseif ($role === 'qe') {
            $query->where('status', 'qe');
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->when($search, function ($q) use ($search) {
            $sanitizedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $q->where(function ($sq) use ($sanitizedSearch) {
                $sq->where('art_no', 'like', "%{$sanitizedSearch}%")
                    ->orWhere('sap_no', 'like', "%{$sanitizedSearch}%");
            });
        })
        ->latest()
        ->paginate(10);
    }

    /**
     * Update order status and processing state.
     */
    public function updateStatus(int $orderId, string $status, ?int $processingBy = null): bool
    {
        return MarketingOrder::where('id', $orderId)->update([
            'status' => $status,
            'processing_by' => $processingBy,
            'processing_at' => $processingBy ? now() : null,
        ]);
    }
}
