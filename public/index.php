<?php
/**
 * Front Controller — Entry point for all requests.
 */

// Load all includes
require_once __DIR__ . '/../includes/data-loader.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/template.php';
require_once __DIR__ . '/../includes/router.php';

// Set language cookie for future visits
$lang = get_language();
setcookie('lang', $lang, [
    'expires' => time() + (365 * 24 * 60 * 60),
    'path'    => '/',
    'secure'  => true,
    'httponly'=> true,
    'samesite'=> 'Lax',
]);

// Dispatch
$dispatch = dispatch();
$view = $dispatch['view'];
$data = $dispatch['data'];

// Render
echo render($view, $data);
