<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Services\ProductionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * SingleOperatorForm
 *
 * Komponen ini memungkinkan SATU operator (misalnya operator Finishing)
 * mengisi log untuk semua tahap dari Dyeing hingga Fleece dalam satu halaman.
 * Stepper dan checklist sidebar dibangkitkan secara dinamis berdasarkan
 * kolom req_* pada tabel marketing_orders.
 *
 * Pipeline yang didukung:
 *   Dyeing → Relax Dryer → Compactor → Heat Setting → Stenter → Tumbler → Fleece
 */
class SingleOperatorForm extends Component
{
    // ─── SAP Lookup ────────────────────────────────────────────────────────────
    public string $artikelInput  = '';
    public bool   $isLoading     = false;
    public ?string $artikelError = null;

    /** @var MarketingOrder|null */
    public $order = null;
    public array $productionHistory = [];
    public $activeDetailTab = 'marketing';

    // ─── Stepper State ─────────────────────────────────────────────────────────
    /** Daftar divisi yang REQ-nya true, dibangun setelah order dimuat */
    public array $activeSteps = [];

    /** Indeks dalam $activeSteps yang sedang aktif */
    public int $currentStepIndex = 0;

    // ─── Form Data per Divisi ──────────────────────────────────────────────────
    // Dyeing
    public $dyeing_operator, $dyeing_cek_greige, $dyeing_gramasi, $dyeing_lebar, $dyeing_tanggal, $dyeing_jenis_mesin,
           $dyeing_no_mesin, $dyeing_warna, $dyeing_kode_warna, $dyeing_dye_system, $dyeing_treatment;

    // Relax Dryer
    public $relax_operator, $relax_tanggal, $relax_no_mesin, $relax_suhu, $relax_speed, 
           $relax_chemical, $relax_handfeel, $relax_overfeed, $relax_lebar, $relax_gramasi, $relax_shrinkage;

    // Compactor
    public $compactor_operator, $compactor_tanggal, $compactor_no_mesin, $compactor_rangka,
           $compactor_suhu, $compactor_speed, $compactor_overfeed, $compactor_felt,
           $compactor_delivery_speed, $compactor_folding_speed, $compactor_lebar, $compactor_gramasi, $compactor_shrinkage;

    // Heat Setting
    public $heat_operator, $heat_tanggal, $heat_no_mesin, $heat_rangka, $heat_suhu, $heat_speed,
           $heat_overfeed, $heat_delivery_speed, $heat_folding_speed, $heat_lebar, $heat_gramasi;

    // Stenter
    public $stenter_operator, $stenter_no_mesin;
    public array $stenter_preset = [];
    public array $stenter_drying = [];
    public array $stenter_finishing = [];

    // Tumbler
    public $tumbler_operator, $tumbler_tanggal, $tumbler_no_mesin, $tumbler_suhu,
           $tumbler_steam_inject, $tumbler_hotwind, $tumbler_coldwind, 
           $tumbler_lebar, $tumbler_gramasi, $tumbler_shrinkage;

    // Fleece
    public $fleece_no_mesin;
    public array $fleece_raising = [];
    public array $fleece_brushing = [];
    public array $fleece_shearing = [];

    // ─── Submit tracking ───────────────────────────────────────────────────────
    /** Divisi yang sudah berhasil disimpan ['dyeing', 'relax-dryer', ...] */
    public array $savedDivisions = [];

    // ─── Definisi Pipeline ─────────────────────────────────────────────────────

