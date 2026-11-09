<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Control Energy System — kabar riset, tim, dan project pemantauan energi Teknik Elektro UNS.">
    <meta name="theme-color" content="#0f172a">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="CES — Control Energy System">
    <meta property="og:title" content="CES | Control Energy System — Teknik Elektro UNS">
    <meta property="og:description" content="Kabar riset, tim, dan project pemantauan energi Teknik Elektro UNS.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('assets/logo/ces-logo.png') }}">
    <meta name="twitter:card" content="summary">
    <title>CES | Control Energy System — Teknik Elektro UNS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('partials.landing-style')
<link rel="stylesheet" href="{{ asset('css/home-news-gallery.css') }}"><script defer src="{{ asset('js/home-news-gallery.js') }}?v={{ file_exists(public_path('js/home-news-gallery.js')) ? filemtime(public_path('js/home-news-gallery.js')) : '20261017' }}"></script><link rel="stylesheet" href="{{ asset('css/home-sections.css') }}"><script defer src="{{ asset('js/home-projects.js') }}"></script>    <link rel="stylesheet" href="{{ asset('css/site-responsive.css') }}?v={{ filemtime(public_path('css/site-responsive.css')) }}">
</head>
<body>
@php
    $projectLinks = [
        ['Digital Twin Baterai', 'Sistem AC, DC, dan BMS Daly', route('dashboard.digital-twin')],
        ['Digital Twin PV', 'Daya, tegangan, arus, cahaya, dan suhu', route('dashboard.lab-scale')],
        ['HESS', 'Baterai utama, superkapasitor, dan kendaraan', route('dashboard.hess')],
        ['Monitoring Kumbung Jamur', 'Suhu, kelembapan, dan kondisi kumbung', route('dashboard.jamur')],
        ['Smart Water Pump', 'Debit air dan kelistrikan pompa', route('dashboard.living-lab')],
    ];
@endphp
<a class="skip" href="#main">Lewati ke konten</a>
@include('partials.landing-header')
<section class="hero" id="beranda" aria-labelledby="hero-title">
    <video class="hero-photo" data-hero-video autoplay muted loop playsinline preload="auto" aria-hidden="true" tabindex="-1">
        <source src="{{ asset('uploads/ces-hero-background.mp4') }}?v={{ file_exists(public_path('uploads/ces-hero-background.mp4')) ? filemtime(public_path('uploads/ces-hero-background.mp4')) : '20261017' }}" type="video/mp4">
    </video>

    <div class="wrap hero-content">
        <h1 id="hero-title" aria-label="Control Energy System"><span class="hero-word">Control</span><span class="hero-word">Energy</span><span class="hero-word">System</span></h1>
        <div class="hero-copy"><div class="values"><span>Inovatif</span><span>Terintegrasi</span><span>Berkelanjutan</span></div><h2>Riset dan teknologi untuk pengelolaan energi yang lebih cerdas.</h2><p>Teknik Elektro · Universitas Sebelas Maret<span class="hero-description"><br>Pemantauan dan kendali energi berbasis Internet of Things.</span></p><a class="hero-link" href="{{ route('riset.index') }}">Jelajahi Kabar Riset <span aria-hidden="true">↗</span></a><button type="button" class="hero-video-toggle" data-hero-video-toggle aria-label="Putar video" title="Putar video" hidden><span data-hero-video-play>@include('partials.ui-icon', ['icon'=>'play'])</span><span data-hero-video-pause hidden>@include('partials.ui-icon', ['icon'=>'pause'])</span></button></div>
    </div>
    <div class="wrap hero-meta"><span>CES · Research &amp; Innovation</span><span>Dibimbing oleh Dr. Ir. Hari Maghfiroh, S.T., M.Eng., M.Sc.</span></div>
</section>
<main id="main">
    <div class="home-overview">
    <section class="section wrap" id="tentang"><div class="about-grid"><div><div class="eyebrow">Tentang Kami</div><h2><span class="about-heading-full">Menghubungkan riset,<br>data, dan energi.</span><span class="about-heading-short">Tentang Kami</span></h2><div class="tags"><span>Internet of Things</span><span>Sistem Kendali</span><span>Energi</span></div></div><div><p><span class="about-copy-full">Control Energy System merupakan wadah riset pemantauan dan kendali energi berbasis IoT di Teknik Elektro, Universitas Sebelas Maret.</span><span class="about-copy-short">Riset pemantauan dan kendali energi berbasis IoT di Teknik Elektro UNS.</span></p><p>Di bawah bimbingan Dr. Ir. Hari Maghfiroh, S.T., M.Eng., M.Sc., kami mengembangkan ide dan mengeksplorasi teknologi untuk pengelolaan energi.</p><div class="facts"><div class="fact"><strong>{{ $projects->count() }}</strong><span>Project dipublikasikan</span></div><div class="fact"><strong>{{ $teamMembers->count() }}</strong><span>Anggota tim</span></div></div>
        @include('partials.home-team-preview')
    </div></div></section>
    @include('partials.home-news-gallery')
    </div>
    @include('partials.home-team')
    @include('partials.home-projects')
</main>
@include('partials.landing-footer')
@include('partials.landing-script')
<script defer src="{{ asset('js/hero-video.js') }}?v={{ file_exists(public_path('js/hero-video.js')) ? filemtime(public_path('js/hero-video.js')) : '20261017' }}"></script>
</body>
</html>
