<?php
/**
 * Template Engine — Render views with data.
 *
 * Uses simple PHP include with extract() for variable passing.
 * No external dependencies.
 */

/**
 * Render a view file with the given data.
 *
 * @param string $view View name (e.g., 'bio', 'layout')
 * @param array $data Associative array of variables to pass
 * @return string Rendered HTML
 */
function render(string $view, array $data = []): string
{
    $viewPath = __DIR__ . '/../views/' . $view . '.php';

    if (!file_exists($viewPath)) {
        error_log("View not found: {$viewPath}");
        return "<!-- View not found: {$view} -->";
    }

    // Extract data into local scope
    extract($data);

    // Capture output
    ob_start();
    include $viewPath;
    return ob_get_clean();
}