    /**
     * Seluruh pipeline dalam urutan tetap.
     * 'flag' null  → selalu wajib (tidak bisa di-skip).
     * 'flag' string → nama kolom req_* yang dicek dari order.
     */
    protected function getPipelineDefinition(): array
    {
        return [
            [
                'key'    => 'dyeing',
                'label'  => 'SCR / Dyeing',
                'flag'   => null,
                'icon'   => '🧪',
                'fields' => ['dyeing_operator', 'dyeing_cek_greige', 'dyeing_tanggal', 'dyeing_no_mesin', 'dyeing_jenis_mesin', 'dyeing_gramasi', 'dyeing_lebar', 'dyeing_warna', 'dyeing_kode_warna', 'dyeing_dye_system', 'dyeing_treatment'],
            ],
            [
                'key'    => 'relax-dryer',
                'label'  => 'Relax Dryer',
                'flag'   => null,
                'icon'   => '💨',
                'fields' => ['relax_operator', 'relax_tanggal', 'relax_chemical', 'relax_handfeel', 'relax_no_mesin', 'relax_overfeed', 'relax_suhu', 'relax_speed', 'relax_lebar', 'relax_gramasi', 'relax_shrinkage'],
            ],
            [
                'key'    => 'compactor',
                'label'  => 'Compactor',
                'flag'   => 'req_compactor',
                'icon'   => '🔧',
                'fields' => ['compactor_operator', 'compactor_tanggal', 'compactor_no_mesin', 'compactor_rangka', 'compactor_suhu', 'compactor_speed', 'compactor_overfeed', 'compactor_felt', 'compactor_delivery_speed', 'compactor_folding_speed', 'compactor_lebar', 'compactor_gramasi', 'compactor_shrinkage'],
            ],
            [
                'key'    => 'heat-setting',
                'label'  => 'Heat Setting',
                'flag'   => 'req_heat_setting',
                'icon'   => '🌡️',
                'fields' => ['heat_operator', 'heat_tanggal', 'heat_no_mesin', 'heat_rangka', 'heat_suhu', 'heat_speed', 'heat_overfeed', 'heat_delivery_speed', 'heat_folding_speed', 'heat_lebar', 'heat_gramasi'],
            ],
            [
                'key'    => 'stenter',
                'label'  => 'Stenter',
                'flag'   => 'req_stenter',
                'icon'   => '📐',
                'fields' => ['stenter_operator', 'stenter_no_mesin'],
            ],
            [
                'key'    => 'tumbler',
                'label'  => 'Tumbler',
                'flag'   => 'req_tumbler',
                'icon'   => '🌀',
                'fields' => ['tumbler_operator', 'tumbler_tanggal', 'tumbler_no_mesin', 'tumbler_suhu', 'tumbler_steam_inject', 'tumbler_hotwind', 'tumbler_coldwind', 'tumbler_lebar', 'tumbler_gramasi', 'tumbler_shrinkage'],
            ],
            [
                'key'    => 'fleece',
                'label'  => 'Fleece',
                'flag'   => 'req_fleece',
                'icon'   => '🧶',
                'fields' => ['fleece_no_mesin'],
            ],
        ];
    }

    // ─── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(?string $artikel = null): void
    {
        $this->dyeing_tanggal    = now()->format('Y-m-d');
        $this->relax_tanggal     = now()->format('Y-m-d');
        $this->compactor_tanggal = now()->format('Y-m-d');
        $this->heat_tanggal      = now()->format('Y-m-d');
        $this->tumbler_tanggal   = now()->format('Y-m-d');

        // Initialize Stenter
        $stenterFields = [
            'tanggal' => now()->format('Y-m-d'), 'suhu' => '', 'speed' => '', 'padder' => '',
            'rangka' => '', 'overfeed_a' => '', 'overfeed_b' => '', 'fan' => '',
            'delivery' => '', 'folding' => '', 'chem1' => '', 'chem2' => '',
            'lebar' => '', 'gramasi' => '', 'shrinkage' => ''
        ];
        $this->stenter_preset = $stenterFields;
        $this->stenter_drying = $stenterFields;
        $this->stenter_finishing = $stenterFields;

        // Initialize Fleece
        $this->fleece_raising = [
            'tanggal' => now()->format('Y-m-d'), 'operator' => '', 'standar_bulu' => '',
            'speed' => '', 'cloth_out' => '', 'bend_pin' => '', 'stright_pin' => '',
            'rpm_drum' => '', 'lebar_gsm' => '', 'drum_brush' => ''
        ];
        $this->fleece_brushing = [
            'tanggal' => now()->format('Y-m-d'), 'operator' => '', 'standar_bulu' => '',
            'cloth_speed' => '', 'cloth_out' => '', 'left_brush' => '', 'right_brush' => '',
            'rpm_drum' => '', 'tension' => '', 'lebar_gramasi' => ''
        ];
        $this->fleece_shearing = [
            'tanggal' => now()->format('Y-m-d'), 'operator' => '', 'speed' => '',
            'cloth_out' => '', 'expending' => '', 'shear' => '', 'lebar_gramasi' => ''
        ];

        // Jika Artikel dipassing via URL (mendukung 'artikel' atau legacy 'sap'), otomatis lookup
        $targetIdentifier = $artikel ?? request()->query('artikel') ?? request()->query('sap');
        if ($targetIdentifier) {
            $this->artikelInput = $targetIdentifier;
            $this->lookupArtikel();
        }
    }

