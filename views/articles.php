<?php
// Capture the page content
ob_start();
?>
<div class="page articles-page">
    <h1><?= e(t('nav.articles')) ?></h1>

    <?php
    // Sort all articles by date descending
    $allArticles = $articles ?? [];
    usort($allArticles, function ($a, $b) {
        $dateA = $a['date'] ?? '0000-00-00';
        $dateB = $b['date'] ?? '0000-00-00';
        return strcmp($dateB, $dateA);
    });
    ?>

    <div class="articles-list">
        <?php foreach ($allArticles as $article): ?>
            <article class="article-card">
                <div class="article-header">
                    <span class="article-date"><?= e($article['date'] ?? '') ?></span>
                    <h2 class="article-title">
                        <a href="<?= e($article['url']) ?>" target="_blank" rel="noopener"><?= e($article['title']) ?></a>
                    </h2>
                </div>
                <?php if (!empty($article['summary'])): ?>
                    <p class="article-summary"><?= e(trans($article['summary'], $lang)) ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php
$content = ob_get_clean();

// Render with layout
echo render('layout', [
    'site' => $site,
    'strings' => $strings,
    'lang' => $lang,
    'current_route' => $current_route,
    'current_path' => $current_path,
    'base_url' => $base_url,
    'content' => $content,
]);
?>
