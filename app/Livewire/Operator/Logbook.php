<?php

namespace App\Livewire\Operator;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductionActivity;
use Illuminate\Support\Facades\Auth;

class Logbook extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = ''; // Untuk filter jenis (Knitting, Dyeing, dll)

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteEntry($id)
    {
        $activity = ProductionActivity::where('user_id', Auth::id())->findOrFail($id);
        $activity->delete();
        
        session()->flash('message', 'Data berhasil dihapus dari logbook.');
    }

    public function render()
    {
        $activities = ProductionActivity::query()
            ->with('marketingOrder')
            ->where('user_id', Auth::id())
            ->when($this->search, function($query) {
                $query->whereHas('marketingOrder', function($q) {
                    $q->where('sap_no', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterType, function($query) {
                $query->where('type', $this->filterType);
            })
            ->latest()
            ->paginate(10);

        return view('components.operator.logbook')
            ->layout('layouts.app');
    }
}