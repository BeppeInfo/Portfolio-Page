<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($site['title'] ?? 'Portfolio') ?></title>
    <meta name="description" content="<?= e($site['description'] ?? '') ?>">
    <link rel="stylesheet" href="<?= e(base_url()) ?>/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="<?= e(base_url()) ?>/assets/favicon.svg">
</head>
<body>
    <nav class="nav" id="nav">
        <div class="nav-container">
            <a href="<?= e(base_url()) ?>/" class="nav-logo">
                <?= e($site['title'] ?? 'Portfolio') ?>
            </a>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span class="hamburger"></span>
            </button>

            <div class="nav-menu" id="navMenu">
                <?php foreach ($site['navigation'] as $item): ?>
                    <?php
                    $label = t($item['label']);
                    $active = ($current_route['page'] === $item['route']) ? ' active' : '';
                    ?>
                    <a href="<?= e(base_url()) ?>/<?= e($item['route']) ?>" class="nav-link<?= e($active) ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>

                <div class="nav-lang">
                    <?php foreach ($site['languages'] as $code): ?>
                        <?php $active = ($lang === $code) ? ' active' : ''; ?>
                        <a href="<?= e(base_url()) ?>/?lang=<?= e($code) ?>"
                           class="lang-btn<?= e($active) ?>"
                           title="<?= e($code) ?>">
                            <?= e(strtoupper($code)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="main">
        <?= $content ?>
    </main>

    <footer class="footer">
        <div class="footer-container">
            <p><?= e($strings['footer'] ?? $site['footer'] ?? '') ?></p>
            <div class="social-links">
                <?php if (!empty($site['social']['github'])): ?>
                    <a href="<?= e($site['social']['github']) ?>" target="_blank" rel="noopener" title="GitHub">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($site['social']['linkedin'])): ?>
                    <a href="<?= e($site['social']['linkedin']) ?>" target="_blank" rel="noopener" title="LinkedIn">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.287zM5.333 7.438a2.062 2.062 0 01-1.722-1.014 2.06 2.06 0 01.226-2.244A2.063 2.063 0 014.83 2.25a2.062 2.062 0 011.498 1.922 2.06 2.06 0 01-.46 1.998 2.062 2.062 0 01-1.535 1.268zM.118 20.452H3.67V9H.118v11.452z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if (!empty($site['social']['peertube'])): ?>
                    <a href="<?= e($site['social']['peertube']) ?>" target="_blank" rel="noopener" title="Peertube">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <script src="<?= e(base_url()) ?>/assets/js/main.js"></script>
</body>
</html>
