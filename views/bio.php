<?php
$content = render('_bio_content', [
    'bio' => $bio,
    'strings' => $strings,
]);
echo render('layout', [
    'site' => $site,
    'strings' => $strings,
    'lang' => $lang,
    'current_route' => $current_route,
    'base_url' => $base_url,
    'content' => $content,
]);
?>
