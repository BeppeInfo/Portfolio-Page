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
                    <div class="media-card" data-type="<?= e($item['type']) ?>">
                        <?php if ($item['type'] === 'video'): ?>
                            <div class="video-placeholder"
                                 data-embed-url="<?= e($item['embedUrl'] ?? '') ?>"
                                 data-peertube-link="<?= e($item['links']['peertube'] ?? '') ?>">
                                <img src="<?= e($item['thumbnail'] ?? '') ?>"
                                     alt="<?= e($item['title']) ?>"
                                     loading="lazy">
                                <div class="play-button">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
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
                                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                                        GitHub
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($item['links']['live']) || !empty($item['links']['peertube'])): ?>
                                    <a href="<?= e($item['links']['live'] ?? $item['links']['peertube']) ?>" target="_blank" rel="noopener" title="<?= e(t('media.view_on_peertube')) ?>">
                                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                                        <?= e(t('media.view_on_peertube')) ?>
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
