<?php
// Capture the page content
ob_start();
?>
<div class="page experience-page">
    <h1><?= e(t('experience.title')) ?></h1>

    <div class="timeline timeline-experience">
        <?php foreach ($experience as $item): ?>
            <div class="timeline-item">
                <div class="timeline-marker">
                    <span class="timeline-icon">
                        <?= icon('briefcase') ?>
                    </span>
                </div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <span class="timeline-year"><?= e($item['period']) ?></span>
                        <h3><?= e($item['role']) ?></h3>
                        <?php if (!empty($item['company'])): ?>
                            <?php if (!empty($item['link'])): ?>
                                <a href="<?= e($item['link']) ?>" target="_blank" rel="noopener" class="timeline-company"><?= e($item['company']) ?></a>
                            <?php else: ?>
                                <span class="timeline-company"><?= e($item['company']) ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-body">
                        <?php if (!empty($item['location'])): ?>
                            <div class="timeline-detail">
                                <strong><?= t('education.location') ?>:</strong> <?= e($item['location']) ?>
                                <?php if (!empty($item['remote'])): ?>
                                    <span class="badge badge-remote"><?= t('experience.remote') ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item['description'])): ?>
                            <p class="timeline-description"><?= e($item['description']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($item['achievements'])): ?>
                            <div class="achievements-section">
                                <h4><?= t('experience.achievements') ?></h4>
                                <ul>
                                    <?php foreach ($item['achievements'] as $achievement): ?>
                                        <li><?= e($achievement) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item['technologies'])): ?>
                            <div class="technologies-section">
                                <h4><?= t('experience.technologies') ?></h4>
                                <div class="tech-tags">
                                    <?php foreach ($item['technologies'] as $tech): ?>
                                        <span class="tech-tag"><?= e($tech) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
