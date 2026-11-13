@php
    $showcaseLinks = collect($projectLinks)->concat($projects->map(fn ($item) => [$item->name, $item->summary ?? '', $item->monitoring_path ?: route('riset.show', $item)]))
        ->unique(fn ($item) => parse_url($item[2], PHP_URL_PATH))->take(6)->values();
@endphp
<section class="section pale home-projects" id="project" aria-labelledby="project-title"><div class="wrap">
    <div class="section-head"><div><div class="eyebrow">Project CES</div><h2 id="project-title">Eksplorasi sistem kami.</h2><p>Pilih sistem untuk melihat project dan membuka dashboardnya.</p></div></div>
    <div class="project-showcase" data-reveal>
        <div class="project-showcase-tabs" role="tablist" aria-label="Project CES" aria-orientation="vertical">@foreach($showcaseLinks as [$label,$description,$link])<button type="button" role="tab" id="project-choice-{{ $loop->index }}" aria-controls="project-preview-{{ $loop->index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? 0 : -1 }}"><span class="project-choice-number">0{{ $loop->iteration }}</span><span>{{ $label }}<small>{{ $description }}</small></span></button>@endforeach</div>
        <div class="project-showcase-previews">@foreach($showcaseLinks as [$label,$description,$link])
            @php
                $target = parse_url($link, PHP_URL_PATH);
                $matchingProject = $projects->first(fn($item) => $item->monitoring_path === $target || ($target === '/dashboard/smart-water-pump' && $item->slug === 'living-lab'));
                $fallbacks = ['Digital Twin Baterai'=>'digital-twin-baterai.jpg','Digital Twin PV'=>'lab-scale-pv.jpg','HESS'=>'hess-battery-testing-inl.jpg','Monitoring Kumbung Jamur'=>'jamur-kumbung-aktual.jpg'];
                $fallback = $fallbacks[$label] ?? null;
                $previewPhoto = $matchingProject?->thumbnail ? asset('uploads/'.$matchingProject->thumbnail) : ($fallback && file_exists(public_path('uploads/'.$fallback)) ? asset('uploads/'.$fallback) : null);
                if (!$previewPhoto && $label === 'Digital Twin PV') $previewPhoto = 'https://images.unsplash.com/photo-1509391366360-2e959784a276?auto=format&fit=crop&w=1200&q=85';
            @endphp
            <article class="project-showcase-panel" id="project-preview-{{ $loop->index }}" role="tabpanel" aria-labelledby="project-choice-{{ $loop->index }}" @if(!$loop->first) hidden @endif>
                @if($previewPhoto)<img src="{{ $previewPhoto }}" alt="{{ $label }}" loading="lazy">@else<div class="project-showcase-monogram" aria-hidden="true">CES</div>@endif
                <div class="project-showcase-copy"><span>Monitoring · Riwayat · Export</span><h3>{{ $label }}</h3><p>{{ $matchingProject?->summary ?? $description }}</p><a href="{{ $link }}">Buka Dashboard <span aria-hidden="true">↗</span></a></div>
            </article>
        @endforeach</div>
    </div>
</div></section>
