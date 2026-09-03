<?php
// Capture the page content
ob_start();
?>
<div class="page education-page">
    <h1><?= e(t('education.title')) ?></h1>

    <div class="timeline">
        <?php foreach ($education as $item): ?>
            <div class="timeline-item">
                <div class="timeline-marker">
                    <span class="timeline-icon">
                        <?= icon($item['icon']) ?>
                    </span>
                </div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <span class="timeline-year"><?= e($item['year']) ?></span>
                        <h3><?= e($item['degree']) ?></h3>
                    </div>
                    <div class="timeline-body">
                        <div class="timeline-detail">
                            <strong><?= t('education.institution') ?>:</strong> <?= e($item['institution']) ?>
                        </div>
                        <?php if (!empty($item['location'])): ?>
                            <div class="timeline-detail">
                                <strong><?= t('education.location') ?>:</strong> <?= e($item['location']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item['description'])): ?>
                            <p class="timeline-description"><?= e($item['description']) ?></p>
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
