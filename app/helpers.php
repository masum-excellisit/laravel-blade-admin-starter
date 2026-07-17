<?php

use App\Models\CmsContent;

if (! function_exists('cms')) {
    /**
     * Read a CMS field value for a fixed page section.
     */
    function cms(string $page, string $section, ?string $field = null, mixed $default = null): mixed
    {
        if ($field === null) {
            return CmsContent::getSection($page, $section);
        }

        return CmsContent::getField($page, $section, $field, $default);
    }
}

if (! function_exists('cms_image')) {
    function cms_image(string $page, string $section, string $field, mixed $default = null): ?string
    {
        $path = cms($page, $section, $field);
        if (! $path) {
            return $default;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }
}
