<?php
// Capture the page content
ob_start();
?>
<div class="page articles-page">
    <h1><?= e(t('nav.articles')) ?></h1>

    <?php
    // Sort parts by date descending
    $parts = $articles ?? [];
    usort($parts, function ($a, $b) {
        $dateA = $a['date'] ?? '0000-00-00';
        $dateB = $b['date'] ?? '0000-00-00';
        return strcmp($dateB, $dateA);
    });
    ?>

    <div class="articles-list">
        <?php foreach ($parts as $index => $part): ?>
            <div class="article-part">
                <div class="part-header-row">
                    <?php if (!empty($part['articles'])): ?>
                        <button class="part-header" aria-expanded="false" aria-controls="part-articles-<?= $index ?>" data-part-index="<?= $index ?>">
                            <span class="part-heading">
                                <span class="part-title">
                                    <?= e(trans($part['title'], $lang)) ?>
                                </span>
                                <?php if (!empty($part['description'])): ?>
                                    <span class="part-description">
                                        <?= e(trans($part['description'], $lang)) ?>
                                    </span>
                                <?php endif; ?>
                            </span>
                            <span class="part-date"><?= e($part['date'] ?? '') ?></span>
                            <span class="part-chevron">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </span>
                        </button>
                    <?php else: ?>
                        <span class="part-heading">
                            <span class="part-title">
                                <?= e(trans($part['title'], $lang)) ?>
                            </span>
                            <?php if (!empty($part['description'])): ?>
                                <span class="part-description">
                                    <?= e(trans($part['description'], $lang)) ?>
                                </span>
                            <?php endif; ?>
                        </span>
                        <span class="part-date"><?= e($part['date'] ?? '') ?></span>
                    <?php endif; ?>
                    <?php if (!empty($part['url'])): ?>
                        <a class="part-link" href="<?= e($part['url']) ?>" target="_blank" rel="noopener" title="<?= e(trans($part['title'], $lang)) ?>">
                            <?= icon('external-link') ?>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($part['articles'])): ?>
                    <div class="part-articles" id="part-articles-<?= $index ?>">
                        <?php
                        // Sort articles within part by date descending
                        $partArticles = $part['articles'];
                        usort($partArticles, function ($a, $b) {
                            $dateA = $a['date'] ?? '0000-00-00';
                            $dateB = $b['date'] ?? '0000-00-00';
                            return strcmp($dateB, $dateA);
                        });
                        ?>
                        <ul class="article-items">
                            <?php foreach ($partArticles as $article): ?>
                                <li class="article-item">
                                    <span class="article-date"><?= e($article['date'] ?? '') ?></span>
                                    <span class="article-body">
                                        <a class="article-link" href="<?= e($article['url']) ?>" target="_blank" rel="noopener">
                                            <?= e(trans($article['title'], $lang)) ?>
                                        </a>
                                        <?php if (!empty($article['summary'])): ?>
                                            <span class="article-summary"><?= e(trans($article['summary'], $lang)) ?></span>
                                        <?php endif; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
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
