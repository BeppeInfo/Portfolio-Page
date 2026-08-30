<?php
echo render('layout', [
    'site' => $site,
    'strings' => $strings,
    'lang' => $lang,
    'current_route' => $current_route,
    'base_url' => $base_url,
    'content' => '<div class="page page-placeholder"><h1>' . e(t('experience.title')) . '</h1><p class="placeholder-msg">Coming soon.</p></div>',
]);
?>
