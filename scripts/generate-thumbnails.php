#!/usr/bin/env php
<?php
/**
 * generate-thumbnails.php — Batch thumbnail generation using PHP GD.
 *
 * Converts source images (JPEG, PNG, WebP) into optimized WebP files at
 * multiple sizes for use in the portfolio media gallery.
 *
 * Usage:
 *   php generate-thumbnails.php <source-file>
 *   php generate-thumbnails.php <directory>       # process all images in dir
 *   php generate-thumbnails.php <file1> <file2>    # process multiple files
 *
 * Output sizes:
 *   <name>.webp        — original dimensions, quality 85
 *   <name>-thumb.webp  — 300×200   (gallery card thumbnail)
 *   <name>-medium.webp — 800×533   (medium display)
 *   <name>-large.webp  — 1600×1067 (lightbox / detail view)
 *
 * Requirements:
 *   - PHP 8.2+ with GD extension
 *   - libwebp support compiled into GD (check phpinfo() for "WebP Support")
 *
 * Examples:
 *   php generate-thumbnails.php public/images/projects/alpha.jpg
 *   php generate-thumbnails.php public/images/projects/
 *   php generate-thumbnails.php public/images/projects/alpha.jpg public/images/media/demo-thumb.jpg
 */

declare(strict_types=1);

// ─── Configuration ───────────────────────────────────────────────────────────

const OUTPUT_QUALITY   = 85;          // WebP quality (0–100)
const STRATEGY         = 'both';      // 'width' | 'height' | 'both' — fit strategy

// Target sizes: [width, height] — aspect ratio preserved via `fit` mode
const SIZES = [
    'thumb'  => [300, 200],
    'medium' => [800, 533],
    'large'  => [1600, 1067],
];

// Supported input extensions (case-insensitive)
const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif'];

// ─── Bootstrap ───────────────────────────────────────────────────────────────

if (!extension_loaded('gd')) {
    fwrite(STDERR, "Error: GD extension is not loaded.\n");
    fwrite(STDERR, "Install it with: docker-php-ext-install gd\n");
    exit(1);
}

// Check WebP support
$im = imagecreatetruecolor(1, 1);
if (!function_exists('image/webp') && !method_exists($im, 'imagewebp')) {
    // image/webp isn't a function; check via gd_info
    $info = gd_info();
    if (!($info['WebP Support'] ?? false)) {
        fwrite(STDERR, "Error: GD does not support WebP output.\n");
        fwrite(STDERR, "Rebuild GD with libwebp support, e.g.:\n");
        fwrite(STDERR, "  apk add --no-cache libwebp-dev\n");
        fwrite(STDERR, "  docker-php-ext-install gd\n");
        exit(1);
    }
}
imagedestroy($im);

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Determine the MIME type of an image file.
 */
function detect_mime(string $path): string
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($path);

    // Normalize
    if (str_starts_with($mime, 'image/jpeg')) {
        return 'image/jpeg';
    }
    if (str_starts_with($mime, 'image/png')) {
        return 'image/png';
    }
    if (str_starts_with($mime, 'image/webp')) {
        return 'image/webp';
    }
    if (str_starts_with($mime, 'image/bmp')) {
        return 'image/bmp';
    }

    return $mime;
}

/**
 * Load an image from any supported format into a GD resource.
 */
function load_image(string $path): false|GdImage
{
    $mime = detect_mime($path);

    return match ($mime) {
        'image/jpeg'  => imagecreatefromjpeg($path),
        'image/png'   => imagecreatefrompng($path),
        'image/webp'  => imagecreatefromwebp($path),
        'image/bmp'   => imagecreatefrombmp($path),
        'image/gif'   => imagecreatefromgif($path),
        default       => false,
    };
}

/**
 * Save a GD image as WebP at the given quality.
 */
function save_webp(GdImage $image, string $path, int $quality = OUTPUT_QUALITY): bool
{
    // Create parent directories if needed
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return imagewebp($image, $path, $quality);
}

/**
 * Fit an image into a target box while preserving aspect ratio.
 * Uses "fit" strategy: the image fills the box, center-cropped if needed.
 *
 * @param GdImage $src  Source image
 * @param int     $tw   Target width
 * @param int     $th   Target height
 * @return GdImage       New GD image resource (caller must destroy)
 */
function fit_image(GdImage $src, int $tw, int $th): GdImage
{
    $sw = imagesx($src);
    $sh = imagesy($src);

    // Calculate scaling ratio to fill the target box
    $ratio = max($tw / $sw, $th / $sh);
    $newW = (int) floor($sw * $ratio);
    $newH = (int) floor($sh * $ratio);

    // Create transparent canvas
    $canvas = imagecreatetruecolor($tw, $th);

    // Fill with background color (dark, matching the site theme)
    $bg = imagecolorallocate($canvas, 13, 13, 26); // --color-bg
    imagefill($canvas, 0, 0, $bg);

    // Center-crop: calculate source offset
    $srcX = (int) floor(($newW - $tw) / $ratio / 2);
    $srcY = (int) floor(($newH - $th) / $ratio / 2);

    // Preserve alpha for PNG/WebP source
    imagesavealpha($canvas, true);

    imagecopyresampled(
        $canvas, $src,
        0, 0,                       // dest x, y
        $srcX, $srcY,               // src x, y (center-crop)
        $tw, $th,                   // dest w, h
        $newW, $newH,               // src w, h
    );

    return $canvas;
}

