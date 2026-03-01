<?php
use Livewire\Volt\Component;
use App\Models\ActivityLog;

new class extends Component {
    public function with() {
        return [
            'logs' => ActivityLog::with('user')->latest()->paginate(20)
        ];
    }
}; ?>

<div class="py-12 bg-slate-50 min-h-screen font-sans italic tracking-tighter text-left">
    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-10 flex justify-between items-end">
            <div>
                <h2 class="text-4xl font-black uppercase text-slate-800 leading-none">
                    System <span class="text-red-600">Audit Trail</span>
                </h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-2">Monitoring Aktivitas Penghapusan Log Produksi</p>
            </div>
        </div>

        <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white font-black italic uppercase">
                        <th class="px-6 py-5 text-[10px] tracking-widest">Timestamp</th>
                        <th class="px-6 py-5 text-[10px] tracking-widest">PIC Operator</th>
                        <th class="px-6 py-5 text-[10px] tracking-widest">Divisi</th>
                        <th class="px-6 py-5 text-[10px] tracking-widest">SAP Target</th>
                        <th class="px-6 py-5 text-[10px] tracking-widest">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 italic">
                    @forelse($logs as $log)
                        <tr class="hover:bg-red-50/50 transition-colors">
                            <td class="px-6 py-4 text-[11px] font-bold text-slate-400">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-black text-slate-800 uppercase italic">{{ $log->user->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[9px] font-black uppercase italic border border-slate-200">
                                    {{ $log->division }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-black text-red-600 text-xs">
                                #{{ $log->sap_no }}
                            </td>
                            <td class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase">
                                {{ $log->details }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center text-slate-300 font-black uppercase text-xs italic">Belum ada aktivitas penghapusan yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
</div>