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
        $format = strtolower($request->query('format', 'pdf')); 
        $mode = strtolower($request->query('mode', 'all'));
        $start = $request->query('start', date('Y-m-d'));
        $end = $request->query('end', date('Y-m-d'));
        $unit = $request->query('unit', 'SEMUA'); // INI PERBAIKANNYA

        // 2. Query Dasar
        $query = ProductionActivity::with(['marketingOrder', 'operator'])
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);

        // Filter Unit (DDT 2, DPF 3, dll)
        if ($unit !== 'SEMUA') {
            $query->whereHas('marketingOrder', fn($q) => $q->where('kelompok_kain', $unit));
        }

        // Filter Mode
        if ($mode === 'rajut') {
            $query->where('division_name', 'knitting');
        } elseif ($mode === 'warna') {
            $query->whereIn('division_name', ['dyeing', 'finishing']);
        }

        $activities = $query->latest()->get();

        // 3. LOGIKA PEMISAH FORMAT
        if ($format === 'excel') {
            // Kirim $unit ke dalam constructor Excel
            return Excel::download(
                new ProductionExport($start, $end, $mode, $unit), 
                "RND_REPORT_".strtoupper($mode)."_".date('Ymd').".xlsx"
            );
        }

        // 4. LOGIKA PDF (Jika format PDF)
        // (Sisa kode trends, hourlyActivity, dan pdfData Anda tetap sama di bawah ini)
        $trends = $activities->groupBy(fn($date) => Carbon::parse($date->created_at)->format('d M'))
            ->map(fn($group) => ['day' => $group->first()->created_at->format('d M'), 'total' => $group->sum('kg')])
            ->values();

        $hourlyActivity = array_fill(0, 24, 0);
        foreach ($activities as $act) {
            $hour = (int)$act->created_at->format('H');
            $hourlyActivity[$hour]++;
        }

        $divisionLeadTimes = ['knitting' => 2.5, 'dyeing' => 3.0, 'finishing' => 1.5];

        $pdfData = [
            'activities' => $activities,
            'selectedDivision' => ($mode === 'rajut') ? 'knitting' : (($mode === 'warna') ? 'dyeing' : 'all'),
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