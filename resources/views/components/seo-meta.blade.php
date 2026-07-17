@props([
    'title' => null,
    'description' => null,
    'ogImage' => null,
    'canonical' => null,
])

@php
    $siteName = $settings['site_name'] ?? config('app.name');
    $metaTitle = $title ?: $siteName;
    $metaDescription = $description ?: ($settings['site_tagline'] ?? '');
    $canonicalUrl = $canonical ?: url()->current();
    $imageUrl = $ogImage;

    if ($imageUrl && ! \Illuminate\Support\Str::startsWith($imageUrl, ['http://', 'https://', '//'])) {
        $imageUrl = url($imageUrl);
    }
@endphp

@if($metaDescription)
<meta name="description" content="{{ $metaDescription }}">
@endif
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $metaTitle }}">
@if($metaDescription)
<meta property="og:description" content="{{ $metaDescription }}">
@endif
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if($imageUrl)
<meta property="og:image" content="{{ $imageUrl }}">
@endif
<meta name="twitter:card" content="{{ $imageUrl ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $metaTitle }}">
@if($metaDescription)
<meta name="twitter:description" content="{{ $metaDescription }}">
@endif
@if($imageUrl)
<meta name="twitter:image" content="{{ $imageUrl }}">
@endif
<link rel="canonical" href="{{ $canonicalUrl }}">
