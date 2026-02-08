<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
   <div class="bg-white rounded-[2rem] p-8 mb-8 shadow-sm">
    <div class="flex justify-between items-center mb-8">
        <h3 class="text-sm font-black uppercase italic text-slate-800">📈 Production Analytics (Livewire)</h3>
        
        <div class="flex bg-slate-100 p-1 rounded-2xl">
            <button wire:click="setPeriod('weekly')" class="px-4 py-2 rounded-xl text-[9px] font-black {{ $period == 'weekly' ? 'bg-white text-red-600 shadow' : 'text-slate-400' }}">Mingguan</button>
            <button wire:click="setPeriod('monthly')" class="px-4 py-2 rounded-xl text-[9px] font-black {{ $period == 'monthly' ? 'bg-white text-red-600 shadow' : 'text-slate-400' }}">Bulanan</button>
        </div>
    </div>

    <div class="h-64 w-full bg-slate-50/50 rounded-[2rem] p-8 relative">
        @php
            $maxVal = collect($trends)->max('total') ?: 1;
            $points = "";
            foreach($trends as $i => $d) {
                $x = ($i / (count($trends) - 1)) * 100;
                $y = 100 - (($d['total'] / $maxVal) * 80 + 10);
                $points .= "$x,$y ";
            }
        @endphp

        <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 100 100">
            <polyline fill="none" stroke="#ED1C24" stroke-width="1.5" points="{{ $points }}" />
            @foreach($trends as $i => $d)
                @php 
                    $cx = ($i / (count($trends) - 1)) * 100;
                    $cy = 100 - (($d['total'] / $maxVal) * 80 + 10);
                @endphp
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="1.5" fill="white" stroke="#ED1C24" stroke-width="1" />
            @endforeach
        </svg>

        <div class="flex justify-between mt-4">
            @foreach($trends as $d)
                <span class="text-[9px] font-black text-slate-400 uppercase">{{ $d['day'] }}</span>
            @endforeach
        </div>
    </div>
</div>