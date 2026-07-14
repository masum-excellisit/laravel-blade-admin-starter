@php
    $s = $settings ?? [];
    $primary = $s['theme_primary'] ?? '#6366f1';
    $secondary = $s['theme_secondary'] ?? '#8b5cf6';
    $accent = $s['theme_accent'] ?? '#ec4899';
    $sidebar = $s['theme_sidebar'] ?? '#0f172a';
@endphp
<style>
    :root {
        --c-primary: {{ $primary }};
        --c-secondary: {{ $secondary }};
        --c-accent: {{ $accent }};
        --grad-from: {{ $primary }};
        --grad-to: {{ $secondary }};
        --sidebar-bg: {{ $sidebar }};
    }
</style>