    // ─── SAP Lookup ────────────────────────────────────────────────────────────

    public function lookupArtikel(): void
    {
        $this->artikelError  = null;
        $this->order     = null;
        $this->activeSteps    = [];
        $this->savedDivisions = [];
        $this->currentStepIndex = 0;

        $this->validate(['artikelInput' => 'required|string|min:1'], [
            'artikelInput.required' => 'Nomor Artikel wajib diisi.',
        ]);

        $repo = app(\App\Repositories\OrderRepository::class);
        $order = $repo->findByIdentifier($this->artikelInput);

        if (! $order) {
            $this->artikelError = "Nomor Artikel #{$this->artikelInput} tidak ditemukan dalam database.";
            return;
        }

        $this->order = $order;
        $this->dyeing_warna = '';
        $this->dyeing_kode_warna = ''; 
        
        // Reset dynamic arrays to prevent data leaking from previous search
        $this->mount();
        // JEJAK PRODUKSI (Traceability)
        $this->productionHistory = ProductionActivity::with('operator')
            ->where('marketing_order_id', $order->id)
            ->orderBy('created_at', 'asc')
            ->get()->toArray();

        // Bangun daftar step aktif berdasarkan flag order
        $this->activeSteps = collect($this->getPipelineDefinition())
            ->filter(fn($step) => $step['flag'] === null || (bool) $order->{$step['flag']} === true)
            ->values()
            ->toArray();

        // Resume progres: cari divisi mana saja yang sudah diselesaikan untuk Artikel ini
        $existingLogs = ProductionActivity::where('marketing_order_id', $order->id)
            ->whereIn('division_name', collect($this->activeSteps)->pluck('key')->toArray())
            ->pluck('division_name')
            ->toArray();

        $this->savedDivisions = $existingLogs;

        // Tentukan step berikutnya yang belum diisi
        foreach ($this->activeSteps as $index => $step) {
            if (!in_array($step['key'], $this->savedDivisions)) {
                $this->currentStepIndex = $index;
                break;
            }
        }
        
        // Jika semuanya sudah terisi, arahkan ke step terakhir
        if (count($this->savedDivisions) === count($this->activeSteps) && count($this->activeSteps) > 0) {
            $this->currentStepIndex = count($this->activeSteps) - 1;
        }
    }

    // ─── Computed helpers ──────────────────────────────────────────────────────

    /**
     * Kembalikan semua langkah pipeline beserta statusnya untuk tampilan checklist.
     * Setiap item memiliki: key, label, icon, flag, status ('skipped'|'saved'|'active'|'pending')
     */
    public function getChecklist(): array
    {
        if (! $this->order) {
            return [];
        }

        $activeKeys = collect($this->activeSteps)->pluck('key')->toArray();

        return collect($this->getPipelineDefinition())->map(function ($step) use ($activeKeys) {
            if (! in_array($step['key'], $activeKeys)) {
                $status = 'skipped';
            } elseif (in_array($step['key'], $this->savedDivisions)) {
                $status = 'saved';
            } elseif (
                isset($this->activeSteps[$this->currentStepIndex]) &&
                $this->activeSteps[$this->currentStepIndex]['key'] === $step['key']
            ) {
                $status = 'active';
            } else {
                $status = 'pending';
            }

            return array_merge($step, ['status' => $status]);
        })->toArray();
    }

