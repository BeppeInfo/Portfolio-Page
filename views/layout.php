<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($site['title'] ?? 'Portfolio') ?></title>
    <meta name="description" content="<?= e($site['description'] ?? '') ?>">
    <link rel="stylesheet" href="<?= e(base_url()) ?>/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="<?= e(favicon_url()) ?>">
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
                        <?= icon('github') ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($site['social']['gitlab'])): ?>
                    <a href="<?= e($site['social']['gitlab']) ?>" target="_blank" rel="noopener" title="GitLab">
                        <?= icon('gitlab') ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($site['social']['linkedin'])): ?>
                    <a href="<?= e($site['social']['linkedin']) ?>" target="_blank" rel="noopener" title="LinkedIn">
                        <?= icon('linkedin') ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($site['social']['peertube'])): ?>
                    <a href="<?= e($site['social']['peertube']) ?>" target="_blank" rel="noopener" title="Peertube">
                        <?= icon('peertube') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <script src="<?= e(base_url()) ?>/assets/js/main.js"></script>
</body>
</html>
