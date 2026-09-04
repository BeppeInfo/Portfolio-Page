<?php
/**
 * Helpers — Utility functions used across the application.
 */

/**
 * Get the current language from query param, cookie, or Accept-Language header.
 *
 * @return string Language code (en, pt, es)
 */
function get_language(): string
{
    // 1. Query param (user override)
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'pt', 'es'])) {
        return $_GET['lang'];
    }

    // 2. Cookie (persistent preference)
    if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], ['en', 'pt', 'es'])) {
        return $_COOKIE['lang'];
    }

    // 3. Accept-Language header
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = parse_accept_language($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($langs as $lang) {
            if (in_array($lang, ['en', 'pt', 'es'])) {
                return $lang;
            }
        }
    }

    // 4. Default
    $site = load_config('site');
    return $site['default_language'] ?? 'en';
}

/**
 * Parse Accept-Language header into ordered list of language codes.
 *
 * @param string $header Accept-Language header value
 * @return array List of language codes sorted by quality
 */
function parse_accept_language(string $header): array
{
    $langs = [];
    $items = explode(',', $header);

    foreach ($items as $item) {
        $parts = explode(';', trim($item));
        $lang = trim($parts[0]);
        $quality = 1.0;

        if (isset($parts[1])) {
            $qParts = explode('=', trim($parts[1]));
            if ($qParts[0] === 'q' && is_numeric($qParts[1])) {
                $quality = (float) $qParts[1];
            }
        }

        // Normalize: pt-BR -> pt
        $lang = explode('-', $lang)[0];
        $langs[$lang] = $quality;
    }

    arsort($langs);
    return array_keys($langs);
}

/**
 * Translate a string key using the current language.
 *
 * Usage: t('nav.bio') returns the translated string for nav.bio
 *
 * @param string $key Dot-separated key (e.g., 'nav.bio')
 * @param string $lang Language code (optional, defaults to current)
 * @return string Translated string or key if not found
 */
function t(string $key, string $lang = null): string
{
    if ($lang === null) {
        $lang = get_language();
    }

    $strings = load_strings($lang);
    $parts = explode('.', $key);

    $value = $strings;
    foreach ($parts as $part) {
        if (isset($value[$part])) {
            $value = $value[$part];
        } else {
            return $key; // fallback to key
        }
    }

    if (is_array($value)) {
        return $key; // fallback to key if value is still an array
    }
    return (string) $value;
}

/**
 * Get the current route from the URL.
 *
 * @return array ['page' => string, 'type' => string|null, 'id' => string|null]
 */
function get_route(): array
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');

    // Remove base path if behind a reverse proxy
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $script = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']), '/');
        if ($script && str_starts_with($uri, $script)) {
            $uri = substr($uri, strlen($script));
            $uri = '/' . ltrim($uri, '/');
        }
    }

    if ($uri === '' || $uri === '/') {
        return ['page' => 'media', 'type' => null, 'id' => null];
    }

    $segments = explode('/', trim($uri, '/'));
    $page = $segments[0];
    $type = isset($segments[1]) ? $segments[1] : null;
    $id = isset($segments[2]) ? $segments[2] : null;

    return ['page' => $page, 'type' => $type, 'id' => $id];
}

/**
 * Sanitize output for HTML — prevents XSS.
 *
 * @param string $str Input string
 * @return string HTML-escaped string
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Get the base URL of the application.
 *
 * @return string Base URL
 */
function base_url(): string
{
    // Check X-Forwarded-Proto for reverse proxy / Kubernetes ingress
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $protocol = 'https';
    } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $protocol = 'https';
    } else {
        $protocol = 'http';
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);

    if ($script === '/' || $script === '/index.php') {
        return $protocol . '://' . $host;
    }

    return $protocol . '://' . $host . $script;
}

/**
 * Render an SVG icon from file.
 *
 * Checks for a user override at public/assets/overrides/icons/<name>.svg,
 * falls back to the built-in public/assets/icons/<name>.svg.
 *
 * Usage: icon('github') outputs <svg>...</svg>
 *
 * @param string $name Icon name (without .svg extension)
 * @param array $attrs Additional HTML attributes (class, style, etc.)
 * @return string SVG markup or empty string if file not found
 */
