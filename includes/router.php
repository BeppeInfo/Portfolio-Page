<?php
/**
 * Router — Parse URL and dispatch to the correct view.
 */

/**
 * Dispatch the current route to the appropriate view.
 *
 * @return array ['view' => string, 'data' => array]
 */
function dispatch(): array
{
    $route = get_route();
    $page = $route['page'];
    $lang = get_language();

    // Load shared data
    $site = load_config('site') ?: [];
    $strings = load_strings($lang);

    $sharedData = [
        'site' => $site,
        'strings' => $strings,
        'lang' => $lang,
        'current_route' => $route,
        'current_path' => '/' . $page,
        'base_url' => base_url(),
    ];

    // Dispatch by page
    switch ($page) {
        case 'bio':
            $bio = load_config('bio') ?: [];
            return ['view' => 'bio', 'data' => array_merge($sharedData, ['bio' => $bio])];

        case 'education':
            $education = load_config('education') ?: [];
            return ['view' => 'education', 'data' => array_merge($sharedData, ['education' => $education])];

        case 'experience':
            $experience = load_config('experience') ?: [];
            return ['view' => 'experience', 'data' => array_merge($sharedData, ['experience' => $experience])];

        case 'media':
            $media = load_config('media') ?: [];
            return ['view' => 'media', 'data' => array_merge($sharedData, ['media' => $media])];

        default:
            return ['view' => '404', 'data' => $sharedData];
    }
}
