<?php
// Capture the page content
ob_start();
?>
<div class="page media-page">
    <h1><?= e(t('media.title')) ?></h1>

    <?php
    // Group items by category
    $categories = [];
    $allItems = $media['items'] ?? [];
    foreach ($allItems as $item) {
        $cat = $item['category'];
        if (!isset($categories[$cat])) {
            $categories[$cat] = [];
        }
        $categories[$cat][] = $item;
    }

    // Sort categories by their order in site.json
    $catOrder = [];
    foreach (($media['categories'] ?? []) as $c) {
        $catOrder[$c['id']] = $c;
    }
    ksort($catOrder);
    ?>

    <!-- Category Tabs -->
    <div class="media-tabs">
        <?php $first = true; ?>
        <?php foreach ($catOrder as $catId => $cat): ?>
            <?php if (!isset($categories[$catId])) continue; ?>
            <button class="media-tab<?= $first ? ' active' : '' ?>"
                    data-category="<?= e($catId) ?>">
                <?= e(t($cat['label'])) ?>
            </button>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </div>

    <!-- Media Grid -->
    <div class="media-grid">
        <?php foreach ($catOrder as $catId => $cat): ?>
            <?php if (!isset($categories[$catId])) continue; ?>
            <div class="media-category" data-category="<?= e($catId) ?>">
                <?php foreach ($categories[$catId] as $item): ?>
                    <?php
                    $videoInfo = $item['type'] === 'video' ? video_platform_url($item) : null;
                    $videoLink = $videoInfo ? $videoInfo['url'] : '';
                    $videoPlatform = $videoInfo ? $videoInfo['platform'] : '';
                    $videoIcon = $videoInfo ? $videoInfo['icon'] : '';
                    $repoInfo = repo_platform_url($item);
                    $repoLink = $repoInfo['url'];
                    $repoPlatform = $repoInfo['platform'];
                    $repoIcon = $repoInfo['icon'];
                    $siteLink = $item['links']['website'] ?? '';
                    ?>
                    <div class="media-card" data-type="<?= e($item['type']) ?>">
                        <?php if (!empty($videoInfo['embedUrl'] ?? '')): ?>
                            <div class="video-placeholder"
                                 data-embed-url="<?= e($videoInfo['embedUrl'] ?? '') ?>"
                                 data-platform="<?= e($videoInfo['platform'] ?? '') ?>">
                                <img src="<?= e($item['thumbnail'] ?? '') ?>"
                                     alt="<?= e($item['title']) ?>"
                                     loading="lazy">
                                <div class="play-button">
                                    <?= icon('play') ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?= e($item['image'] ?? '') ?>" class="media-image-link">
                                <img src="<?= e($item['thumbnail'] ?? '') ?>"
                                     alt="<?= e($item['title']) ?>"
                                     loading="lazy">
                            </a>
                        <?php endif; ?>

                        <div class="media-card-body">
                            <h3><?= e($item['title']) ?></h3>
                            <?php if (!empty($item['description'])): ?>
                                <p class="media-card-desc"><?= e($item['description']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($item['tags'])): ?>
                                <div class="media-tags">
                                    <?php foreach ($item['tags'] as $tag): ?>
                                        <span class="media-tag"><?= e($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="media-links">
                                <?php if (!empty($repoLink)): ?>
                                    <?php
                                    $repoLabel = $repoPlatform === 'generic' ? t('media.view_source_code') : t('media.view_on') . ' ' . $repoPlatform;
                                    ?>
                                    <a href="<?= e($repoLink) ?>" target="_blank" rel="noopener" title="<?= e($repoLabel) ?>">
                                        <?= icon($repoIcon) ?>
                                        <?= e($repoLabel) ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($videoLink)): ?>
                                    <?php
                                    $videoLabel = $videoPlatform === 'generic' ? t('media.view_original') : t('media.view_on') . ' ' . $videoPlatform;
                                    ?>
                                    <a href="<?= e($videoLink) ?>" target="_blank" rel="noopener" title="<?= e($videoLabel) ?>">
                                        <?= icon($videoIcon) ?>
                                        <?= e($videoLabel) ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($siteLink)): ?>
                                    <?php                                                                                                                                                                      
                                    $siteLabel = t('media.view_website');                                                                                                                                      
                                    ?>                                                                                                                                                                         
                                    <a href="<?= e($siteLink) ?>" target="_blank" rel="noopener" title="<?= e($siteLabel) ?>">
                                        <?= icon('website') ?>
                                        <?= e($siteLabel) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Video lightbox overlay -->
<div class="video-lightbox" id="videoLightbox">
    <div class="video-lightbox-content">
        <button class="video-lightbox-close" id="videoLightboxClose" aria-label="Close">&times;</button>
        <div class="video-lightbox-iframe"></div>
    </div>
</div>

<!-- Image lightbox overlay -->
<div class="image-lightbox" id="imageLightbox">
    <div class="image-lightbox-content">
        <button class="image-lightbox-close" id="imageLightboxClose" aria-label="Close">&times;</button>
        <img src="" alt="" id="imageLightboxImg">
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
