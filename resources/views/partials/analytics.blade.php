@php
    $gtmId = trim((string) ($settings['analytics_gtm_id'] ?? ''));
    $ga4Id = trim((string) ($settings['analytics_ga4_id'] ?? ''));
    $plausibleDomain = trim((string) ($settings['analytics_plausible_domain'] ?? ''));
@endphp

@if($gtmId)
<script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer',@js($gtmId));
</script>
@endif

@if($ga4Id)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga4Id) }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', @js($ga4Id));
</script>
@endif

@if($plausibleDomain)
<script defer data-domain="{{ $plausibleDomain }}" src="https://plausible.io/js/script.js"></script>
@endif
