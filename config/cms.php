<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fixed CMS pages
    |--------------------------------------------------------------------------
    | Each page has sections; each section has typed fields rendered in admin
    | and consumed on the public site via cms($page, $section, $field).
    */
    'pages' => [
        'home' => [
            'title' => 'Home',
            'sections' => [
                'hero' => [
                    'label' => 'Hero',
                    'fields' => [
                        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow'],
                        ['key' => 'headline', 'type' => 'text', 'label' => 'Headline'],
                        ['key' => 'subheadline', 'type' => 'textarea', 'label' => 'Subheadline'],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Background / hero image'],
                        ['key' => 'cta_primary_text', 'type' => 'text', 'label' => 'Primary CTA text'],
                        ['key' => 'cta_primary_url', 'type' => 'url', 'label' => 'Primary CTA URL'],
                        ['key' => 'cta_secondary_text', 'type' => 'text', 'label' => 'Secondary CTA text'],
                        ['key' => 'cta_secondary_url', 'type' => 'url', 'label' => 'Secondary CTA URL'],
                    ],
                ],
                'features' => [
                    'label' => 'Features intro',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Section title'],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Section subtitle'],
                        ['key' => 'item_1_title', 'type' => 'text', 'label' => 'Feature 1 title'],
                        ['key' => 'item_1_body', 'type' => 'textarea', 'label' => 'Feature 1 body'],
                        ['key' => 'item_2_title', 'type' => 'text', 'label' => 'Feature 2 title'],
                        ['key' => 'item_2_body', 'type' => 'textarea', 'label' => 'Feature 2 body'],
                        ['key' => 'item_3_title', 'type' => 'text', 'label' => 'Feature 3 title'],
                        ['key' => 'item_3_body', 'type' => 'textarea', 'label' => 'Feature 3 body'],
                    ],
                ],
            ],
        ],
        'about' => [
            'title' => 'About',
            'sections' => [
                'intro' => [
                    'label' => 'Intro',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'body', 'type' => 'richtext', 'label' => 'Body'],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Image'],
                    ],
                ],
                'mission' => [
                    'label' => 'Mission',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'body', 'type' => 'textarea', 'label' => 'Body'],
                    ],
                ],
            ],
        ],
        'services' => [
            'title' => 'Services',
            'sections' => [
                'intro' => [
                    'label' => 'Page intro',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle'],
                    ],
                ],
            ],
        ],
        'how-it-works' => [
            'title' => 'How It Works',
            'sections' => [
                'intro' => [
                    'label' => 'Intro',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle'],
                    ],
                ],
                'steps' => [
                    'label' => 'Steps',
                    'fields' => [
                        ['key' => 'step_1_title', 'type' => 'text', 'label' => 'Step 1 title'],
                        ['key' => 'step_1_body', 'type' => 'textarea', 'label' => 'Step 1 body'],
                        ['key' => 'step_2_title', 'type' => 'text', 'label' => 'Step 2 title'],
                        ['key' => 'step_2_body', 'type' => 'textarea', 'label' => 'Step 2 body'],
                        ['key' => 'step_3_title', 'type' => 'text', 'label' => 'Step 3 title'],
                        ['key' => 'step_3_body', 'type' => 'textarea', 'label' => 'Step 3 body'],
                    ],
                ],
            ],
        ],
        'careers' => [
            'title' => 'Careers',
            'sections' => [
                'intro' => [
                    'label' => 'Intro',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle'],
                        ['key' => 'body', 'type' => 'richtext', 'label' => 'Body'],
                    ],
                ],
            ],
        ],
        'contact' => [
            'title' => 'Contact',
            'sections' => [
                'intro' => [
                    'label' => 'Intro',
                    'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Subtitle'],
                        ['key' => 'address', 'type' => 'textarea', 'label' => 'Address'],
                        ['key' => 'email', 'type' => 'text', 'label' => 'Email'],
                        ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
                    ],
                ],
            ],
        ],
    ],
];
