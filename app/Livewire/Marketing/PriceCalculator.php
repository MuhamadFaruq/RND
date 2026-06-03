<?php

namespace App\Livewire\Marketing;

use Livewire\Component;

class PriceCalculator extends Component
{
    // Inputs
    public $yarn_price = 0;
    public $chemical_price = 0;
    public $knitting_fee = 0;
    public $dyeing_fee = 0;
    public $overhead = 0;
    public $waste_knitting = 3; // Default 3%
    public $waste_dyeing = 2;   // Default 2%
    public $margin = 15;        // Default 15%
    public $ppn = 11;           // PPN 11%

    // Outputs
    public $hpp = 0;
    public $selling_price = 0;
    public $profit_per_kg = 0;
    public $basic_cost = 0;
    public $waste_factor = 0;

    // Save Form
    public $customer_name = '';
    public $article_name = '';
    public $showSaveModal = false;
    public $activeTab = 'calculator'; // calculator | history

    public function updated()
    {
        $this->calculate();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function openSaveModal()
    {
        $this->showSaveModal = true;
    }

    public function saveQuotation()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'article_name' => 'required|string|max:255',
        ]);

        \App\Models\MarketingQuotation::create([
            'user_id' => auth()->id(),
            'customer_name' => $this->customer_name,
            'article_name' => $this->article_name,
            'yarn_price' => $this->yarn_price,
            'chemical_price' => $this->chemical_price,
            'knitting_fee' => $this->knitting_fee,
            'dyeing_fee' => $this->dyeing_fee,
            'overhead' => $this->overhead,
            'waste_knitting' => $this->waste_knitting,
            'waste_dyeing' => $this->waste_dyeing,
            'margin' => $this->margin,
            'ppn' => $this->ppn,
            'hpp' => $this->hpp,
            'selling_price' => $this->selling_price,
        ]);

        $this->showSaveModal = false;
        $this->customer_name = '';
        $this->article_name = '';
        $this->activeTab = 'history';

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Quotation berhasil disimpan ke riwayat!'
        ]);
    }

    public function deleteQuotation($id)
    {
        \App\Models\MarketingQuotation::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Quotation dihapus.'
        ]);
    }

    public function loadQuotation($id)
    {
        $q = \App\Models\MarketingQuotation::findOrFail($id);
        
        $this->yarn_price = $q->yarn_price;
        $this->chemical_price = $q->chemical_price;
        $this->knitting_fee = $q->knitting_fee;
        $this->dyeing_fee = $q->dyeing_fee;
        $this->overhead = $q->overhead;
        $this->waste_knitting = $q->waste_knitting;
        $this->waste_dyeing = $q->waste_dyeing;
        $this->margin = $q->margin;
        $this->ppn = $q->ppn;

        $this->calculate();
        $this->activeTab = 'calculator';

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Data quotation berhasil dimuat!'
        ]);
    }

    public function mount()
    {
        if (!auth()->check() || (auth()->user()->role !== 'marketing' && !auth()->user()->isSuperAdmin())) {
            abort(403, 'Akses Ditolak.');
        }

        $this->calculate();
    }

    public function calculate()
    {
        // 1. Basic Sum of Costs
        $this->basic_cost = (float)$this->yarn_price + 
                           (float)$this->chemical_price + 
                           (float)$this->knitting_fee + 
                           (float)$this->dyeing_fee + 
                           (float)$this->overhead;

        $basic_cost = $this->basic_cost;

        // 2. Waste Factor Calculation (Inverse of Yield)
        // If waste is 5%, yield is 0.95. HPP increases by 1/0.95
        $yield_knitting = 1 - ((float)$this->waste_knitting / 100);
        $yield_dyeing = 1 - ((float)$this->waste_dyeing / 100);
        
        $total_yield = $yield_knitting * $yield_dyeing;

        if ($total_yield > 0) {
            $this->hpp = $basic_cost / $total_yield;
            $this->waste_factor = 1 / $total_yield;
        } else {
            $this->hpp = 0;
            $this->waste_factor = 0;
        }

        // 3. Selling Price
        $price_before_tax = $this->hpp * (1 + ((float)$this->margin / 100));
        $this->selling_price = $price_before_tax * (1 + ((float)$this->ppn / 100));

        // 4. Profit per KG (Net Profit before Tax)
        $this->profit_per_kg = $price_before_tax - $this->hpp;
    }

    public function render()
    {
        return view('livewire.marketing.price-calculator');
    }
}
