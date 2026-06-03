<?php

namespace App\Services;

use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Repositories\OrderRepository;
use App\Repositories\ActivityRepository;
use App\Models\ActivityLog;

/**
 * ProductionService
 * 
 * Engine pusat untuk logika produksi RND Duniatex.
 * Service ini menangani siklus hidup order (Start, Takeover, Save, Advance Status).
 * 
 * PRINSIP KERJA:
 * Mengutamakan 'No Artikel' sebagai identitas operasional sesuai kebutuhan lapangan.
 * Berinteraksi dengan Repository untuk abstraksi database agar kode tetap bersih
 * meskipun nama kolom database (sap_no) belum berubah.
 */
class ProductionService
{
    protected $orderRepo;
    protected $activityRepo;

    public function __construct(OrderRepository $orderRepo, ActivityRepository $activityRepo)
    {
        $this->orderRepo = $orderRepo;
        $this->activityRepo = $activityRepo;
    }
    /**
     * Umum: Menyimpan aktivitas produksi dan memajukan status order secara dinamis
     */
    private function logActivityAndAdvance(
        int $orderId,
        int $userId,
        string $divisionName,
        array $technicalData = [],
        array $extraData = []
    ): void {
        DB::transaction(function () use ($orderId, $userId, $divisionName, $technicalData, $extraData) {
            
            $order = MarketingOrder::findOrFail($orderId);
            $nextStatus = $this->getNextRequiredStatus($order, $divisionName);

            $kg = $extraData['kg'] ?? ($technicalData['kg'] ?? null);
            $roll = $extraData['roll'] ?? ($technicalData['roll'] ?? null);

            if ($kg === null || $roll === null) {
                $prevLog = ProductionActivity::where('marketing_order_id', $orderId)->latest('id')->first();
                if ($prevLog) {
                    $kg = $kg ?? $prevLog->kg;
                    $roll = $roll ?? $prevLog->roll;
                }
            }

            $activityData = [
                'marketing_order_id' => $orderId,
                'operator_id'        => $userId,
                'operator_name'      => $technicalData['nama_input'] ?? ($technicalData['operator_manual_name'] ?? null),
                'division_name'      => $divisionName,
                'kg'                 => $kg,
                'roll'               => $roll,
                'technical_data'     => array_merge($technicalData, $extraData),
            ];

            if ($divisionName === 'knitting') {
                $existing = $this->activityRepo->findForDivision($orderId, 'knitting');
                if ($existing) {
                    $existing->update($activityData);
                } else {
                    $this->activityRepo->log($activityData);
                }
            } else {
                $this->activityRepo->log($activityData);
            }

            if ($nextStatus) {
                $this->orderRepo->updateStatus($orderId, $nextStatus);
            }

            // ADD AUDIT LOG
            ActivityLog::create([
                'user_id'     => $userId,
                'action'      => 'CREATE_PRODUCTION_LOG',
                'division'    => $divisionName,
                'art_no'      => $order->art_no,
                'sap_no'      => $order->sap_no,
                'description' => "Input data produksi divisi {$divisionName}",
            ]);
        });
    }

    /**
     * Start a job by an operator.
     */
    public function startJob(int $orderId, int $userId): void
    {
        $order = MarketingOrder::findOrFail($orderId);
        $this->orderRepo->updateStatus($orderId, $order->status, $userId);

        ActivityLog::create([
            'user_id'     => $userId,
            'action'      => 'START_PROCESS',
            'division'    => auth()->user()->role ?? 'OPERATOR',
            'art_no'      => $order->art_no,
            'sap_no'      => $order->sap_no,
            'description' => "Memulai pengerjaan Artikel: {$order->art_no}",
        ]);
    }

