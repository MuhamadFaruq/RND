@props(['title', 'value', 'color', 'icon'])

<div {{ $attributes->merge(['class' => "bg-white p-6 rounded-3xl shadow-sm border-b-4 $color"]) }}>
    <div class="flex justify-between items-start">
        <div>
            <div class="text-slate-400 text-[10px] font-black uppercase mb-1 tracking-widest">{{ $title }}</div>
            <div class="text-3xl font-black text-slate-800">{{ $value }}</div>
        </div>
        <div class="text-2xl opacity-50">{{ $icon }}</div>
    </div>
</div>