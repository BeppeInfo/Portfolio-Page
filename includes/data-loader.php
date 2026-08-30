<?php
/**
 * Data Loader — Load JSON config files with optional caching.
 */

/**
 * Load a JSON config file from config/.
 *
 * @param string $file Filename (without .json extension)
 * @return array|false Parsed data or false on failure
 */
function load_config(string $file): array|false
{
    $path = __DIR__ . '/../config/' . $file . '.json';

    if (!file_exists($path)) {
        error_log("Config file not found: {$path}");
        return false;
    }

    $json = file_get_contents($path);
    if ($json === false) {
        error_log("Failed to read config file: {$path}");
        return false;
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error in {$path}: " . json_last_error_msg());
        return false;
    }

    return $data;
}

/**
 * Load all language string files and merge with the requested language.
 *
 * @param string $lang Language code (en, pt, es)
 * @return array Merged string translations
 */
function load_strings(string $lang): array
{
    $base = load_config('strings.en'); // fallback base
    if ($base === false) {
        return [];
    }

    $override = load_config('strings.' . $lang);
    if ($override === false) {
        return $base;
    }

    return array_merge_recursive($base, $override);
}
