<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel; // Pastikan library ini ada
use App\Exports\ProductionExport;
use Carbon\Carbon;

class ProductionReportController extends Controller
{
    public function export(Request $request)
    {
        // 1. TANGKAP SEMUA PARAMETER DARI REQUEST
        $format   = strtolower($request->query('format', 'pdf')); 
        $mode     = strtolower($request->query('mode', 'all'));
        $unit     = $request->query('unit', 'SEMUA');
        $operator = $request->query('operator', 'SEMUA');

        // Logika Rentang Tanggal: Jika tidak di-set, ambil semua data dari awal tahun 2026
        // Jika hanya salah satu di-set, gunakan tanggal hari ini sebagai pasangan.
        $start = $request->query('start');
        $end   = $request->query('end');

        if (!$start && !$end) {
            $start = '2026-01-01'; // Default awal sistem
            $end   = date('Y-m-d');
        } else {
            $start = $start ?: date('Y-m-d');
            $end   = $end ?: date('Y-m-d');
        }

        // 2. Query Dasar untuk PDF/Preview
        $query = ProductionActivity::with(['marketingOrder', 'operator'])
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);

        // Filter Unit
        if ($unit !== 'SEMUA') {
            $query->whereHas('marketingOrder', fn($q) => $q->where('kelompok_kain', $unit));
        }

        // Filter Operator
        if ($operator !== 'SEMUA') {
            $query->where('operator_id', $operator);
        }

        // Filter Mode Divisi
        if ($mode === 'rajut') {
            $query->where('division_name', 'knitting');
        } elseif ($mode === 'warna') {
            $query->whereIn('division_name', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece']);
        }

        $activities = $query->latest()->get();

        // 3. LOGIKA PEMISAH FORMAT
        if ($format === 'excel') {
            if ($mode === 'master') {
                return Excel::download(
                    new \App\Exports\MasterProductionExport($start, $end, $unit), 
                    "RND_MASTER_PIPELINE_" . date('Ymd_His') . ".xlsx"
                );
            }
            if ($mode === 'marketing') {
                $orders = \App\Models\MarketingOrder::whereDate('created_at', '>=', $start)
                    ->whereDate('created_at', '<=', $end)
                    ->when($unit !== 'SEMUA', fn($q) => $q->where('kelompok_kain', $unit))
                    ->latest()->get();
                $labelPeriode = date('d M Y', strtotime($start)) . ' - ' . date('d M Y', strtotime($end));
                return Excel::download(
                    new \App\Exports\MarketingOrdersExport($orders, $labelPeriode),
                    "RND_MARKETING_ORDERS_" . date('Ymd_His') . ".xlsx"
                );
            }
            
            if ($mode === 'rajut') {
                return Excel::download(
                    new \App\Exports\KnittingProductionExport($start, $end, $unit, $operator, 'DIVISI KNITTING (RAJUT)'),
                    "RND_KNITTING_" . date('Ymd_His') . ".xlsx"
                );
            }
            
            if ($mode === 'warna') {
                return Excel::download(
                    new \App\Exports\DyeingFinishingExport($start, $end, $unit, 'DIVISI DYEING & FINISH'),
                    "RND_DYEING_FINISHING_" . date('Ymd_His') . ".xlsx"
                );
            }
            
            return Excel::download(
                new ProductionExport($start, $end, $mode, $unit, $operator), 
                "RND_REPORT_".strtoupper($mode)."_".date('Ymd_His').".xlsx"
            );
        }

        // 4. LOGIKA PDF (Jika format PDF)
        
        // Sesuaikan data jika mode marketing
        if ($mode === 'marketing') {
            $activities = \App\Models\MarketingOrder::whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->when($unit !== 'SEMUA', fn($q) => $q->where('kelompok_kain', $unit))
                ->latest()->get();
                
            $selectedDivision = 'marketing';
            
            // Dummy trend untuk marketing (berdasarkan jumlah pesanan)
            $trends = $activities->groupBy(fn($date) => Carbon::parse($date->created_at)->format('d M'))
                ->map(fn($group) => ['day' => $group->first()->created_at->format('d M'), 'total' => $group->sum('kg_target')])
                ->values();
        } else {
            $selectedDivision = ($mode === 'rajut') ? 'knitting' : (($mode === 'warna') ? 'dyeing' : 'all');
            
            $trends = $activities->groupBy(fn($date) => Carbon::parse($date->created_at)->format('d M'))
                ->map(fn($group) => ['day' => $group->first()->created_at->format('d M'), 'total' => $group->sum('kg')])
                ->values();
        }

        $hourlyActivity = array_fill(0, 24, 0);
        foreach ($activities as $act) {
            $hour = (int)$act->created_at->format('H');
            $hourlyActivity[$hour]++;
        }

        $divisionLeadTimes = ['knitting' => 2.5, 'dyeing' => 3.0, 'finishing' => 1.5];

        $pdfData = [
            'activities' => $activities,
            'selectedDivision' => $selectedDivision,
            'period' => $start . ' s/d ' . $end,
            'generated_at' => now()->format('d/m/Y H:i'),
            'admin_name' => auth()->user()->name ?? 'Super Admin',
            'trends' => $trends,
            'hourlyActivity' => $hourlyActivity,
            'divisionLeadTimes' => $divisionLeadTimes,
            'unit' => $unit // Pastikan variabel $unit dikirim ke PDF jika dibutuhkan di Blade
        ];

        $pdf = Pdf::loadView('pdf.production-report', $pdfData)->setPaper('a4', 'portrait');
        return $pdf->download("RND_REPORT_".strtoupper($mode)."_".date('Ymd').".pdf");
    }
}