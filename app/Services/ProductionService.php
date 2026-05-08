<?php

namespace App\Services;

use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    /**
     * Umum: Menyimpan aktivitas produksi dan memajukan status order
     */
    private function logActivityAndAdvance(
        int $orderId,
        int $userId,
        string $divisionName,
        string $nextStatus,
        array $technicalData = [],
        array $extraData = []
    ): void {
        DB::transaction(function () use ($orderId, $userId, $divisionName, $nextStatus, $technicalData, $extraData) {
            
            $activityData = array_merge([
                'marketing_order_id' => $orderId,
                'user_id'            => $userId, // Atur ke user_id
                'operator_id'        => $userId, // Fallback karena ada kolom operator_id juga di migration
                'division_name'      => $divisionName,
                'technical_data'     => $technicalData,
            ], $extraData);

            // Karena beberapa tabel mungkin butuh updateOrCreate, tapi secara umum create.
            // Untuk Knitting yang spesifik menggunakan updateOrCreate di form lamanya:
            if ($divisionName === 'knitting') {
                 ProductionActivity::updateOrCreate(
                    [
                        'marketing_order_id' => $orderId,
                        'division_name'      => 'knitting',
                    ],
                    $activityData
                );
            } else {
                 ProductionActivity::create($activityData);
            }

            MarketingOrder::where('id', $orderId)->update(['status' => $nextStatus]);
        });
    }

    public function processKnitting(int $orderId, int $userId, float $kg, int $roll, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'knitting', 
            OrderStatus::DYEING->value, 
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
            OrderStatus::RELAX_DRYER->value, 
            $technicalData
        );
    }

    public function processRelaxDryer(int $orderId, int $userId, float $kg, int $roll, int $shift, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'relax-dryer', 
            OrderStatus::COMPACTOR->value, 
            $technicalData,
            ['kg' => $kg, 'roll' => $roll, 'shift' => $shift]
        );
    }

    public function processFinishing(int $orderId, int $userId, string $type, string $noMesin, int $shift, array $technicalData): void
    {
        // $type bisa berupa 'compactor' atau 'heat-setting' yang didapat dari $jenis_proses
        $nextStatus = ($type === 'compactor') ? OrderStatus::HEAT_SETTING->value : OrderStatus::STENTER->value;

        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'finishing', 
            $nextStatus, 
            $technicalData,
            ['type' => 'finishing', 'no_mesin' => $noMesin, 'shift' => $shift]
        );
    }

    public function processStenter(int $orderId, int $userId, int $shift, array $technicalData, bool $isLastFinishingStep): void
    {
        // Jika finishing sudah selesai di proses stenter ini
        $nextStatus = $isLastFinishingStep ? OrderStatus::TUMBLER->value : OrderStatus::STENTER->value;

        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'stenter', 
            $nextStatus, 
            $technicalData,
            ['type' => 'stenter', 'shift' => $shift]
        );
    }

    public function processTumbler(int $orderId, int $userId, int $shift, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'tumbler', 
            OrderStatus::FLEECE->value, 
            $technicalData,
            ['type' => 'tumbler', 'shift' => $shift]
        );
    }

    public function processFleece(int $orderId, int $userId, int $shift, array $technicalData, bool $isLastShearingStep): void
    {
        $nextStatus = $isLastShearingStep ? OrderStatus::PENGUJIAN->value : OrderStatus::FLEECE->value;

        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'fleece', 
            $nextStatus, 
            $technicalData,
            ['type' => 'fleece', 'shift' => $shift]
        );
    }

    public function processPengujian(int $orderId, int $userId, int $shift, array $technicalData): void
    {
        $this->logActivityAndAdvance(
            $orderId, 
            $userId, 
            'pengujian', 
            OrderStatus::QE->value, 
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
            OrderStatus::FINISHED->value, 
            $technicalData,
            ['type' => 'qe', 'shift' => $shift]
        );
    }
}