    /**
     * Apakah tombol submit final boleh aktif?
     * True jika semua step aktif sudah tersimpan.
     */
    public function canSubmitAll(): bool
    {
        if (empty($this->activeSteps)) {
            return false;
        }
        $activeKeys = collect($this->activeSteps)->pluck('key')->toArray();
        return empty(array_diff($activeKeys, $this->savedDivisions));
    }

    // ─── Step Navigation ───────────────────────────────────────────────────────

    public function goToStep(string $key): void
    {
        foreach ($this->activeSteps as $index => $step) {
            if ($step['key'] === $key) {
                $this->currentStepIndex = $index;
                
                if (in_array($key, $this->savedDivisions)) {
                    $this->loadSavedData($key);
                }
                break;
            }
        }
    }

    protected function loadSavedData(string $key): void
    {
        $log = \App\Models\ProductionActivity::where('marketing_order_id', $this->order->id)
            ->where('division_name', $key)
            ->first();
            
        if (!$log) return;
        
        $techData = is_array($log->technical_data) ? $log->technical_data : json_decode($log->technical_data, true);
        
        if ($key === 'dyeing') {
            $this->dyeing_operator    = $techData['operator'] ?? '';
            $this->dyeing_cek_greige  = $techData['cek_greige'] ?? '';
            $this->dyeing_tanggal     = $techData['tanggal'] ?? now()->format('Y-m-d');
            $this->dyeing_no_mesin    = $techData['no_mesin'] ?? '';
            $this->dyeing_jenis_mesin = $techData['jenis_mesin'] ?? '';
            $this->dyeing_gramasi     = $techData['gramasi'] ?? '';
            $this->dyeing_lebar       = $techData['lebar'] ?? '';
            $this->dyeing_warna       = $techData['warna'] ?? '';
            $this->dyeing_kode_warna  = $techData['kode_warna'] ?? '';
            $this->dyeing_dye_system  = $techData['dye_system'] ?? '';
            $this->dyeing_treatment   = $techData['treatment'] ?? '';
        }
    }

    public function nextStep(): void
    {
        if ($this->currentStepIndex < count($this->activeSteps) - 1) {
            $this->currentStepIndex++;
        }
    }

    public function prevStep(): void
    {
        if ($this->currentStepIndex > 0) {
            $this->currentStepIndex--;
        }
    }

    // ─── Save per Divisi ───────────────────────────────────────────────────────

    public function saveCurrentStep(): void
    {
        if (! $this->order || empty($this->activeSteps)) {
            return;
        }

        $step = $this->activeSteps[$this->currentStepIndex];

        // Dispatch ke metode save yang sesuai
        match ($step['key']) {
            'dyeing'       => $this->saveDivision('dyeing',       $this->buildDyeingData()),
            'relax-dryer'  => $this->saveDivision('relax-dryer',  $this->buildRelaxData()),
            'compactor'    => $this->saveDivision('compactor',     $this->buildCompactorData()),
            'heat-setting' => $this->saveDivision('heat-setting',  $this->buildHeatData()),
            'stenter'      => $this->saveDivision('stenter',       $this->buildStenterData()),
            'tumbler'      => $this->saveDivision('tumbler',       $this->buildTumblerData()),
            'fleece'       => $this->saveDivision('fleece',        $this->buildFleeceData()),
            default        => null,
        };
    }

