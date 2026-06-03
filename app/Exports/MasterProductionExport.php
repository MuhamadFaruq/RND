<?php
namespace App\Exports;

use App\Models\MarketingOrder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\MarketingOrdersExport;
use App\Exports\ProductionExport;
use App\Exports\DyeingFinishingExport;
use App\Exports\KnittingProductionExport;

class MasterProductionExport implements WithMultipleSheets
{
    protected $start;
    protected $end;
    protected $unit;

    public function __construct($start, $end, $unit)
    {
        $this->start = $start;
        $this->end = $end;
        $this->unit = $unit;
    }

    public function sheets(): array
    {
        $sheets = [];
        $labelPeriode = date('d M Y', strtotime($this->start)) . ' - ' . date('d M Y', strtotime($this->end));

        // 1. SHEET MARKETING (Semua data marketing sesuai filter)
        $orders = MarketingOrder::whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->when($this->unit !== 'SEMUA', fn($q) => $q->where('kelompok_kain', $this->unit))
            ->latest()->get();
        
        $sheets[] = new MarketingOrdersExport($orders, $labelPeriode, '1. DIVISI MARKETING');

        // 2. SHEET KNITTING (Produksi Rajut - Custom Layout)
        $sheets[] = new KnittingProductionExport($this->start, $this->end, $this->unit, 'SEMUA', '2. DIVISI KNITTING (RAJUT)');

        // 3. SHEET DYEING & FINISHING (Produksi Warna Horizontal)
        $sheets[] = new DyeingFinishingExport($this->start, $this->end, $this->unit, '3. DIVISI DYEING & FINISH');

        return $sheets;
    }
}