<x-layouts.app>
    {{-- Container utama dengan background gelap agar konsisten dengan tema industrial --}}
    <div class="min-h-screen bg-slate-900">
        
        {{-- 1. DASHBOARD SUPER ADMIN / ADMIN --}}
        @if(in_array(auth()->user()->role, ['super-admin', 'admin']))
            <livewire:admin.dashboard />
        @endif

        {{-- 2. DASHBOARD MARKETING --}}
        @if(auth()->user()->role === 'marketing')
            <livewire:marketing.marketing-dashboard />
        @endif

        {{-- 3. DASHBOARD OPERATOR (KNITTING, DYEING, DLL) --}}
        @if(in_array(auth()->user()->role, ['knitting', 'dyeing', 'qc', 'relax-dryer', 'finishing', 'stenter']))
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {{-- Anda bisa memanggil komponen logbook langsung di sini --}}
                    <livewire:operator.logbook />
                </div>
            </div>
        @endif

    </div>
</x-layouts.app>