    protected function saveDivision(string $divisionKey, array $techData): void
    {
        if (in_array($divisionKey, $this->savedDivisions)) {
            $this->dispatch('show-toast', message: "Divisi {$divisionKey} sudah tersimpan.", type: 'warning');
            return;
        }

        $this->validate($this->rulesFor($divisionKey));

        $service = app(\App\Services\ProductionService::class);
        $shift = method_exists($this, 'determineShift') ? $this->determineShift() : 1;

        // Delegate ke ProductionService
        match ($divisionKey) {
            'dyeing'       => $service->processDyeing($this->order->id, auth()->id(), $techData),
            'relax-dryer'  => $service->processRelaxDryer($this->order->id, auth()->id(), $techData['kg'] ?? 0, $techData['roll'] ?? 0, $shift, $techData),
            'compactor'    => $service->processFinishing($this->order->id, auth()->id(), 'compactor', $techData['no_mesin'] ?? '?', $shift, $techData),
            'heat-setting' => $service->processFinishing($this->order->id, auth()->id(), 'heat-setting', $techData['no_mesin'] ?? '?', $shift, $techData),
            'stenter'      => $service->processStenter($this->order->id, auth()->id(), $shift, $techData, true),
            'tumbler'      => $service->processTumbler($this->order->id, auth()->id(), $shift, $techData),
            'fleece'       => $service->processFleece($this->order->id, auth()->id(), $shift, $techData, true),
            default        => null,
        };

        $this->savedDivisions[] = $divisionKey;
        $this->dispatch('show-toast', message: "✅ Data {$divisionKey} berhasil disimpan!", type: 'success');

        if ($this->currentStepIndex < count($this->activeSteps) - 1) {
            $this->currentStepIndex++;
        }
    }

    /**
     * Submit final: update status order ke tahap selanjutnya setelah semua step selesai.
     */
    public function submitAll()
    {
        if (! $this->canSubmitAll()) {
            $this->dispatch('show-toast', message: 'Selesaikan semua tahap terlebih dahulu.', type: 'error');
            return;
        }

        $service = app(\App\Services\ProductionService::class);
        $lastStepKey = end($this->activeSteps)['key'] ?? 'fleece';
        $nextStatus = $service->getNextRequiredStatus($this->order, $lastStepKey) ?? 'finished';

        DB::transaction(function () use ($nextStatus) {
            $this->order->update([
                'status' => $nextStatus,
                'processing_by' => null,
                'processing_at' => null,
            ]);
        });

        session()->flash('message', "Semua data berhasil dikirim! Order #" . $this->order->art_no . " → {$nextStatus}. 🚀");

        return redirect()->route('operator.logbook', ['menu' => 'orders']);
    }

    // ─── Validation Rules ──────────────────────────────────────────────────────

    protected function rulesFor(string $divisionKey): array
    {
        return match ($divisionKey) {
            'dyeing' => [
                'dyeing_operator'    => 'required',
                'dyeing_tanggal'     => 'required|date',
                'dyeing_no_mesin'    => 'required',
                'dyeing_jenis_mesin' => 'required',
                'dyeing_gramasi'     => 'required',
            ],
            'relax-dryer' => [
                'relax_tanggal'  => 'required|date',
                'relax_no_mesin' => 'required',
                'relax_suhu'     => 'required|numeric',
                'relax_speed'    => 'required|numeric',
            ],
            'compactor' => [
                'compactor_tanggal'  => 'required|date',
                'compactor_no_mesin' => 'required',
            ],
            'heat-setting' => [
                'heat_tanggal'  => 'required|date',
                'heat_no_mesin' => 'required',
            ],
            'stenter' => [
                'stenter_no_mesin' => 'required',
            ],
            'tumbler' => [
                'tumbler_tanggal'  => 'required|date',
                'tumbler_no_mesin' => 'required',
            ],
            'fleece' => [
                'fleece_no_mesin' => 'required',
            ],
            default => [],
        };
    }

    // ─── Data Builders ─────────────────────────────────────────────────────────

