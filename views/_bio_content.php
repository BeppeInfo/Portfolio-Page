<div class="page bio-page">
    <div class="bio-header">
        <div class="bio-avatar">
            <img src="<?= e($bio['avatar'] ?? '/images/bio/avatar.webp') ?>"
                 alt="<?= e($bio['name'] ?? '') ?>"
                 loading="eager">
        </div>
        <div class="bio-info">
            <h1><?= e($bio['name'] ?? 'LeonardoJC') ?></h1>
            <p class="bio-headline"><?= e(t('bio.headline')) ?></p>
            <p class="bio-summary"><?= e($bio['summary'] ?? '') ?></p>
        </div>
    </div>

    <?php if (!empty($bio['highlights'])): ?>
        <section class="bio-section">
            <h2><?= e(t('bio.highlights_title')) ?></h2>
            <div class="highlights-grid">
                <?php foreach ($bio['highlights'] as $h): ?>
                    <div class="highlight-card">
                        <div class="highlight-icon">
                            <?php
                            $icons = [
                                'code'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.5 4L1.5 12L8.5 20M15.5 4L22.5 12L15.5 20M14 6L10 18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                                'cloud'     => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 10h-1.2A4 4 0 0012 6a4 4 0 00-3.8 5.3A3 3 0 0011 17h7a3 3 0 000-6z"/></svg>',
                                'terminal'  => '<svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><path d="M7 8l4 4-4 4M13 16h5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            ];
                            echo $icons[$h['icon']] ?? '';
                            ?>
                        </div>
                        <h3><?= e($h['title']) ?></h3>
                        <p><?= e($h['description']) ?></p>
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
                        <h3><?= e($skill['category']) ?></h3>
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
