<div class="page bio-page">
    <div class="bio-header">
        <div class="bio-avatar">
            <img src="<?= e($bio['avatar'] ?? '/images/bio/avatar.webp') ?>"
                 alt="<?= e($bio['name'] ?? '') ?>"
                 loading="eager">
        </div>
        <div class="bio-info">
            <h1><?= e($bio['name'] ?? 'Leonardo J. Consoni') ?></h1>
            <?php if (!empty($bio['nickname'])): ?>
                <span class="bio-nickname">@<?= e($bio['nickname']) ?></span>
            <?php endif; ?>
            <p class="bio-headline"><?= e(trans($bio['headline'], $lang)) ?></p>
            <?php if (!empty($bio['company'])): ?>
                <p class="bio-company"><?= e($bio['company']) ?></p>
            <?php endif; ?>
            <p class="bio-summary"><?= e(trans($bio['summary'], $lang)) ?></p>
        </div>
    </div>

    <?php if (!empty($bio['highlights'])): ?>
        <section class="bio-section">
            <h2><?= e(t('bio.highlights_title')) ?></h2>
            <div class="highlights-grid">
                <?php foreach ($bio['highlights'] as $h): ?>
                    <div class="highlight-card">
                        <div class="highlight-icon">
                            <?= icon($h['icon']) ?>
                        </div>
                        <h3><?= e(trans($h['title'], $lang)) ?></h3>
                        <p><?= e(trans($h['description'], $lang)) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($bio['skills'])): ?>
        <section class="bio-section">
            <h2><?= e(t('bio.skills_title')) ?></h2>
            <div class="skills-grid">
                <?php foreach ($bio['skills'] as $skill): ?>
                    <div class="skill-category">
                        <h3><?= e(trans($skill['category'], $lang)) ?></h3>
                        <div class="skill-tags">
                            <?php foreach ($skill['items'] as $item): ?>
                                <span class="skill-tag"><?= e($item) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