/**
 * Check if a file is a supported image.
 */
function is_supported_image(string $path): bool
{
    if (!is_file($path)) {
        return false;
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($ext, SUPPORTED_EXTENSIONS, true);
}

/**
 * Generate all output files for a single source image.
 * Returns an array of generated file paths.
 */
function generate_all(string $sourcePath, array &$stats): array
{
    $stats['processed'] = ($stats['processed'] ?? 0) + 1;
    $stats['errors']    = ($stats['errors'] ?? 0);

    $image = load_image($sourcePath);
    if ($image === false) {
        fwrite(STDERR, "  ✗ Could not load: $sourcePath\n");
        $stats['errors']++;
        return [];
    }

    $base     = pathinfo($sourcePath, PATHINFO_DIRNAME) . '/' . pathinfo($sourcePath, PATHINFO_FILENAME);
    $generated = [];

    // ── Original quality (full size) ──────────────────────────────────────
    $originalPath = $base . '.webp';
    if (file_exists($originalPath)) {
        fwrite(STDOUT, "  ⚪ Skipping (exists): $originalPath\n");
        $stats['skipped'] = ($stats['skipped'] ?? 0) + 1;
    } else {
        if (save_webp($image, $originalPath)) {
            $size = round(filesize($originalPath) / 1024, 1);
            fwrite(STDOUT, "  ✓ Original: $originalPath ({$size} KB)\n");
            $generated[] = $originalPath;
        } else {
            fwrite(STDERR, "  ✗ Failed to save: $originalPath\n");
            $stats['errors']++;
        }
    }

    // ── Scaled variants ───────────────────────────────────────────────────
    foreach (SIZES as $label => [$tw, $th]) {
        $outPath = "{$base}-{$label}.webp";

        if (file_exists($outPath)) {
            fwrite(STDOUT, "  ⚪ Skipping (exists): $outPath\n");
            $stats['skipped'] = ($stats['skipped'] ?? 0) + 1;
            $generated[] = $outPath;
            continue;
        }

        $resized = fit_image($image, $tw, $th);
        if (save_webp($resized, $outPath)) {
            $size = round(filesize($outPath) / 1024, 1);
            fwrite(STDOUT, "  ✓ {$label} ({$tw}×{$th}): $outPath ({$size} KB)\n");
            imagedestroy($resized);
            $generated[] = $outPath;
        } else {
            fwrite(STDERR, "  ✗ Failed to save: $outPath\n");
            $stats['errors']++;
            imagedestroy($resized);
        }
    }

    imagedestroy($image);
    return $generated;
}

/**
 * Recursively find all supported image files in a directory.
 */
function find_images_in_dir(string $dir): array
{
    $images = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && is_supported_image($file->getPathname())) {
            $images[] = $file->getPathname();
        }
    }

    return $images;
}

// ─── Main ────────────────────────────────────────────────────────────────────

$files = [];

foreach ($argv as $i => $arg) {
    if ($i === 0) continue; // skip script name

    if (is_dir($arg)) {
        $files = array_merge($files, find_images_in_dir($arg));
    } elseif (is_file($arg) && is_supported_image($arg)) {
        $files[] = $arg;
    } else {
        fwrite(STDERR, "  ⚪ Skipping (not a supported file): $arg\n");
    }
}

if (empty($files)) {
    fwrite(STDERR, "No supported images found.\n");
    fwrite(STDERR, "\nUsage:\n");
    fwrite(STDERR, "  php generate-thumbnails.php <source-file>\n");
    fwrite(STDERR, "  php generate-thumbnails.php <directory>\n");
    fwrite(STDERR, "  php generate-thumbnails.php <file1> <file2>\n");
    fwrite(STDERR, "\nSupported formats: " . implode(', ', SUPPORTED_EXTENSIONS) . "\n");
    exit(1);
}

// Sort for deterministic output
sort($files);

fwrite(STDOUT, "Generating thumbnails for " . count($files) . " image(s)...\n");
fwrite(STDOUT, "Quality: " . OUTPUT_QUALITY . ", Format: WebP\n");
fwrite(STDOUT, "Sizes: thumb=" . implode('×', SIZES['thumb'])
    . ", medium=" . implode('×', SIZES['medium'])
    . ", large=" . implode('×', SIZES['large']) . "\n\n");

$stats = [];

foreach ($files as $file) {
    fwrite(STDOUT, "Processing: $file\n");
    generate_all($file, $stats);
}

// ─── Summary ─────────────────────────────────────────────────────────────────

fwrite(STDOUT, "\n── Summary ─────────────────────────────────────────────\n");
fwrite(STDOUT, "  Processed: " . ($stats['processed'] ?? 0) . "\n");
fwrite(STDOUT, "  Skipped:   " . ($stats['skipped'] ?? 0) . "\n");
fwrite(STDOUT, "  Errors:    " . ($stats['errors'] ?? 0) . "\n");

if (($stats['errors'] ?? 0) > 0) {
    exit(1);
}

fwrite(STDOUT, "  Done.\n");
exit(0);
