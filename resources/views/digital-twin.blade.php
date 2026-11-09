<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite('resources/css/app.css')

    <title>Digital Twin Baterai | CES</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    @include('partials.landing-style')
    <link rel="stylesheet" href="{{ asset('css/lab-monitoring.css') }}?v={{ file_exists(public_path('css/lab-monitoring.css')) ? filemtime(public_path('css/lab-monitoring.css')) : '20261017' }}">

    <link rel="stylesheet" href="{{ asset('css/digital-twin.css') }}?v={{ file_exists(public_path('css/digital-twin.css')) ? filemtime(public_path('css/digital-twin.css')) : '20261017' }}">

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
    <script src="{{ asset('vendor/lab/chart.umd.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script defer src="{{ asset('js/dashboard-export.js') }}"></script><script defer src="{{ asset('js/dashboard-history.js') }}?v={{ file_exists(public_path('js/dashboard-history.js')) ? filemtime(public_path('js/dashboard-history.js')) : '20261017' }}"></script>    <link rel="stylesheet" href="{{ asset('css/site-responsive.css') }}?v={{ filemtime(public_path('css/site-responsive.css')) }}">
</head>

<body class="lab-body">
    @include('partials.landing-header')

    <div class="digital-twin-page" id="app-main">
        <div class="lab-page wrap">
