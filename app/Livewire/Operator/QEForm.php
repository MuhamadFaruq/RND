<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use App\Services\ProductionService;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Auth;

class QEForm extends Component
{
    public $artikelNo, $order_id;
    public $order;
    public $activeDetailTab = 'marketing';
    public array $pipelineErrors = [];

    // Properti Form QE
    public $operator, $fabric_name, $lebar, $gramasi, $shrinkage, $note;

    public function mount($artikel = null)
    {
        if (auth()->user()->role !== 'qe' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses Ditolak: Anda tidak terdaftar di divisi QE.');
        }

        if ($artikel) {
            $this->artikelNo = $artikel;
            $this->loadOrder($artikel);
        }
    }

    public function loadOrder($identifier)
    {
        $repo = app(OrderRepository::class);
        $this->order = $repo->findByIdentifier($identifier);

        if ($this->order) {
            $this->order_id = $this->order->id;
            $this->fabric_name = $this->order->art_no;
            $this->validatePipelineCompleteness();
        } else {
            $this->reset(['order_id', 'order', 'fabric_name']);
        }
    }

    public function updatedArtikelNo($value)
    {
        $this->loadOrder($value);
    }

    private function getPipelineMap(): array
    {
        return [
            'req_compactor' => ['label' => 'Compactor', 'division' => 'compactor'],
            'req_heat_setting' => ['label' => 'Heat Setting', 'division' => 'heat-setting'],
            'req_stenter' => ['label' => 'Stenter', 'division' => 'stenter'],
            'req_tumbler' => ['label' => 'Tumbler', 'division' => 'tumbler'],
            'req_fleece' => ['label' => 'Fleece', 'division' => 'fleece'],
            'req_pengujian' => ['label' => 'Pengujian', 'division' => 'pengujian'],
        ];
    }

    private function validatePipelineCompleteness(): bool
    {
        $this->pipelineErrors = [];
        if (!$this->order) return true;

        $existingDivisions = ProductionActivity::where('marketing_order_id', $this->order->id)
            ->pluck('division_name')
            ->toArray();

        // Selalu wajib: dyeing, relax-dryer
        foreach (['dyeing' => 'Dyeing', 'relax-dryer' => 'Relax Dryer'] as $div => $label) {
            if (!in_array($div, $existingDivisions)) {
                $this->pipelineErrors[] = "Proses {$label} belum diisi oleh bagian produksi. Silakan lengkapi sebelum QE final.";
            }
        }

        // Cek berdasarkan req_*
        foreach ($this->getPipelineMap() as $flag => $info) {
            if ((bool) $this->order->{$flag} === true) {
                if (!in_array($info['division'], $existingDivisions)) {
                    $this->pipelineErrors[] = "Proses {$info['label']} belum diisi oleh bagian produksi. Silakan lengkapi sebelum QE final.";
                }
            }
        }

        return empty($this->pipelineErrors);
    }

    public function submit()
    {
        $this->validate([
            'artikelNo' => 'required',
            'fabric_name' => 'required',
            'lebar' => 'required|numeric',
            'gramasi' => 'required|numeric',
        ]);

        if (!$this->validatePipelineCompleteness()) {
            return;
        }

        try {
            $service = app(ProductionService::class);
            $service->processQE(
                $this->order_id,
                Auth::id(),
                $this->determineShift(),
                [
                    'operator' => $this->operator,
                    'fabric_name' => $this->fabric_name,
                    'lebar' => $this->lebar,
                    'gramasi' => $this->gramasi,
                    'shrinkage' => $this->shrinkage,
                    'note' => $this->note,
                ]
            );

            session()->flash('message', 'Data QE Final Description berhasil disimpan! ');
            return redirect()->route('operator.logbook');
        } catch (\Exception $e) {
            $this->dispatch('show-error-toast', message: 'Gagal menyimpan data QE: ' . $e->getMessage());
        }
    }

    private function determineShift()
    {
        $hour = date('H');
        if ($hour >= 7 && $hour < 15) return 1;
        if ($hour >= 15 && $hour < 23) return 2;
        return 3;
    }

    public function render()
    {
        $history = [];
        if ($this->order_id) {
            $history = ProductionActivity::with('operator')
                ->where('marketing_order_id', $this->order_id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('livewire.operator.qe-form', [
            'productionHistory' => $history
        ])->layout('layouts.app');
    }
}