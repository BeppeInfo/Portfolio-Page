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
        return ['page' => 'bio', 'type' => null, 'id' => null];
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
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);

    if ($script === '/') {
        return $protocol . '://' . $host;
    }

    return $protocol . '://' . $host . $script;
}