function icon(string $name, array $attrs = []): string
{
    static $cache = [];

    $cacheKey = 'icon/' . $name;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    // Check for user override first
    $override = __DIR__ . '/../public/assets/overrides/icons/' . $name . '.svg';
    if (is_file($override)) {
        $svg = file_get_contents($override);
    } else {
        // Fall back to built-in default
        $file = __DIR__ . '/../public/assets/icons/' . $name . '.svg';
        if (is_file($file)) {
            $svg = file_get_contents($file);
        } else {
            $cache[$cacheKey] = '';
            return '';
        }
    }

    if (empty($svg)) {
        $cache[$cacheKey] = '';
        return '';
    }

    // Add default class
    if (!preg_match('/\s+class=/', $svg)) {
        $svg = preg_replace('/<svg/', '<svg class="icon"', $svg, 1);
    }

    // Add aria attributes for accessibility
    $svg = preg_replace('/<svg/', '<svg role="img" aria-hidden="true"', $svg, 1);

    $cache[$cacheKey] = $svg;
    return $svg;
}

/**
 * Get the favicon URL.
 *
 * Checks for a user override at public/assets/overrides/favicon.svg,
 * falls back to the built-in public/assets/favicon.svg.
 *
 * @return string Full favicon URL
 */
function favicon_url(): string
{
    // Check for user override
    $override = __DIR__ . '/../public/assets/overrides/favicon.svg';
    if (is_file($override)) {
        return base_url() . '/assets/overrides/favicon.svg';
    }

    // Built-in default
    return base_url() . '/assets/favicon.svg';
}

/**
 * Detect video platform and return embed URL.
 *
 * Supports Peertube, YouTube, Odysee, and generic fallback.
 * If an explicit embedUrl is provided in the config, it is used as-is.
 *
 * @param array $item Media item with 'links' and optional 'embedUrl'
 * @return array ['embedUrl' => string, 'platform' => string]
 */
function video_embed_url(array $item): array
{
    // If embedUrl is explicitly set, use it directly
    if (!empty($item['embedUrl'])) {
        return ['embedUrl' => $item['embedUrl'], 'platform' => 'generic'];
    }

    $link = $item['links']['live'] ?? $item['links']['peertube'] ?? $item['links']['youtube'] ?? $item['links']['odysee'] ?? '';

    if (empty($link)) {
        return ['embedUrl' => '', 'platform' => 'none'];
    }

    // Peertube: /videos/watch/xxx → /videos/embed/xxx
    if (preg_match('/peertube/i', $link)) {
        $embedUrl = preg_replace('/\/videos\/watch\//', '/videos/embed/', $link);
        return ['embedUrl' => $embedUrl, 'platform' => 'peertube'];
    }

    // YouTube: /watch?v=xxx → /embed/xxx
    if (preg_match('/youtube\.com|youtu\.be/i', $link)) {
        $videoId = '';
        if (preg_match('/v=([^&\s]+)/', $link, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtu\.be\/([^\s?]+)/', $link, $matches)) {
            $videoId = $matches[1];
        }
        if ($videoId) {
            return ['embedUrl' => 'https://www.youtube.com/embed/' . $videoId, 'platform' => 'youtube'];
        }
    }

    // Odysee: /$/embed/xxx
    if (preg_match('/odysee\.com/i', $link)) {
        // Odysee URL: https://odysee.com/@user:post:xxx → https://odysee.com/$/embed/xxx
        $embedUrl = preg_replace('/([^\/]+\/[^\/]+\/[^\/]+)\/?$/', '$1/embed', $link);
        return ['embedUrl' => $embedUrl, 'platform' => 'odysee'];
    }

    // Generic fallback — no embed available
    return ['embedUrl' => '', 'platform' => 'generic'];
}

/**
 * Get the platform icon name for a video link.
 *
 * @param string $link Video link URL
 * @return string Icon name
 */
function video_platform_icon(string $link): string
{
    if (preg_match('/peertube/i', $link)) {
        return 'peertube';
    }
    if (preg_match('/youtube\.com|youtu\.be/i', $link)) {
        return 'youtube';
    }
    if (preg_match('/odysee\.com/i', $link)) {
        return 'odysee';
    }
    return 'video';
}