@include('partials.mobile-project-picker')

            <nav class="lab-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Beranda</a> / Digital Twin Baterai / Monitoring</nav>
            
            <div class="dt-header monitoring-heading flex flex-col lg:flex-row lg:items-end justify-between mb-8 gap-5">
                <div class="flex items-center gap-4">
                    <div class="brand-mark shrink-0">
                        <svg width="24" height="24" fill="white" viewBox="0 0 16 16"><path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/></svg>
                    </div>
                    <div>

                        <h1 class="text-3xl font-extrabold tracking-tight text-primary">Digital Twin Baterai</h1>
                        <p class="text-sm text-secondary mt-2 font-medium">Pemantauan sistem AC (TEM055), DC (PZEM-017), dan BMS Daly</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="panel px-5 py-3 flex items-center gap-3">
                        <div id="mqtt-indicator" data-connection-state="offline" class="pulse-dot w-3 h-3 rounded-full text-red-500" style="background-color:currentColor; box-shadow:0 0 8px currentColor;"></div>
                        <div>
                            <p class="text-xs text-faint font-semibold uppercase tracking-wider">Koneksi MQTT</p>
                            <p id="mqtt-status" data-connection-state="offline" class="text-sm font-bold text-primary">Menghubungkan...</p>
                        </div>
                    </div>

                    
                    <div id="battery-system-status" data-connection-state="offline" class="battery-system-note"><span id="battery-system-label">Sistem sedang tidak dinyalakan</span></div>
                    
                </div>
            </div>

            
            <div class="dt-layout"><div class="dt-main">
            <section class="dt-battery-card">
                {{-- Upload uploads/digital-twin-baterai.jpg to show a photo; otherwise the dark card background is used. --}}
                @if(file_exists(public_path('uploads/digital-twin-baterai.jpg')))<img src="{{ asset('uploads/digital-twin-baterai.jpg') }}" alt="Perangkat Digital Twin Baterai CES">@endif
                <div class="dt-battery-copy"><h2>Kondisi baterai</h2><p>State of charge</p><strong><span data-bms-mirror="rt-bmsSoc">—</span><small> %</small></strong><p id="battery-report-status">Menunggu laporan BMS</p></div>
            </section>
            <div class="flex gap-4 border-b border-gray-200 mb-8 overflow-x-auto">
                <button id="btn-ac" class="tab-btn active" onclick="switchTab('ac')">
                    <div class="flex items-center gap-2">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M5.52.359A.5.5 0 0 1 6 0h4a.5.5 0 0 1 .474.658L8.694 6H12.5a.5.5 0 0 1 .395.807l-7 9a.5.5 0 0 1-.873-.454L6.823 9.5H3.5a.5.5 0 0 1-.48-.641z"/></svg>
                        Sistem AC (TEM055)
                        
                        <span id="dot-tab-ac" class="w-2.5 h-2.5 rounded-full ml-1.5 transition-colors duration-300" style="background-color: #ef4444; box-shadow: 0 0 6px #ef4444;" data-connection-state="offline" title="Offline"></span>
                    </div>
                </button>
                <button id="btn-dc" class="tab-btn" onclick="switchTab('dc')">
                    <div class="flex items-center gap-2">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2zm8.5 7.5h-5a.5.5 0 0 1 0-1h5a.5.5 0 0 1 0 1z"/></svg>
                        Sistem DC (PZEM-017)
                        
                        <span id="dot-tab-dc" class="w-2.5 h-2.5 rounded-full ml-1.5 transition-colors duration-300" style="background-color: #ef4444; box-shadow: 0 0 6px #ef4444;" data-connection-state="offline" title="Offline"></span>
                    </div>
                </button>
                <button id="btn-bms" class="tab-btn" onclick="switchTab('bms')">
                    <div class="flex items-center gap-2">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M2 3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H2zm12 1.5a.5.5 0 0 1 .5-.5h.5a1.5 1.5 0 0 1 1.5 1.5v5a1.5 1.5 0 0 1-1.5 1.5h-.5a.5.5 0 0 1-.5-.5v-7zM3 6.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-5z"/></svg>
                        BMS Daly R24PK
                        
                        <span id="dot-tab-bms" class="w-2.5 h-2.5 rounded-full ml-1.5 transition-colors duration-300" style="background-color: #ef4444; box-shadow: 0 0 6px #ef4444;" data-connection-state="offline" title="Offline"></span>
                    </div>
                </button>
            </div>

            
            
            
            <div id="tab-ac" class="tab-content active">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    
                    <div class="panel-raised p-6 card-hover relative overflow-hidden">
                        <div class="blob -right-4 -top-4 w-24 h-24" style="background:#f43f5e;"></div>
                        <div class="flex items-center justify-between mb-6 relative z-10"><div class="flex items-center gap-2"><div class="w-2 h-6 rounded-full" style="background:#f43f5e;"></div><h3 class="text-sm font-bold text-faint uppercase tracking-widest">Fasa A (R)</h3></div></div>
                        <div class="space-y-4 relative z-10">
                            <div class="flex justify-between items-end"><span class="text-sm text-secondary font-medium">Tegangan</span><div class="text-right"><span id="rt-vA" class="text-3xl font-extrabold text-primary mono">—</span> <span class="text-faint font-semibold">V</span></div></div>
                            <div class="w-full h-px divider"></div>
                            <div class="flex justify-between items-end"><span class="text-sm text-secondary font-medium">Arus</span><div class="text-right"><span id="rt-cA" class="text-3xl font-extrabold text-primary mono">—</span> <span class="text-faint font-semibold">A</span></div></div>
                        </div>
                    </div>
                    
                    <div class="panel-raised p-6 card-hover relative overflow-hidden">
                        <div class="blob -right-4 -top-4 w-24 h-24" style="background:#f59e0b;"></div>
                        <div class="flex items-center gap-2 mb-6 relative z-10"><div class="w-2 h-6 rounded-full" style="background:#f59e0b;"></div><h3 class="text-sm font-bold text-faint uppercase tracking-widest">Fasa B (S)</h3></div>
                        <div class="space-y-4 relative z-10">
                            <div class="flex justify-between items-end"><span class="text-sm text-secondary font-medium">Tegangan</span><div class="text-right"><span id="rt-vB" class="text-3xl font-extrabold text-primary mono">—</span> <span class="text-faint font-semibold">V</span></div></div>
                            <div class="w-full h-px divider"></div>
                            <div class="flex justify-between items-end"><span class="text-sm text-secondary font-medium">Arus</span><div class="text-right"><span id="rt-cB" class="text-3xl font-extrabold text-primary mono">—</span> <span class="text-faint font-semibold">A</span></div></div>
                        </div>
                    </div>
                    
                    <div class="panel-raised p-6 card-hover relative overflow-hidden">
                        <div class="blob -right-4 -top-4 w-24 h-24" style="background:#3b82f6;"></div>
                        <div class="flex items-center gap-2 mb-6 relative z-10"><div class="w-2 h-6 rounded-full" style="background:#3b82f6;"></div><h3 class="text-sm font-bold text-faint uppercase tracking-widest">Fasa C (T)</h3></div>
                        <div class="space-y-4 relative z-10">
                            <div class="flex justify-between items-end"><span class="text-sm text-secondary font-medium">Tegangan</span><div class="text-right"><span id="rt-vC" class="text-3xl font-extrabold text-primary mono">—</span> <span class="text-faint font-semibold">V</span></div></div>
                            <div class="w-full h-px divider"></div>
                            <div class="flex justify-between items-end"><span class="text-sm text-secondary font-medium">Arus</span><div class="text-right"><span id="rt-cC" class="text-3xl font-extrabold text-primary mono">—</span> <span class="text-faint font-semibold">A</span></div></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 mb-4 mt-8"><div class="w-1.5 h-4 rounded-full" style="background: var(--text);"></div><h3 class="text-base font-bold text-primary">Tren Tegangan Fasa (Volt)</h3></div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                    <div class="panel p-5"><div class="relative h-48 w-full"><canvas id="chart-va"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-48 w-full"><canvas id="chart-vb"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-48 w-full"><canvas id="chart-vc"></canvas></div></div>
                </div>

                <div class="flex items-center gap-2 mb-4"><div class="w-1.5 h-4 rounded-full" style="background: var(--text);"></div><h3 class="text-base font-bold text-primary">Tren Arus Fasa (Ampere)</h3></div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                    <div class="panel p-5"><div class="relative h-48 w-full"><canvas id="chart-ia"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-48 w-full"><canvas id="chart-ib"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-48 w-full"><canvas id="chart-ic"></canvas></div></div>
                </div>

                <div class="panel overflow-hidden mb-10">
                    <div class="px-6 py-4 panel-header-row flex items-center justify-between gap-3 flex-wrap">
                        <h3 class="text-sm font-bold text-primary uppercase tracking-wider">Log Historis AC</h3>
                        <div class="export-actions">
                            <button type="button" class="btn-export excel" onclick="exportExcel('ac')"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM4.68 12.157a.5.5 0 0 1-.512-.858L6.53 9.5 4.168 7.7a.5.5 0 1 1 .615-.792L7 8.68l2.217-1.772a.5.5 0 1 1 .615.792L7.47 9.5l2.362 1.799a.5.5 0 0 1-.512.858L7 10.32l-2.32 1.837z"/></svg> Excel</button>
                            <button type="button" class="btn-export pdf" onclick="exportPDF('ac')"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM4.5 12h1v-1h.5a1 1 0 1 0 0-2h-1.5v3zm1-2h.5a.25.25 0 0 1 0 .5h-.5v-.5zM7.5 12.5V9h1.25a1.25 1.25 0 0 1 0 2.5H8.5v1h-1zm1-2v-.5h.25a.25.25 0 0 1 0 .5H8.5zM10.5 12.5V9H12v.75h-.5v.75h.5v.75h-.5v1.25h-1z"/></svg> PDF</button>
                        </div>
                    </div>
                    <div class="table-container p-0">
                        <table class="w-full text-sm text-left text-secondary">
                            <thead class="sticky-head text-xs text-faint uppercase sticky top-0 shadow-sm z-10">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Waktu</th>
                                    <th class="px-6 py-4 font-bold" style="color:#f43f5e;">VA (V)</th><th class="px-6 py-4 font-bold" style="color:#f59e0b;">VB (V)</th><th class="px-6 py-4 font-bold" style="color:#3b82f6;">VC (V)</th>
                                    <th class="px-6 py-4 font-bold" style="color:#f43f5e;">IA (A)</th><th class="px-6 py-4 font-bold" style="color:#f59e0b;">IB (A)</th><th class="px-6 py-4 font-bold" style="color:#3b82f6;">IC (A)</th>
                                </tr>
                            </thead>
                            <tbody id="table-body-ac" class="divide-y"><tr><td colspan="7" class="px-6 py-8 text-center text-faint font-medium">Sinkronisasi ke database...</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>

            
            
            
            <div id="tab-dc" class="tab-content">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
                    <div class="panel-raised p-6 text-center card-hover relative overflow-hidden">
                        <div class="blob -right-6 -top-6 w-20 h-20" style="background:#10b981;"></div>
                        <p class="text-xs font-bold text-faint mb-1 uppercase tracking-widest relative z-10 mt-2">Tegangan</p>
                        <h3 class="text-3xl font-extrabold text-primary mono relative z-10"><span id="rt-dcV">—</span> <span class="text-base font-medium text-faint">V</span></h3>
                    </div>
                    <div class="panel-raised p-6 text-center card-hover relative overflow-hidden">
                        <div class="blob -right-6 -top-6 w-20 h-20" style="background:#f97316;"></div>
                        <p class="text-xs font-bold text-faint mb-1 uppercase tracking-widest relative z-10 mt-2">Arus</p>
                        <h3 class="text-3xl font-extrabold text-primary mono relative z-10"><span id="rt-dcA">—</span> <span class="text-base font-medium text-faint">A</span></h3>
                    </div>
                    <div class="panel-raised p-6 text-center card-hover relative overflow-hidden">
                        <div class="blob -right-6 -top-6 w-20 h-20" style="background:#8b5cf6;"></div>
                        <p class="text-xs font-bold text-faint mb-1 uppercase tracking-widest relative z-10 mt-2">Daya</p>
                        <h3 class="text-3xl font-extrabold text-primary mono relative z-10"><span id="rt-dcW">—</span> <span class="text-base font-medium text-faint">W</span></h3>
                    </div>
                    <div class="panel-raised p-6 text-center card-hover relative overflow-hidden">
                        <div class="blob -right-6 -top-6 w-20 h-20" style="background:#0d9488;"></div>
                        <p class="text-xs font-bold text-faint mb-1 uppercase tracking-widest relative z-10 mt-2">Energi</p>
                        <h3 class="text-3xl font-extrabold text-primary mono relative z-10"><span id="rt-dcWh">—</span> <span class="text-base font-medium text-faint">Wh</span></h3>
                    </div>
                </div>

                <div class="flex items-center gap-2 mb-4 mt-8"><div class="w-1.5 h-4 rounded-full" style="background: var(--text);"></div><h3 class="text-base font-bold text-primary">Tren Parameter DC Terpisah</h3></div>
                <div class="dt-bms-charts grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                    <div class="panel p-5"><div class="relative h-56 w-full"><canvas id="chart-dc-v"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-56 w-full"><canvas id="chart-dc-i"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-56 w-full"><canvas id="chart-dc-w"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-56 w-full"><canvas id="chart-dc-wh"></canvas></div></div>
                </div>

                <div class="panel overflow-hidden mb-10">
                    <div class="px-6 py-4 panel-header-row flex items-center justify-between gap-3 flex-wrap">
                        <h3 class="text-sm font-bold text-primary uppercase tracking-wider">Log Historis DC</h3>
                        <div class="export-actions">
                            <button type="button" class="btn-export excel" onclick="exportExcel('dc')"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM4.68 12.157a.5.5 0 0 1-.512-.858L6.53 9.5 4.168 7.7a.5.5 0 1 1 .615-.792L7 8.68l2.217-1.772a.5.5 0 1 1 .615.792L7.47 9.5l2.362 1.799a.5.5 0 0 1-.512.858L7 10.32l-2.32 1.837z"/></svg> Excel</button>
                            <button type="button" class="btn-export pdf" onclick="exportPDF('dc')"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM4.5 12h1v-1h.5a1 1 0 1 0 0-2h-1.5v3zm1-2h.5a.25.25 0 0 1 0 .5h-.5v-.5zM7.5 12.5V9h1.25a1.25 1.25 0 0 1 0 2.5H8.5v1h-1zm1-2v-.5h.25a.25.25 0 0 1 0 .5H8.5zM10.5 12.5V9H12v.75h-.5v.75h.5v.75h-.5v1.25h-1z"/></svg> PDF</button>
                        </div>
                    </div>
                    <div class="table-container p-0">
                        <table class="w-full text-sm text-left text-secondary">
                            <thead class="sticky-head text-xs text-faint uppercase sticky top-0 shadow-sm z-10">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Waktu</th>
                                    <th class="px-6 py-4 font-bold" style="color:#10b981;">Tegangan (V)</th><th class="px-6 py-4 font-bold" style="color:#f97316;">Arus (A)</th>
                                    <th class="px-6 py-4 font-bold" style="color:#8b5cf6;">Daya (W)</th><th class="px-6 py-4 font-bold" style="color:#0d9488;">Energi (Wh)</th>
                                </tr>
                            </thead>
                            <tbody id="table-body-dc" class="divide-y"><tr><td colspan="5" class="px-6 py-8 text-center text-faint font-medium">Sinkronisasi ke database...</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>

            
            
            
            <div id="tab-bms" class="tab-content">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
                    <div class="panel-raised p-6 text-center card-hover relative overflow-hidden">
                        <div class="blob -right-6 -top-6 w-20 h-20" style="background:#3b82f6;"></div>
                        <p class="text-xs font-bold text-faint mb-1 uppercase tracking-widest relative z-10 mt-2">Tegangan</p>
                        <h3 class="text-3xl font-extrabold text-primary mono relative z-10"><span id="rt-bmsV">—</span> <span class="text-base font-medium text-faint">V</span></h3>
                    </div>
                    <div class="panel-raised p-6 text-center card-hover relative overflow-hidden">
                        <div class="blob -right-6 -top-6 w-20 h-20" style="background:#f97316;"></div>
                        <p class="text-xs font-bold text-faint mb-1 uppercase tracking-widest relative z-10 mt-2">Arus</p>
                        <h3 class="text-3xl font-extrabold text-primary mono relative z-10"><span id="rt-bmsA">—</span> <span class="text-base font-medium text-faint">A</span></h3>
                    </div>
                    <div class="panel-raised p-6 text-center card-hover relative overflow-hidden">
                        <div class="blob -right-6 -top-6 w-20 h-20" style="background:#10b981;"></div>
                        <p class="text-xs font-bold text-faint mb-1 uppercase tracking-widest relative z-10 mt-2">SOC (Kapasitas)</p>
                        <h3 class="text-3xl font-extrabold text-primary mono relative z-10"><span id="rt-bmsSoc">—</span> <span class="text-base font-medium text-faint">%</span></h3>
                    </div>
                    <div class="panel-raised p-6 text-center card-hover relative overflow-hidden">
                        <div class="blob -right-6 -top-6 w-20 h-20" style="background:#f43f5e;"></div>
                        <p class="text-xs font-bold text-faint mb-1 uppercase tracking-widest relative z-10 mt-2">Temperatur</p>
                        <h3 class="text-3xl font-extrabold text-primary mono relative z-10"><span id="rt-bmsTemp">—</span> <span class="text-base font-medium text-faint">°C</span></h3>
                    </div>
                </div>

                <div class="flex items-center gap-2 mb-4 mt-8"><div class="w-1.5 h-4 rounded-full" style="background: var(--text);"></div><h3 class="text-base font-bold text-primary">Tren Historis Sistem Baterai</h3></div>
                <div class="dt-bms-charts grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                    <div class="panel p-5"><div class="relative h-56 w-full"><canvas id="chart-bms-v"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-56 w-full"><canvas id="chart-bms-i"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-56 w-full"><canvas id="chart-bms-soc"></canvas></div></div>
                    <div class="panel p-5"><div class="relative h-56 w-full"><canvas id="chart-bms-temp"></canvas></div></div>
                </div>

                <div class="panel overflow-hidden mb-10">
                    <div class="px-6 py-4 panel-header-row flex items-center justify-between gap-3 flex-wrap">
                        <h3 class="text-sm font-bold text-primary uppercase tracking-wider">Log Historis BMS</h3>
                        <div class="export-actions">
                            <button type="button" class="btn-export excel" onclick="exportExcel('bms')"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM4.68 12.157a.5.5 0 0 1-.512-.858L6.53 9.5 4.168 7.7a.5.5 0 1 1 .615-.792L7 8.68l2.217-1.772a.5.5 0 1 1 .615.792L7.47 9.5l2.362 1.799a.5.5 0 0 1-.512.858L7 10.32l-2.32 1.837z"/></svg> Excel</button>
                            <button type="button" class="btn-export pdf" onclick="exportPDF('bms')"><svg viewBox="0 0 16 16" fill="currentColor"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM4.5 12h1v-1h.5a1 1 0 1 0 0-2h-1.5v3zm1-2h.5a.25.25 0 0 1 0 .5h-.5v-.5zM7.5 12.5V9h1.25a1.25 1.25 0 0 1 0 2.5H8.5v1h-1zm1-2v-.5h.25a.25.25 0 0 1 0 .5H8.5zM10.5 12.5V9H12v.75h-.5v.75h.5v.75h-.5v1.25h-1z"/></svg> PDF</button>
                        </div>
                    </div>
                    <div class="table-container p-0">
                        <table class="w-full text-sm text-left text-secondary">
                            <thead class="sticky-head text-xs text-faint uppercase sticky top-0 shadow-sm z-10">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Waktu</th>
                                    <th class="px-6 py-4 font-bold" style="color:#3b82f6;">Tegangan (V)</th><th class="px-6 py-4 font-bold" style="color:#f97316;">Arus (A)</th>
                                    <th class="px-6 py-4 font-bold" style="color:#10b981;">SOC (%)</th><th class="px-6 py-4 font-bold" style="color:#f43f5e;">Suhu (°C)</th>
                                </tr>
                            </thead>
                            <tbody id="table-body-bms" class="divide-y"><tr><td colspan="5" class="px-6 py-8 text-center text-faint font-medium">Sinkronisasi ke database...</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>

            </div><aside class="dt-aside">
                @include('partials.project-research-card', ['researchTitle'=>'Kabar Riset Baterai'])
                @include('partials.dashboard-project-team', ['teamTitle' => 'Tim Baterai'])
            </aside></div>
        </div>
    </div>

    
    

    
    <script src="{{ asset('js/digital-twin-tabs.js') }}"></script>

    
    <script src="{{ asset('js/digital-twin-mqtt.js') }}"></script>

    
    <script src="{{ asset('js/digital-twin-charts.js') }}"></script>
<div class="wrap">@include('partials.dashboard-history', ['historyProject'=>'digital-twin'])</div>
@include('partials.landing-footer')
@include('partials.landing-script')
<script src="{{ asset('js/digital-twin-presentation.js') }}"></script>
<script src="{{ asset('js/dashboard-chart-style.js') }}"></script>
</body>
</html>