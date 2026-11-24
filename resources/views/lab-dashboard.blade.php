<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Digital Twin PV · Monitoring | CES</title>
    @include('partials.landing-style')
    <link rel="stylesheet" href="{{ asset('css/lab-monitoring.css') }}?v={{ file_exists(public_path('css/lab-monitoring.css')) ? filemtime(public_path('css/lab-monitoring.css')) : '20261017' }}">
    <script defer src="{{ asset('vendor/lab/chart.umd.js') }}"></script>
    <script defer src="{{ asset('js/lab-monitoring.js') }}?v={{ file_exists(public_path('js/lab-monitoring.js')) ? filemtime(public_path('js/lab-monitoring.js')) : 'live-20261021' }}"></script>
    <script defer src="{{ asset('js/lab-export.js') }}?v=irradiansi-20261020"></script>
    <link rel="stylesheet" href="{{ asset('css/site-responsive.css') }}?v={{ filemtime(public_path('css/site-responsive.css')) }}">
    @include('partials.pv-monitoring-layout')
</head>
<body class="lab-body">
@include('partials.landing-header')
<main class="lab-page wrap" id="lab-app" data-vendor="{{ asset('vendor/lab') }}" data-live="{{ route('lab.monitoring') }}" data-readings="{{ route('lab.readings') }}" data-toggle="{{ url('/dashboard/lab-scale/toggle') }}">
@include('partials.mobile-project-picker')
    <nav class="lab-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Beranda</a> / Digital Twin PV / Monitoring</nav>
    <div class="lab-heading monitoring-heading"><div><h1>Digital Twin PV</h1><p>Pemantauan daya, tegangan, arus, cahaya, dan suhu sistem fotovoltaik.</p>@if($project)<a class="lab-link" href="{{ route('dashboard.pengaturan', $project->slug) }}" aria-label="Masuk ke kontrol project"><span class="monitoring-control-full" aria-hidden="true">Masuk ke kontrol project →</span><span class="monitoring-control-short" aria-hidden="true">Kontrol @include('partials.ui-icon', ['icon'=>'external'])</span></a>@endif</div><div class="lab-connection"><strong id="connection">Memeriksa koneksi…</strong><small id="last-reading">Menunggu pembacaan perangkat</small></div></div>

    @if($errors->any())<p class="lab-error" role="alert">{{ $errors->first() }}</p>@endif
    @include('partials.lab-device-health')
    <div class="lab-grid pv-monitoring-grid" id="monitoring">
        <div class="lab-main-column">
            <section class="lab-card lab-overview">
                <div class="lab-photo">
                    <img src="{{ file_exists(public_path('uploads/lab-scale-pv.jpg')) ? asset('uploads/lab-scale-pv.jpg') : ($project?->thumbnail ? asset('uploads/' . $project->thumbnail) : 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1600&q=85') }}" alt="{{ $project?->thumbnail ? 'Perangkat project Digital Twin PV' : 'Ilustrasi panel surya' }}">
                    <div class="lab-photo-copy"><h2>Kondisi Digital Twin PV</h2><p>Daya PV saat ini</p><strong><span data-reading="daya">—</span> <small>W</small></strong></div>
                    @unless($project?->thumbnail || file_exists(public_path('uploads/lab-scale-pv.jpg')))<span class="lab-photo-caption">Ilustrasi panel surya</span>@endunless
                </div>
                <div class="lab-metrics"><div>Tegangan<strong><span data-reading="tegangan">—</span> V</strong></div><div>Arus<strong><span data-reading="arus">—</span> A</strong></div><div>Estimasi irradiansi<strong><span data-reading="irradiansi">—</span> W/m²</strong></div><div>Suhu<strong><span data-reading="suhu">—</span> °C</strong></div></div>
            </section>

            <section class="lab-card pv-prediction" aria-labelledby="pv-prediction-title">
                <div class="pv-prediction-main">
                    <div class="pv-prediction-summary">
                        <div class="pv-prediction-heading"><h2 id="pv-prediction-title">Deteksi Anomali Daya</h2><p>Daya aktual dibandingkan dengan prediksi model SS2.</p></div>
                        <div class="pv-prediction-values"><div><small>Aktual</small><strong><span data-reading="daya">—</span> W</strong></div><div><small>Prediksi</small><strong><span data-reading="ss2_prediksi">—</span> W</strong></div><div><small>Selisih</small><strong><span data-reading="ss2_selisih">—</span> W</strong></div></div>
                        <span class="pv-prediction-status" data-reading="ss2_status">Menunggu data pembanding</span>
                    </div>
                    <div class="pv-prediction-chart"><canvas id="anomaly-chart" role="img" aria-label="Grafik interaktif daya aktual dan prediksi model"></canvas><span class="sensor-chart-empty">Menunggu data pembanding</span></div>
                </div>
                <div class="pv-prediction-explainer">
                    <div><strong>Cara membaca hasil</strong><dl><dt>Model yang digunakan</dt><dd>SS2 · state-space diskrit</dd><dt>Interval model</dt><dd>2 detik</dd><dt>Input</dt><dd>Estimasi irradiansi + suhu</dd><dt>Output</dt><dd>Prediksi daya</dd><dt>Fit data estimasi</dt><dd>85,26% · RMSE 1,56 W</dd></dl></div>
                    <div class="pv-prediction-thresholds"><strong>Batas status</strong><span class="is-normal">Normal ≤ 3,12 W</span><span class="is-observe">Perlu diamati 3,13–4,68 W</span><span class="is-candidate">Kandidat anomali &gt; 4,68 W</span><span class="is-rule">Anomali terdeteksi setelah 3 pembacaan berturut-turut</span></div>
                </div>
                <p class="pv-prediction-note">Ambang awal; dikalibrasi ulang setelah validasi residual.</p>
            </section>

    <section class="pv-charts" aria-label="Grafik pembacaan Digital Twin PV">
        <div class="lab-card pv-chart" data-pv-color="power"><h2>Grafik daya</h2><div class="lab-chart"><canvas id="production-chart" role="img" aria-label="Grafik daya PV"></canvas><span class="sensor-chart-empty">Belum ada pembacaan</span></div></div>
        <div class="lab-card pv-chart" data-pv-color="voltage"><h2>Grafik tegangan</h2><div class="lab-chart"><canvas id="voltage-chart" role="img" aria-label="Grafik tegangan PV"></canvas><span class="sensor-chart-empty">Belum ada pembacaan</span></div></div>
        <div class="lab-card pv-chart" data-pv-color="current"><h2>Grafik arus</h2><div class="lab-chart"><canvas id="current-chart" role="img" aria-label="Grafik arus PV"></canvas><span class="sensor-chart-empty">Belum ada pembacaan</span></div></div>
        <div class="lab-card pv-chart" data-pv-color="light"><h2>Grafik estimasi irradiansi</h2><div class="lab-chart"><canvas id="light-chart" role="img" aria-label="Grafik estimasi irradiansi PV"></canvas><span class="sensor-chart-empty">Belum ada pembacaan</span></div></div>
        <div class="lab-card pv-chart" data-pv-color="temperature"><h2>Grafik suhu</h2><div class="lab-chart"><canvas id="temperature-chart" role="img" aria-label="Grafik suhu PV"></canvas><span class="sensor-chart-empty">Belum ada pembacaan</span></div></div>
    </section><p id="chart-message" role="status"></p>
        </div>
        <aside class="lab-side-column">
            @include('partials.project-research-card', ['researchTitle'=>'Kabar Riset Digital Twin PV'])
            @include('partials.dashboard-project-team', ['teamTitle' => 'Tim Digital Twin PV'])
        </aside>
    </div>
    <section class="lab-card lab-history" id="history">
        <div class="lab-card-heading"><h2>Riwayat pembacaan</h2><details class="lab-export-menu"><summary class="lab-button">Export <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></summary><div>@foreach(['csv'=>'CSV','xlsx'=>'Excel (.xlsx)','pdf'=>'PDF','png'=>'PNG','docx'=>'Word (.docx)'] as $format=>$label)<button type="button" data-export="{{ $format }}">{{ $label }}</button>@endforeach<small>Grafik + seluruh tabel dalam rentang</small></div></details></div>
        <form class="lab-filters" method="get" action="{{ route('dashboard.lab-scale') }}#history"><label>Dari<input type="datetime-local" name="from" value="{{ $from->format('Y-m-d\TH:i') }}" required></label><label>Sampai<input type="datetime-local" name="to" value="{{ $to->format('Y-m-d\TH:i') }}" required></label><button class="lab-button">Terapkan</button><a href="{{ route('dashboard.lab-scale') }}#history">Hari ini</a><small>Zona waktu: {{ config('app.timezone') }}</small></form>
        <p id="export-message" role="status" aria-live="polite"></p>
        <p id="history-message" role="status"></p>
        <div class="lab-table-scroll"><table><thead><tr><th>Waktu</th><th>Tegangan (V)</th><th>Arus (A)</th><th>Daya (W)</th><th>Suhu (°C)</th><th>Est. irradiansi (W/m²)</th></tr></thead><tbody id="lab-history-body">@include('partials.lab-history-rows')</tbody></table></div>
        <div class="lab-pagination" id="lab-history-pagination">@include('partials.lab-history-pagination')</div>
    </section>
</main>
@include('partials.landing-footer')
@include('partials.landing-script')
</body>
</html>