    protected function buildDyeingData(): array
    {
        return [
            'operator'    => $this->dyeing_operator,
            'cek_greige'  => $this->dyeing_cek_greige,
            'tanggal'     => $this->dyeing_tanggal,
            'no_mesin'    => $this->dyeing_no_mesin,
            'jenis_mesin' => $this->dyeing_jenis_mesin,
            'gramasi'     => $this->dyeing_gramasi,
            'lebar'       => $this->dyeing_lebar,
            'warna'       => $this->dyeing_warna,
            'kode_warna'  => $this->dyeing_kode_warna,
            'dye_system'  => $this->dyeing_dye_system,
            'treatment'   => $this->dyeing_treatment,
        ];
    }

    protected function buildRelaxData(): array
    {
        return [
            'operator'    => $this->relax_operator,
            'tanggal'     => $this->relax_tanggal,
            'no_mesin'    => $this->relax_no_mesin,
            'suhu'        => $this->relax_suhu,
            'speed'       => $this->relax_speed,
            'chemical'    => $this->relax_chemical,
            'handfeel'    => $this->relax_handfeel,
            'overfeed'    => $this->relax_overfeed,
            'lebar'       => $this->relax_lebar,
            'gramasi'     => $this->relax_gramasi,
            'shrinkage'   => $this->relax_shrinkage,
        ];
    }

    protected function buildCompactorData(): array
    {
        return [
            'operator'       => $this->compactor_operator,
            'tanggal'        => $this->compactor_tanggal,
            'no_mesin'       => $this->compactor_no_mesin,
            'rangka'         => $this->compactor_rangka,
            'suhu'           => $this->compactor_suhu,
            'speed'          => $this->compactor_speed,
            'overfeed'       => $this->compactor_overfeed,
            'felt'           => $this->compactor_felt,
            'delivery_speed' => $this->compactor_delivery_speed,
            'folding_speed'  => $this->compactor_folding_speed,
            'lebar'          => $this->compactor_lebar,
            'gramasi'        => $this->compactor_gramasi,
            'shrinkage'      => $this->compactor_shrinkage,
        ];
    }

    protected function buildHeatData(): array
    {
        return [
            'operator'       => $this->heat_operator,
            'tanggal'        => $this->heat_tanggal,
            'no_mesin'       => $this->heat_no_mesin,
            'rangka'         => $this->heat_rangka,
            'suhu'           => $this->heat_suhu,
            'speed'          => $this->heat_speed,
            'overfeed'       => $this->heat_overfeed,
            'delivery_speed' => $this->heat_delivery_speed,
            'folding_speed'  => $this->heat_folding_speed,
            'lebar'          => $this->heat_lebar,
            'gramasi'        => $this->heat_gramasi,
        ];
    }

    protected function buildStenterData(): array
    {
        return [
            'operator' => $this->stenter_operator,
            'no_mesin' => $this->stenter_no_mesin,
            'preset'   => $this->stenter_preset,
            'drying'   => $this->stenter_drying,
            'finishing' => $this->stenter_finishing,
        ];
    }

    protected function buildTumblerData(): array
    {
        return [
            'operator'     => $this->tumbler_operator,
            'tanggal'      => $this->tumbler_tanggal,
            'no_mesin'     => $this->tumbler_no_mesin,
            'suhu'         => $this->tumbler_suhu,
            'steam_inject' => $this->tumbler_steam_inject,
            'hotwind'      => $this->tumbler_hotwind,
            'coldwind'     => $this->tumbler_coldwind,
            'lebar'        => $this->tumbler_lebar,
            'gramasi'      => $this->tumbler_gramasi,
            'shrinkage'    => $this->tumbler_shrinkage,
        ];
    }

    protected function buildFleeceData(): array
    {
        return [
            'no_mesin' => $this->fleece_no_mesin,
            'raising'  => $this->fleece_raising,
            'brushing' => $this->fleece_brushing,
            'shearing' => $this->fleece_shearing,
        ];
    }

    // ─── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.operator.single-operator-form', [
            'checklist'    => $this->getChecklist(),
            'canSubmitAll' => $this->canSubmitAll(),
            'currentStep'  => $this->activeSteps[$this->currentStepIndex] ?? null,
        ]);
    }
}
