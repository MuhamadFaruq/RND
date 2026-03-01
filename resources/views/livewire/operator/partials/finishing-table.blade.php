<div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 flex justify-between items-center group italic hover:border-emerald-300 transition-all">
    <div class="flex items-center gap-6">
        <div class="bg-emerald-50 text-emerald-600 w-14 h-14 rounded-2xl flex items-center justify-center font-black text-xl shadow-sm">
            ✨
        </div>
        <div class="text-left">
            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">#{{ $job->sap_no }}</span>
            <h4 class="text-xl font-black text-slate-800 leading-none uppercase">{{ $job->art_no }}</h4>
            <div class="flex items-center gap-2 mt-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $job->pelanggan }}</p>
                <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                <p class="text-[10px] font-black text-rose-500 uppercase tracking-tighter">{{ $job->warna }}</p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-8">
        <div class="text-right border-r pr-8 border-slate-100">
            <p class="text-[9px] font-black text-slate-400 uppercase leading-none mb-1">Target Produksi</p>
            <p class="text-base font-black text-slate-800 italic">{{ number_format($job->kg_target, 1) }} <span class="text-[10px]">KG</span></p>
        </div>
        
        <button wire:click="showOrderDetail({{ $job->id }})" 
            class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase hover:bg-blue-600 transition-all shadow-lg shadow-slate-200">
            DETAIL & PROSES
        </button>
    </div>
</div>