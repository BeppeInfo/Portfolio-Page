<div class="page education-page">
    <h1><?= e(t('education.title')) ?></h1>

    <div class="timeline">
        <?php foreach ($education as $item): ?>
            <div class="timeline-item">
                <div class="timeline-marker">
                    <span class="timeline-icon">
                        <?php if ($item['icon'] === 'graduation-cap'): ?>
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3L1 9l11 6 9-4.5V18h2V9M12 15l-9-6 9-6 9 6-9 6z"/></svg>
                        <?php elseif ($item['icon'] === 'certificate'): ?>
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M5 3h14l-1.5 14H6.5L5 3zm3-1h8l-1 12H8L8 2zm2 2L8.5 13h7L14 4h-3z"/><circle cx="12" cy="16" r="2"/></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        <?php endif; ?>
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