    /**
     * Take over a job from another operator.
     */
    public function takeOverJob(int $orderId, int $userId, string $role): void
    {
        $order = MarketingOrder::findOrFail($orderId);
        
        ActivityLog::create([
            'user_id'     => $userId,
            'action'      => 'TAKEOVER_PROCESS',
            'division'    => $role,
            'art_no'      => $order->art_no,
            'sap_no'      => $order->sap_no,
            'description' => "Mengambil alih pengerjaan Artikel: {$order->art_no} dari operator sebelumnya.",
        ]);

        $this->orderRepo->updateStatus($orderId, $order->status, $userId);
    }

    /**
     * Cancel a job and return to queue.
     */
    public function cancelJob(int $orderId, int $userId): void
    {
        $order = MarketingOrder::find($orderId);
        if ($order && $order->processing_by === $userId) {
            $this->orderRepo->updateStatus($orderId, $order->status, null);
        }
    }

    public function processKnitting(int $orderId, int $userId, float $kg, int $roll, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'knitting', 
            $technicalData,
            ['kg' => $kg, 'roll' => $roll, 'status' => 'completed']
        );
    }

    public function processDyeing(int $orderId, int $userId, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'dyeing', 
            $technicalData
        );
    }

    public function processRelaxDryer(int $orderId, int $userId, float $kg, int $roll, int $shift, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'relax-dryer', 
            $technicalData,
            ['kg' => $kg, 'roll' => $roll, 'shift' => $shift]
        );
    }

    public function processFinishing(int $orderId, int $userId, string $type, string $noMesin, int $shift, array $technicalData): void
    {
        // $type bisa berupa 'compactor' atau 'heat-setting'
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            $type, // Gunakan type spesifik agar getNextRequiredStatus tepat
            $technicalData,
            ['type' => 'finishing', 'no_mesin' => $noMesin, 'shift' => $shift]
        );
    }

    public function processStenter(int $orderId, int $userId, int $shift, array $technicalData, bool $isLastFinishingStep): void
    {
        // Jika finishing belum selesai di stenter, status tetap 'stenter'
        // Jika sudah selesai, biarkan logActivityAndAdvance mencari status berikutnya setelah stenter
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'stenter', 
            $technicalData,
            ['type' => 'stenter', 'shift' => $shift]
        );

        // Jika bukan langkah terakhir, kembalikan status ke stenter
        if (!$isLastFinishingStep) {
            $this->orderRepo->updateStatus($orderId, 'stenter');
        }
    }

    public function processTumbler(int $orderId, int $userId, int $shift, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'tumbler', 
            $technicalData,
            ['type' => 'tumbler', 'shift' => $shift]
        );
    }

    public function processFleece(int $orderId, int $userId, int $shift, array $technicalData, bool $isLastShearingStep): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'fleece', 
            $technicalData,
            ['type' => 'fleece', 'shift' => $shift]
        );

        if (!$isLastShearingStep) {
            $this->orderRepo->updateStatus($orderId, 'fleece');
        }
    }

    public function processPengujian(int $orderId, int $userId, int $shift, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'pengujian', 
            $technicalData,
            ['type' => 'pengujian', 'shift' => $shift]
        );
    }

    public function processQE(int $orderId, int $userId, int $shift, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'qe', 
            $technicalData,
            ['type' => 'qe', 'shift' => $shift]
        );
    }

    /**
     * Submit operator production activity (Create or Update).
     * Decouples the controller/view from Eloquent model writes.
     */
    public function submitOperatorActivity(
        int $orderId,
        int $userId,
        string $divisionName,
        float $kg,
        int $roll,
        int $shift,
        string $operatorManualName,
        ?int $activityId = null
    ): void {
        DB::transaction(function () use ($orderId, $userId, $divisionName, $kg, $roll, $shift, $operatorManualName, $activityId) {
            $order = MarketingOrder::findOrFail($orderId);

            $technicalData = [
                'kg' => $kg,
                'roll' => $roll,
                'operator_manual_name' => $operatorManualName,
                'shift' => $shift,
            ];

            $activityData = [
                'marketing_order_id' => $orderId,
                'operator_id'        => $userId,
                'operator_name'      => $operatorManualName,
                'division_name'      => $divisionName,
                'shift'              => $shift,
                'kg'                 => $kg,
                'roll'               => $roll,
                'technical_data'     => $technicalData,
            ];

            if ($activityId) {
                // UPDATE existing activity
                $activity = ProductionActivity::findOrFail($activityId);
                
                // Track edit audit
                ActivityLog::create([
                    'user_id'     => $userId,
                    'action'      => 'EDIT_PRODUCTION_DATA',
                    'division'    => $divisionName,
                    'art_no'      => $order->art_no,
                    'sap_no'      => $order->sap_no,
                    'description' => "Operator {$operatorManualName} memperbarui data produksi divisi {$divisionName}: Dari {$activity->kg} KG menjadi {$kg} KG.",
                ]);

                $activity->update($activityData);
            } else {
                // CREATE new activity
                $this->activityRepo->log($activityData);

                // Track create audit
                ActivityLog::create([
                    'user_id'     => $userId,
                    'action'      => 'CREATE_PRODUCTION_LOG',
                    'division'    => $divisionName,
                    'art_no'      => $order->art_no,
                    'sap_no'      => $order->sap_no,
                    'description' => "Operator {$operatorManualName} menginput data produksi divisi {$divisionName}: {$kg} KG / {$roll} Roll.",
                ]);

                // Advance order status using dynamic pipeline
                $nextStatus = $this->getNextRequiredStatus($order, $divisionName);
                if ($nextStatus) {
                    $this->orderRepo->updateStatus($orderId, $nextStatus);
                }
            }
        });
    }

    /**
     * Logika Transisi Status Dinamis
     */
    public function getNextRequiredStatus(MarketingOrder $order, string $currentDivision): ?string
    {
        $pipeline = [
            ['status' => 'dyeing',       'flag' => null],
            ['status' => 'relax-dryer',  'flag' => null],
            ['status' => 'compactor',    'flag' => 'req_compactor'],
            ['status' => 'heat-setting', 'flag' => 'req_heat_setting'],
            ['status' => 'stenter',      'flag' => 'req_stenter'],
            ['status' => 'tumbler',      'flag' => 'req_tumbler'],
            ['status' => 'fleece',       'flag' => 'req_fleece'],
            ['status' => 'pengujian',    'flag' => 'req_pengujian'],
            ['status' => 'qe',           'flag' => 'req_qe'],
            ['status' => 'finished',     'flag' => null],
        ];

        $d = strtolower(trim($currentDivision));
        $currentStatus = match(true) {
            str_contains($d, 'knit')                      => 'knitting',
            Str::contains($d, ['dye', 'scr'])             => 'dyeing',
            str_contains($d, 'relax')                     => 'relax-dryer',
            str_contains($d, 'compact')                   => 'compactor',
            str_contains($d, 'heat')                      => 'heat-setting',
            str_contains($d, 'stenter')                   => 'stenter',
            str_contains($d, 'tumbler')                   => 'tumbler',
            str_contains($d, 'fleece')                    => 'fleece',
            Str::contains($d, ['uji', 'pengujian'])       => 'pengujian',
            Str::contains($d, ['qc', 'qe'])               => 'qe',
            default                                       => null,
        };

        if ($currentStatus === null) return null;

        $currentIndex = null;
        foreach ($pipeline as $index => $stage) {
            if ($stage['status'] === $currentStatus) {
                $currentIndex = $index;
                break;
            }
        }

        $startIndex = ($currentStatus === 'knitting') ? 0 : ($currentIndex !== null ? $currentIndex + 1 : null);
        if ($startIndex === null) return null;

        for ($i = $startIndex; $i < count($pipeline); $i++) {
            $stage = $pipeline[$i];
            if ($stage['flag'] === null || (bool)$order->{$stage['flag']} === true) {
                return $stage['status'];
            }
        }

        return null;
    }
}
