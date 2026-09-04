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
                    $videoInfo = $item['type'] === 'video' ? video_embed_url($item) : null;
                    $platformIcon = $videoInfo ? video_platform_icon($item['links']['live'] ?? $item['links']['peertube'] ?? $item['links']['youtube'] ?? $item['links']['odysee'] ?? '') : '';
                    ?>
                    <div class="media-card" data-type="<?= e($item['type']) ?>">
                        <?php if ($item['type'] === 'video'): ?>
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
                                <?php if (!empty($item['links']['github'])): ?>
                                    <a href="<?= e($item['links']['github']) ?>" target="_blank" rel="noopener" title="GitHub">
                                        <?= icon('github') ?>
                                        GitHub
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($item['links']['live']) || !empty($item['links']['peertube']) || !empty($item['links']['youtube']) || !empty($item['links']['odysee'])): ?>
                                    <?php
                                    $videoLink = $item['links']['live'] ?? $item['links']['peertube'] ?? $item['links']['youtube'] ?? $item['links']['odysee'] ?? '';
                                    $videoPlatform = $videoInfo ? $videoInfo['platform'] : 'none';
                                    $iconName = $videoPlatform === 'youtube' ? 'youtube' : ($videoPlatform === 'odysee' ? 'odysee' : 'peertube');
                                    $labelKey = $videoPlatform === 'youtube' ? 'media.view_on_youtube' : ($videoPlatform === 'odysee' ? 'media.view_on_odysee' : 'media.view_on_peertube');
                                    $label = $videoPlatform === 'none' ? t('media.view_on_video') : t($labelKey);
                                    ?>
                                    <a href="<?= e($videoLink) ?>" target="_blank" rel="noopener" title="<?= e($label) ?>">
                                        <?= icon($iconName) ?>
                                        <?= e($label) ?>
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
