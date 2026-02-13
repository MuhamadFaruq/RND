<div class="min-h-screen bg-[#f8fafc] py-8 px-6 font-inter tracking-tight">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-[1600px] mx-auto">
        
        <div class="mb-10 flex justify-between items-start">
            <div>
                <h1 class="text-4xl font-black uppercase text-slate-900 leading-none">
                    Marketing <span class="text-red-600">War Room</span>
                </h1>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-[0.3em] mt-2 italic">
                    Duniatex Group Industrial Monitoring Hub
                </p>
            </div>
            <div class="flex gap-4">
                <div class="text-right mr-6 border-r border-slate-200 pr-6">
                    <p class="text-[10px] font-black text-slate-400 uppercase">System Status</p>
                    <p class="text-sm font-bold text-green-500 flex items-center justify-end gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span> LIVE DATA
                    </p>
                </div>
                <button class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase shadow-xl hover:bg-red-600 transition-all flex items-center gap-2">
                    <span>📅</span> {{ now()->format('d M Y') }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden group">
                <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg. Lead Time</p>
                <h3 class="text-4xl font-black text-slate-800 mt-1">14.2 <span class="text-sm font-bold text-slate-400">Days</span></h3>
                <p class="text-[10px] font-bold text-green-500 mt-2">↑ 2.1% Improvement vs Last Month</p>
            </div>

            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Monthly Realization (KG)</p>
                <h3 class="text-4xl font-black text-slate-800 mt-1">82.5<span class="text-sm font-bold text-slate-400">%</span></h3>
                <div class="w-full bg-slate-100 h-2 rounded-full mt-4 overflow-hidden">
                    <div class="bg-red-600 h-full w-[82.5%] rounded-full"></div>
                </div>
                <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-tighter text-right">Target: 2.5M KG</p>
            </div>

            <div class="bg-slate-900 p-6 rounded-[2.5rem] shadow-xl shadow-slate-200 text-white group">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Free Knitting Capacity</p>
                <h3 class="text-4xl font-black text-white mt-1">120 <span class="text-sm font-black text-red-500 italic">TONS</span></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 italic group-hover:text-white transition-colors">Avail. until Week 3 / Feb 2026</p>
            </div>

            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Stock-Lot Ready (Rolls)</p>
                <h3 class="text-4xl font-black text-slate-800 mt-1">1,402 <span class="text-sm font-bold text-slate-400 italic">ROLLS</span></h3>
                <button class="mt-3 text-[10px] font-black text-red-600 underline uppercase tracking-widest hover:text-black">View Stock-lot List</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <div class="lg:col-span-2 bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-black uppercase text-slate-800 tracking-tighter">Production Pipeline Forecast</h3>
                    <select class="bg-slate-50 border-none text-[10px] font-black rounded-xl px-4 py-2 outline-none uppercase">
                        <option>Next 3 Months</option>
                        <option>Current Month</option>
                    </select>
                </div>
                <canvas id="pipelineChart" height="120"></canvas>
            </div>

            <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm">
                <h3 class="font-black uppercase text-slate-800 tracking-tighter mb-6">Top 5 Performing Articles</h3>
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="w-8 h-8 bg-slate-900 text-white flex items-center justify-center rounded-xl font-black text-xs italic">01</span>
                            <div>
                                <p class="text-xs font-black uppercase text-slate-800">CVC 30S PIQUE</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase">240 GSM / 80" / ROUND</p>
                            </div>
                        </div>
                        <p class="text-sm font-black italic text-red-600">450T</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="w-8 h-8 bg-slate-100 text-slate-400 flex items-center justify-center rounded-xl font-black text-xs italic">02</span>
                            <div>
                                <p class="text-xs font-black uppercase text-slate-800">CVC 30S SINGLE JERSEY</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase">160 GSM / 72" / OPEN FINISH</p>
                            </div>
                        </div>
                        <p class="text-sm font-black italic text-slate-400">380T</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 border-l-2 border-red-500 pl-4">
                            <div>
                                <p class="text-xs font-black uppercase text-slate-800">SCUBA SOFT FINISH</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase italic underline decoration-red-500/30">New Article - High Interest</p>
                            </div>
                        </div>
                        <p class="text-sm font-black italic text-slate-800">NEW</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm border-l-8 border-l-red-600">
                <h3 class="font-black uppercase text-slate-800 tracking-tighter mb-6 flex items-center gap-3">
                    Critical Orders Alert <span class="bg-red-100 text-red-600 text-[10px] px-3 py-1 rounded-full animate-pulse">4 URGENT</span>
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center bg-red-50/50 p-4 rounded-[1.5rem] border border-red-100">
                        <div>
                            <p class="text-[10px] font-black text-red-600 uppercase">SAP #120399 / UNTUNG TERANG PT</p>
                            <p class="text-xs font-bold text-slate-800">Status: Pending at Dyeing > 3 Days</p>
                        </div>
                        <button class="bg-red-600 text-white text-[10px] font-black px-4 py-2 rounded-xl uppercase">Escalate</button>
                    </div>
                    <div class="flex justify-between items-center bg-amber-50/50 p-4 rounded-[1.5rem] border border-amber-100">
                        <div>
                            <p class="text-[10px] font-black text-amber-600 uppercase">SAP #120405 / BERKAY JAYA CV</p>
                            <p class="text-xs font-bold text-slate-800 italic">Deadline: 2 Days Remaining (Lead Time Alert)</p>
                        </div>
                        <p class="text-[10px] font-black text-amber-500">K/F PROGRESS: 80%</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 p-8 rounded-[3rem] border border-slate-100">
                <h3 class="font-black uppercase text-slate-800 tracking-tighter mb-6 italic">Technical Database Hub</h3>
                <div class="grid grid-cols-2 gap-4">
                    <button class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 hover:border-red-600 transition-all text-left group">
                        <p class="text-[10px] font-black text-slate-400 group-hover:text-red-600 uppercase">Color Match Library</p>
                        <p class="text-sm font-black text-slate-800 mt-1">Search Color History</p>
                    </button>
                    <button class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 hover:border-red-600 transition-all text-left group">
                        <p class="text-[10px] font-black text-slate-400 group-hover:text-red-600 uppercase">Lab Result Archives</p>
                        <p class="text-sm font-black text-slate-800 mt-1">Spec & Fastness Data</p>
                    </button>
                    <button class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 hover:border-red-600 transition-all text-left group">
                        <p class="text-[10px] font-black text-slate-400 group-hover:text-red-600 uppercase">Sample Library</p>
                        <p class="text-sm font-black text-slate-800 mt-1">Booking Physical Sample</p>
                    </button>
                    <button class="bg-slate-900 p-6 rounded-[2rem] shadow-sm text-white hover:bg-red-600 transition-all text-left">
                        <p class="text-[10px] font-black text-slate-400 uppercase">Marketing Analytics</p>
                        <p class="text-sm font-black mt-1">Annual Performance</p>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('pipelineChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                datasets: [{
                    label: 'Incoming Orders (Tons)',
                    data: [120, 190, 150, 280, 210],
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#dc2626',
                    pointBorderWidth: 2
                }, {
                    label: 'Production Output',
                    data: [100, 150, 180, 220, 240],
                    borderColor: '#1e293b',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { display: false }, ticks: { font: { weight: 'bold' } } },
                    x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
                }
            }
        });
    </script>

    <style>
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>
</div>