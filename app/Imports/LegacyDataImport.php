<?php

namespace App\Imports;

use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Models\ActivityLog;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Services\ProductionService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyDataImport
{
    protected $adminId;

    public function __construct($adminId)
    {
        $this->adminId = $adminId;
    }

    public function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        
        $marketingSheet = $spreadsheet->getSheetByName('1. DIVISI MARKETING');
        $knittingSheet = $spreadsheet->getSheetByName('2. DIVISI KNITTING (RAJUT)');
        $dyeingSheet = $spreadsheet->getSheetByName('3. DIVISI DYEING & FINISH');

        if (!$marketingSheet) {
            throw new \Exception("Sheet '1. DIVISI MARKETING' tidak ditemukan.");
        }

        // 1. Parse Knitting Sheet into Memory
        $knittingData = [];
        if ($knittingSheet) {
            $highestRow = $knittingSheet->getHighestRow();
            for ($row = 8; $row <= $highestRow; $row++) {
                $artNo = trim($knittingSheet->getCell('B' . $row)->getValue() ?? '');
                $sapNo = trim($knittingSheet->getCell('C' . $row)->getValue() ?? '');
                if (!$artNo && !$sapNo) continue;

                $rowData = [];
                $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('AJ');
                for ($colIdx = 1; $colIdx <= $highestColIndex; $colIdx++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $rowData[$col] = $knittingSheet->getCell($col . $row)->getValue();
                }

                if ($artNo) $knittingData[$artNo] = $rowData;
                if ($sapNo) $knittingData[$sapNo] = $rowData;
            }
        }

        // 2. Parse Dyeing & Finishing Sheet into Memory
        $dyeingData = [];
        if ($dyeingSheet) {
            $highestRow = $dyeingSheet->getHighestRow();
            for ($row = 8; $row <= $highestRow; $row++) {
                $artNo = trim($dyeingSheet->getCell('B' . $row)->getValue() ?? '');
                $sapNo = trim($dyeingSheet->getCell('C' . $row)->getValue() ?? '');
                if (!$artNo && !$sapNo) continue;

                $rowData = [];
                // Dyeing & finishing sheet can go up to EF
                $highestCol = $dyeingSheet->getHighestColumn();
                $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
                for ($colIdx = 1; $colIdx <= $highestColIndex; $colIdx++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $rowData[$col] = $dyeingSheet->getCell($col . $row)->getValue();
                }

                if ($artNo) $dyeingData[$artNo] = $rowData;
                if ($sapNo) $dyeingData[$sapNo] = $rowData;
            }
        }

        $importedCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($marketingSheet, $knittingData, $dyeingData, &$importedCount, &$updatedCount) {
            $highestRow = $marketingSheet->getHighestRow();

            for ($row = 9; $row <= $highestRow; $row++) {
                $sapNo = trim($marketingSheet->getCell('B' . $row)->getValue() ?? '');
                $artNo = trim($marketingSheet->getCell('C' . $row)->getValue() ?? '');
                
                // Hentikan jika baris total atau kosong
                if (!$artNo && !$sapNo) continue;
                if (Str::contains($sapNo, 'TOTAL') || Str::contains($artNo, 'TOTAL')) continue;

                $tanggalVal = $marketingSheet->getCell('D' . $row)->getValue();
                $tanggal = $this->parseExcelDate($tanggalVal) ?: now();

                $belahBulat = strtolower(trim($marketingSheet->getCell('M' . $row)->getValue() ?? ''));
                $material = strtolower(trim($marketingSheet->getCell('I' . $row)->getValue() ?? ''));
                $greige = strtolower(trim($marketingSheet->getCell('H' . $row)->getValue() ?? ''));
                $treatment = strtolower(trim($marketingSheet->getCell('Q' . $row)->getValue() ?? ''));
                $keterangan = strtolower(trim($marketingSheet->getCell('T' . $row)->getValue() ?? ''));

                $reqStenter = (str_contains($belahBulat, 'belah'));
                $reqCompactor = (str_contains($belahBulat, 'bulat') && (str_contains($material, 'cotton') || str_contains($material, 'combed') || str_contains($material, 'cm') || str_contains($greige, 'cm') || str_contains($greige, 'cotton') || str_contains($greige, 'combed')));
                $reqHeatSetting = (str_contains($belahBulat, 'bulat') && (str_contains($material, 'poly') || str_contains($material, 'pe') || str_contains($material, 'adidas') || str_contains($greige, 'poly') || str_contains($greige, 'pe')));
                $reqTumbler = (str_contains($treatment, 'tumbler') || str_contains($keterangan, 'tumbler') || str_contains($treatment, 'shaker') || str_contains($keterangan, 'shaker'));
                $reqFleece = (str_contains($treatment, 'fleece') || str_contains($keterangan, 'fleece') || str_contains($treatment, 'raising') || str_contains($keterangan, 'raising') || str_contains($treatment, 'bulu') || str_contains($keterangan, 'bulu') || str_contains($treatment, 'enzim'));

                $orderData = [
                    'sap_no'             => $sapNo ?: null,
                    'art_no'             => $artNo,
                    'tanggal'            => $tanggal,
                    'pelanggan'          => trim($marketingSheet->getCell('E' . $row)->getValue() ?? '-'),
                    'mkt'                => trim($marketingSheet->getCell('F' . $row)->getValue() ?? '-'),
                    'keperluan'          => trim($marketingSheet->getCell('G' . $row)->getValue() ?? '-'),
                    'konstruksi_greige'  => trim($marketingSheet->getCell('H' . $row)->getValue() ?? '-'),
                    'material'           => trim($marketingSheet->getCell('I' . $row)->getValue() ?? '-'),
                    'benang'             => trim($marketingSheet->getCell('J' . $row)->getValue() ?? '-'),
                    'kelompok_kain'      => trim($marketingSheet->getCell('K' . $row)->getValue() ?? '-'),
                    'target_lebar'       => trim($marketingSheet->getCell('L' . $row)->getValue() ?? '-'),
                    'belah_bulat'        => trim($marketingSheet->getCell('M' . $row)->getValue() ?? '-'),
                    'target_gramasi'     => trim($marketingSheet->getCell('N' . $row)->getValue() ?? '-'),
                    'warna'              => trim($marketingSheet->getCell('O' . $row)->getValue() ?? '-'),
                    'handfeel'           => trim($marketingSheet->getCell('P' . $row)->getValue() ?? '-'),
                    'treatment_khusus'   => trim($marketingSheet->getCell('Q' . $row)->getValue() ?? '-'),
                    'roll_target'        => intval($marketingSheet->getCell('R' . $row)->getValue() ?? 0),
                    'kg_target'          => floatval($marketingSheet->getCell('S' . $row)->getValue() ?? 0),
                    'keterangan_artikel' => trim($marketingSheet->getCell('T' . $row)->getValue() ?? '-'),
                    'status'             => 'knitting', // Default status awal
                    'req_stenter'        => $reqStenter,
                    'req_compactor'      => $reqCompactor,
                    'req_heat_setting'   => $reqHeatSetting,
                    'req_tumbler'        => $reqTumbler,
                    'req_fleece'         => $reqFleece,
                    'req_pengujian'      => false,
                    'req_qe'             => false,
                ];

                // Cek apakah order sudah ada
                $order = MarketingOrder::where('art_no', $artNo)
                    ->orWhere(function ($q) use ($sapNo) {
                        if ($sapNo) $q->where('sap_no', $sapNo);
                    })->first();

                if ($order) {
                    $order->update($orderData);
                    $updatedCount++;
                } else {
                    $order = MarketingOrder::create($orderData);
                    $importedCount++;
                }

                // Hapus aktivitas lama agar sinkron ulang secara bersih
                ProductionActivity::where('marketing_order_id', $order->id)->delete();

                $lastCompletedDivision = null;

                // 3. Process Knitting Activity
                $knitRow = $knittingData[$artNo] ?? ($sapNo ? ($knittingData[$sapNo] ?? null) : null);
                if ($knitRow) {
                    $kOperator = trim($knitRow['H'] ?? 'Legacy Operator');
                    $kDate = $this->parseExcelDate($knitRow['I']) ?: $tanggal;
                    $kKg = floatval($knitRow['Q'] ?? $order->kg_target);
                    $kRoll = intval($knitRow['R'] ?? $order->roll_target);

                    $kTech = [
                        'no_mesin'         => trim($knitRow['J'] ?? '-'),
                        'type_mesin'       => trim($knitRow['K'] ?? '-'),
                        'gauge_inch'       => trim($knitRow['L'] ?? '-'),
                        'jml_feeder'       => trim($knitRow['M'] ?? '-'),
                        'jml_jarum'        => trim($knitRow['N'] ?? '-'),
                        'lebar'            => trim($knitRow['O'] ?? '-'),
                        'gramasi'          => trim($knitRow['P'] ?? '-'),
                        'benang_1'         => trim($knitRow['S'] ?? '-'),
                        'benang_1_lot'     => trim($knitRow['T'] ?? '-'),
                        'benang_1_percent' => trim($knitRow['U'] ?? '-'),
                        'yl_1'             => trim($knitRow['V'] ?? '-'),
                        'benang_2'         => trim($knitRow['W'] ?? '-'),
                        'benang_2_lot'     => trim($knitRow['X'] ?? '-'),
                        'benang_2_percent' => trim($knitRow['Y'] ?? '-'),
                        'yl_2'             => trim($knitRow['Z'] ?? '-'),
                        'benang_3'         => trim($knitRow['AA'] ?? '-'),
                        'benang_3_lot'     => trim($knitRow['AB'] ?? '-'),
                        'benang_3_percent' => trim($knitRow['AC'] ?? '-'),
                        'yl_3'             => trim($knitRow['AD'] ?? '-'),
                        'benang_4'         => trim($knitRow['AE'] ?? '-'),
                        'benang_4_lot'     => trim($knitRow['AF'] ?? '-'),
                        'benang_4_percent' => trim($knitRow['AG'] ?? '-'),
                        'yl_4'             => trim($knitRow['AH'] ?? '-'),
                        'note'             => trim($knitRow['AI'] ?? '-'),
                        'produksi_per_day' => trim($knitRow['AJ'] ?? '-'),
                        'operator_manual_name' => $kOperator,
                    ];

                    ProductionActivity::create([
                        'marketing_order_id' => $order->id,
                        'operator_id'        => $this->adminId,
                        'operator_name'      => $kOperator,
                        'division_name'      => 'knitting',
                        'kg'                 => $kKg,
                        'roll'               => $kRoll,
                        'created_at'         => $kDate,
                        'updated_at'         => $kDate,
                        'technical_data'     => $kTech,
                    ]);

                    $lastCompletedDivision = 'knitting';
                }

                // 4. Process Dyeing & Finishing activities from Sheet 3
                $dfRow = $dyeingData[$artNo] ?? ($sapNo ? ($dyeingData[$sapNo] ?? null) : null);
                if ($dfRow) {
                    // Dyeing block (K-S)
                    $dOperator = trim($dfRow['L'] ?? '');
                    if ($dOperator && $dOperator !== '-') {
                        $dDate = $this->parseExcelDate($dfRow['M']) ?: $tanggal;
                        $dTech = [
                            'cek_greige'  => trim($dfRow['K'] ?? '-'),
                            'operator'    => $dOperator,
                            'tanggal'     => $dfRow['M'],
                            'jenis_mesin' => trim($dfRow['N'] ?? '-'),
                            'no_mesin'    => trim($dfRow['O'] ?? '-'),
                            'warna'       => trim($dfRow['P'] ?? '-'),
                            'kode_warna'  => trim($dfRow['Q'] ?? '-'),
                            'dye_system'  => trim($dfRow['R'] ?? '-'),
                            'treatment'   => trim($dfRow['S'] ?? '-'),
                            'operator_manual_name' => $dOperator,
                        ];

                        ProductionActivity::create([
                            'marketing_order_id' => $order->id,
                            'operator_id'        => $this->adminId,
                            'operator_name'      => $dOperator,
                            'division_name'      => 'dyeing',
                            'kg'                 => $order->kg_target,
                            'roll'               => $order->roll_target,
                            'created_at'         => $dDate,
                            'updated_at'         => $dDate,
                            'technical_data'     => $dTech,
                        ]);

                        $lastCompletedDivision = 'dyeing';
                    }

                    // Relax Dryer block (T-AD)
                    $rOperator = trim($dfRow['T'] ?? '');
                    if ($rOperator && $rOperator !== '-') {
                        $rDate = $this->parseExcelDate($dfRow['U']) ?: $tanggal;
                        $rTech = [
                            'operator'  => $rOperator,
                            'tanggal'   => $dfRow['U'],
                            'chemical'  => trim($dfRow['V'] ?? '-'),
                            'handfeel'  => trim($dfRow['W'] ?? '-'),
                            'no_mesin'  => trim($dfRow['X'] ?? '-'),
                            'overfeed'  => trim($dfRow['Y'] ?? '-'),
                            'suhu'      => trim($dfRow['Z'] ?? '-'),
                            'speed'     => trim($dfRow['AA'] ?? '-'),
                            'lebar'     => trim($dfRow['AB'] ?? '-'),
                            'gramasi'   => trim($dfRow['AC'] ?? '-'),
                            'shrinkage' => trim($dfRow['AD'] ?? '-'),
                            'operator_manual_name' => $rOperator,
                        ];

                        ProductionActivity::create([
                            'marketing_order_id' => $order->id,
                            'operator_id'        => $this->adminId,
                            'operator_name'      => $rOperator,
                            'division_name'      => 'relax-dryer',
                            'kg'                 => $order->kg_target,
                            'roll'               => $order->roll_target,
                            'created_at'         => $rDate,
                            'updated_at'         => $rDate,
                            'technical_data'     => $rTech,
                        ]);

                        $lastCompletedDivision = 'relax-dryer';
                    }

                    // Compactor block (AE-AQ)
                    $cOperator = trim($dfRow['AE'] ?? '');
                    if ($cOperator && $cOperator !== '-') {
                        $order->req_compactor = true;
                        $cDate = $this->parseExcelDate($dfRow['AF']) ?: $tanggal;
                        $cTech = [
                            'operator'       => $cOperator,
                            'tanggal'        => $dfRow['AF'],
                            'no_mesin'       => trim($dfRow['AG'] ?? '-'),
                            'rangka'         => trim($dfRow['AH'] ?? '-'),
                            'suhu'           => trim($dfRow['AI'] ?? '-'),
                            'speed'          => trim($dfRow['AJ'] ?? '-'),
                            'overfeed'       => trim($dfRow['AK'] ?? '-'),
                            'felt'           => trim($dfRow['AL'] ?? '-'),
                            'delivery_speed' => trim($dfRow['AM'] ?? '-'),
                            'folding_speed'  => trim($dfRow['AN'] ?? '-'),
                            'lebar'          => trim($dfRow['AO'] ?? '-'),
                            'gramasi'        => trim($dfRow['AP'] ?? '-'),
                            'shrinkage'      => trim($dfRow['AQ'] ?? '-'),
                            'operator_manual_name' => $cOperator,
                        ];

                        ProductionActivity::create([
                            'marketing_order_id' => $order->id,
                            'operator_id'        => $this->adminId,
                            'operator_name'      => $cOperator,
                            'division_name'      => 'compactor',
                            'kg'                 => $order->kg_target,
                            'roll'               => $order->roll_target,
                            'created_at'         => $cDate,
                            'updated_at'         => $cDate,
                            'technical_data'     => $cTech,
                        ]);

                        $lastCompletedDivision = 'compactor';
                    }

                    // Heat Setting block (AR-BB)
                    $hOperator = trim($dfRow['AR'] ?? '');
                    if ($hOperator && $hOperator !== '-') {
                        $order->req_heat_setting = true;
                        $hDate = $this->parseExcelDate($dfRow['AS']) ?: $tanggal;
                        $hTech = [
                            'operator'       => $hOperator,
                            'tanggal'        => $dfRow['AS'],
                            'no_mesin'       => trim($dfRow['AT'] ?? '-'),
                            'rangka'         => trim($dfRow['AU'] ?? '-'),
                            'suhu'           => trim($dfRow['AV'] ?? '-'),
                            'speed'          => trim($dfRow['AW'] ?? '-'),
                            'overfeed'       => trim($dfRow['AX'] ?? '-'),
                            'delivery_speed' => trim($dfRow['AY'] ?? '-'),
                            'folding_speed'  => trim($dfRow['AZ'] ?? '-'),
                            'lebar'          => trim($dfRow['BA'] ?? '-'),
                            'gramasi'        => trim($dfRow['BB'] ?? '-'),
                            'operator_manual_name' => $hOperator,
                        ];

                        ProductionActivity::create([
                            'marketing_order_id' => $order->id,
                            'operator_id'        => $this->adminId,
                            'operator_name'      => $hOperator,
                            'division_name'      => 'heat-setting',
                            'kg'                 => $order->kg_target,
                            'roll'               => $order->roll_target,
                            'created_at'         => $hDate,
                            'updated_at'         => $hDate,
                            'technical_data'     => $hTech,
                        ]);

                        $lastCompletedDivision = 'heat-setting';
                    }

                    // Stenter block (BC-CV)
                    $sOperator = trim($dfRow['BC'] ?? '');
                    if ($sOperator && $sOperator !== '-') {
                        $order->req_stenter = true;
                        $sDate = $this->parseExcelDate($dfRow['BD'] ?: $dfRow['BE'] ?: $dfRow['BF']) ?: $tanggal;
                        
                        $getS = function($colIdxStr, $dfRow) {
                            return trim($dfRow[$colIdxStr] ?? '-');
                        };

                        $sTech = [
                            'operator' => $sOperator,
                            'no_mesin' => '-',
                            'preset'   => [
                                'tanggal'    => $getS('BD', $dfRow),
                                'temperature'=> $getS('BG', $dfRow),
                                'speed'      => $getS('BJ', $dfRow),
                                'padder'     => $getS('BM', $dfRow),
                                'rangka'     => $getS('BP', $dfRow),
                                'overfeed_a' => $getS('BS', $dfRow),
                                'overfeed_b' => $getS('BV', $dfRow),
                                'fan'        => $getS('BY', $dfRow),
                                'delivery'   => $getS('CB', $dfRow),
                                'folding'    => $getS('CE', $dfRow),
                                'chem1'      => $getS('CH', $dfRow),
                                'chem2'      => $getS('CK', $dfRow),
                                'lebar'      => $getS('CN', $dfRow),
                                'gramasi'    => $getS('CQ', $dfRow),
                                'shrinkage'  => $getS('CT', $dfRow),
                            ],
                            'drying'   => [
                                'tanggal'    => $getS('BE', $dfRow),
                                'temperature'=> $getS('BH', $dfRow),
                                'speed'      => $getS('BK', $dfRow),
                                'padder'     => $getS('BN', $dfRow),
                                'rangka'     => $getS('BQ', $dfRow),
                                'overfeed_a' => $getS('BT', $dfRow),
                                'overfeed_b' => $getS('BW', $dfRow),
                                'fan'        => $getS('BZ', $dfRow),
                                'delivery'   => $getS('CC', $dfRow),
                                'folding'    => $getS('CF', $dfRow),
                                'chem1'      => $getS('CI', $dfRow),
                                'chem2'      => $getS('CL', $dfRow),
                                'lebar'      => $getS('CO', $dfRow),
                                'gramasi'    => $getS('CR', $dfRow),
                                'shrinkage'  => $getS('CU', $dfRow),
                            ],
                            'finishing'=> [
                                'tanggal'    => $getS('BF', $dfRow),
                                'temperature'=> $getS('BI', $dfRow),
                                'speed'      => $getS('BL', $dfRow),
                                'padder'     => $getS('BO', $dfRow),
                                'rangka'     => $getS('BR', $dfRow),
                                'overfeed_a' => $getS('BU', $dfRow),
                                'overfeed_b' => $getS('BX', $dfRow),
                                'fan'        => $getS('CA', $dfRow),
                                'delivery'   => $getS('CD', $dfRow),
                                'folding'    => $getS('CG', $dfRow),
                                'chem1'      => $getS('CJ', $dfRow),
                                'chem2'      => $getS('CM', $dfRow),
                                'lebar'      => $getS('CP', $dfRow),
                                'gramasi'    => $getS('CS', $dfRow),
                                'shrinkage'  => $getS('CV', $dfRow),
                            ],
                            'operator_manual_name' => $sOperator,
                        ];

                        ProductionActivity::create([
                            'marketing_order_id' => $order->id,
                            'operator_id'        => $this->adminId,
                            'operator_name'      => $sOperator,
                            'division_name'      => 'stenter',
                            'kg'                 => $order->kg_target,
                            'roll'               => $order->roll_target,
                            'created_at'         => $sDate,
                            'updated_at'         => $sDate,
                            'technical_data'     => $sTech,
                        ]);

                        $lastCompletedDivision = 'stenter';
                    }

                    // Tumbler block (CW-DE)
                    $tOperator = trim($dfRow['CW'] ?? '');
                    if ($tOperator && $tOperator !== '-') {
                        $order->req_tumbler = true;
                        $tDate = $this->parseExcelDate($dfRow['CX']) ?: $tanggal;
                        $tTech = [
                            'operator'     => $tOperator,
                            'tanggal'      => $dfRow['CX'],
                            'no_mesin'     => '-',
                            'suhu'         => trim($dfRow['CY'] ?? '-'),
                            'steam_inject' => trim($dfRow['CZ'] ?? '-'),
                            'hotwind'      => trim($dfRow['DA'] ?? '-'),
                            'coldwind'     => trim($dfRow['DB'] ?? '-'),
                            'lebar'        => trim($dfRow['DC'] ?? '-'),
                            'gramasi'      => trim($dfRow['DD'] ?? '-'),
                            'shrinkage'    => trim($dfRow['DE'] ?? '-'),
                            'operator_manual_name' => $tOperator,
                        ];

                        ProductionActivity::create([
                            'marketing_order_id' => $order->id,
                            'operator_id'        => $this->adminId,
                            'operator_name'      => $tOperator,
                            'division_name'      => 'tumbler',
                            'kg'                 => $order->kg_target,
                            'roll'               => $order->roll_target,
                            'created_at'         => $tDate,
                            'updated_at'         => $tDate,
                            'technical_data'     => $tTech,
                        ]);

                        $lastCompletedDivision = 'tumbler';
                    }

                    // Fleece block (DF-EF)
                    $fRaisingOperator = trim($dfRow['DF'] ?? '');
                    $fBrushingOperator = trim($dfRow['DP'] ?? '');
                    $fShearingOperator = trim($dfRow['DZ'] ?? '');
                    if (($fRaisingOperator && $fRaisingOperator !== '-') ||
                        ($fBrushingOperator && $fBrushingOperator !== '-') ||
                        ($fShearingOperator && $fShearingOperator !== '-')) {
                        
                        $order->req_fleece = true;
                        $fDate = $this->parseExcelDate($dfRow['DG'] ?: $dfRow['DQ'] ?: $dfRow['EA']) ?: $tanggal;
                        
                        $fTech = [
                            'no_mesin' => '-',
                            'raising'  => [
                                'operator'   => $fRaisingOperator ?: '-',
                                'tanggal'    => trim($dfRow['DG'] ?? '-'),
                                'std_bulu'   => trim($dfRow['DH'] ?? '-'),
                                'speed'      => trim($dfRow['DI'] ?? '-'),
                                'cloth_out'  => trim($dfRow['DJ'] ?? '-'),
                                'bend_pin'   => trim($dfRow['DK'] ?? '-'),
                                'straight_pin'=> trim($dfRow['DL'] ?? '-'),
                                'rpm_drum'   => trim($dfRow['DM'] ?? '-'),
                                'lebar_gsm'  => trim($dfRow['DN'] ?? '-'),
                                'drum_brush' => trim($dfRow['DO'] ?? '-'),
                            ],
                            'brushing' => [
                                'operator'   => $fBrushingOperator ?: '-',
                                'tanggal'    => trim($dfRow['DQ'] ?? '-'),
                                'std_bulu'   => trim($dfRow['DR'] ?? '-'),
                                'cloth_speed'=> trim($dfRow['DS'] ?? '-'),
                                'cloth_out'  => trim($dfRow['DT'] ?? '-'),
                                'left_brush' => trim($dfRow['DU'] ?? '-'),
                                'right_brush'=> trim($dfRow['DV'] ?? '-'),
                                'rpm_drum'   => trim($dfRow['DW'] ?? '-'),
                                'tension'    => trim($dfRow['DX'] ?? '-'),
                                'lebar_gsm'  => trim($dfRow['DY'] ?? '-'),
                            ],
                            'shearing' => [
                                'operator'   => $fShearingOperator ?: '-',
                                'tanggal'    => trim($dfRow['EA'] ?? '-'),
                                'speed'      => trim($dfRow['EB'] ?? '-'),
                                'cloth_out'  => trim($dfRow['EC'] ?? '-'),
                                'expending'  => trim($dfRow['ED'] ?? '-'),
                                'shear'      => trim($dfRow['EE'] ?? '-'),
                                'lebar_gsm'  => trim($dfRow['EF'] ?? '-'),
                            ],
                            'operator_manual_name' => $fRaisingOperator ?: ($fBrushingOperator ?: ($fShearingOperator ?: 'Fleece Op')),
                        ];

                        ProductionActivity::create([
                            'marketing_order_id' => $order->id,
                            'operator_id'        => $this->adminId,
                            'operator_name'      => $fTech['operator_manual_name'],
                            'division_name'      => 'fleece',
                            'kg'                 => $order->kg_target,
                            'roll'               => $order->roll_target,
                            'created_at'         => $fDate,
                            'updated_at'         => $fDate,
                            'technical_data'     => $fTech,
                        ]);

                        $lastCompletedDivision = 'fleece';
                    }
                }

                // If not in knitting sheet, default status to knitting.
                // Otherwise, calculate status transition dynamically.
                $order->save();

                if ($lastCompletedDivision) {
                    $productionService = app(ProductionService::class);
                    $nextStatus = $productionService->getNextRequiredStatus($order, $lastCompletedDivision);
                    if ($nextStatus) {
                        $order->status = $nextStatus;
                        $order->save();
                    } else {
                        // If there is no next status required, then it's finished!
                        $order->status = 'finished';
                        $order->save();
                    }
                }
            }
        });

        // Log to audit trail
        ActivityLog::create([
            'user_id'     => $this->adminId,
            'action'      => 'IMPORT',
            'model'       => 'LEGACY_DATA',
            'description' => "Super Admin mengimpor data legacy dari Excel. Diimpor baru: {$importedCount}, Diperbarui: {$updatedCount}",
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent()
        ]);

        return [
            'imported' => $importedCount,
            'updated' => $updatedCount
        ];
    }

    private function parseExcelDate($value)
    {
        if (empty($value) || $value === '-') return null;
        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        // Coba parsing manual
        try {
            return Carbon::parse(str_replace('/', '-', $value));
        } catch (\Exception $e) {
            return null;
        }
    }
}
