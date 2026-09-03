<?php
echo render('layout', [
    'site' => $site,
    'strings' => $strings,
    'lang' => $lang,
    'current_route' => $current_route,
    'current_path' => '',
    'base_url' => $base_url,
    'content' => '<div class="page page-404"><h1>404</h1><p>' . e(t('common.not_found')) . '</p><a href="' . e(base_url()) . '/">' . e(t('common.go_home')) . '</a></div>',
]);
?>
