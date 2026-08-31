# Media Publishing Guide

Add projects and videos to your portfolio's Media page by updating data files and uploading images. No code changes required.

---

## Quick Overview

```
1. Prepare your images (convert → thumbnails)
2. Register the entry in config/media.json
3. Deploy — the media page renders automatically
```

---

## 1. Prepare Your Images

### Required Files Per Entry

| Role | Filename Pattern | Dimensions | Purpose |
|------|-----------------|------------|---------|
| Full image | `{slug}.webp` | Original | Lightbox / detail view |
| Thumbnail | `{slug}-thumb.webp` | 300×200 | Gallery card |

### Directory Structure

```
public/images/
├── projects/
│   ├── my-project.webp           ← full-size
│   ├── my-project-thumb.webp     ← 300×200
│   ├── my-project-medium.webp    ← 800×533 (optional)
│   └── my-project-large.webp     ← 1600×1067 (optional)
│
├── media/
│   ├── talk-k8s-thumb.webp       ← video thumbnail
│   └── demo-app-thumb.webp       ← video thumbnail
│
└── bio/
    └── avatar.webp               ← profile picture (140×140)
```

> **Note:** `public/images/` is gitignored — images are mounted at runtime via Docker volumes or Kubernetes PVCs.

### Generating Thumbnails

Use the built-in script to convert and resize images:

```bash
# Single file
php scripts/generate-thumbnails.php public/images/projects/my-project.jpg

# Entire directory (recursive)
php scripts/generate-thumbnails.php public/images/projects/

# Multiple files
php scripts/generate-thumbnails.php \
  public/images/projects/project1.jpg \
  public/images/projects/project2.png
```

**Output:** For each source image, the script generates:
- `{name}.webp` — original quality, full resolution
- `{name}-thumb.webp` — 300×200 (gallery card)
- `{name}-medium.webp` — 800×533 (medium display)
- `{name}-large.webp` — 1600×1067 (lightbox)

All outputs are center-cropped to fit the target aspect ratio and saved as WebP at 85% quality.

### Requirements

- PHP 8.2+ with GD extension
- WebP support compiled into GD (`gd_info()['WebP Support']`)

Check your environment:
```bash
php -m | grep -i gd
php -r "print_r(gd_info()['WebP Support']);"
```

If WebP is not available, install it:
```bash
# Alpine (Docker image)
apk add --no-cache libwebp-dev
docker-php-ext-install gd

# Debian/Ubuntu
apt-get install libwebp-dev
docker-php-ext-install gd
```

---

## 2. Register a Project (Image Entry)

Add an entry to `config/media.json` under the `items` array:

```json
{
  "id": "unique-slug",
  "type": "image",
  "category": "projects",
  "title": "My Awesome Project",
  "description": "A brief description of what this project does.",
  "image": "/images/projects/my-project.webp",
  "thumbnail": "/images/projects/my-project-thumb.webp",
  "tags": ["PHP", "Docker", "Kubernetes"],
  "links": {
    "github": "https://github.com/yourusername/my-project",
    "live": "https://my-project.example.com"
  },
  "date": "2025-01-15"
}
```

### Field Reference

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | string | ✅ | Unique identifier (slug, no spaces) |
| `type` | string | ✅ | `"image"` for projects/screenshots |
| `category` | string | ✅ | `"projects"` or `"videos"` |
| `title` | string | ✅ | Display title |
| `description` | string | ❌ | Short description (shown in card) |
| `image` | string | ✅ | Path to full-size image |
| `thumbnail` | string | ✅ | Path to thumbnail (300×200) |
| `tags` | string[] | ❌ | Technology/topic tags |
| `links.github` | string | ❌ | GitHub repository URL |
| `links.live` | string | ❌ | Live demo URL |
| `date` | string | ❌ | ISO 8601 date (`YYYY-MM-DD`) |

---

## 3. Register a Video (Peertube Embed)

Add a video entry to `config/media.json`:

```json
{
  "id": "kubernetes-talk-2025",
  "type": "video",
  "category": "videos",
  "title": "Getting Started with Kubernetes on k3s",
  "description": "A presentation on setting up and managing k3s clusters.",
  "embedUrl": "https://tube.choppa.xyz/videos/embed/abc123",
  "thumbnail": "/images/media/k8s-talk-thumb.webp",
  "tags": ["Kubernetes", "k3s", "DevOps"],
  "links": {
    "peertube": "https://tube.choppa.xyz/videos/watch/abc123"
  },
  "date": "2025-05-20"
}
```

### Field Reference

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | string | ✅ | Unique identifier |
| `type` | string | ✅ | `"video"` |
| `category` | string | ✅ | `"videos"` |
| `title` | string | ✅ | Display title |
| `description` | string | ❌ | Short description |
| `embedUrl` | string | ✅ | Peertube embed URL (`/videos/embed/{id}`) |
| `thumbnail` | string | ✅ | Path to thumbnail image |
| `tags` | string[] | ❌ | Topic tags |
| `links.peertube` | string | ✅ | Direct link to Peertube video |
| `date` | string | ❌ | ISO 8601 date |

### Peertube URL Patterns

| URL Type | Pattern | Example |
|----------|---------|---------|
| Embed | `{base}/videos/embed/{videoId}` | `https://tube.choppa.xyz/videos/embed/abc123` |
| Watch | `{base}/videos/watch/{videoId}` | `https://tube.choppa.xyz/videos/watch/abc123` |

> **Tip:** Your Peertube instance URL is configured in `config/site.json` under `social.peertube`. All embed URLs should use the same base.

---

## 4. Upload Images to the Running Instance

Since `public/images/` is mounted at runtime, you need to place images where the volume is mounted.

### Docker Compose (Local Dev)

```bash
# Copy images to the mounted directory
cp public/images/projects/my-project.jpg /path/to/your/config-mount/public/images/projects/

# Or use docker exec
docker compose exec app cp /tmp/my-project.jpg /var/www/html/public/images/projects/
```

With the default `docker-compose.yml`, `public/images/` is a volume mount. Place files in the mounted host directory.

### Kubernetes

Images are stored in a PersistentVolumeClaim (`portfolio-media`). Access via:

```bash
# Copy into the PVC
kubectl cp ./public/images/projects/my-project.jpg \
  portfolio-xxxxx:/var/www/html/public/images/projects/ -n choppa

# Or create a ConfigMap/Secret with images (for small sets)
kubectl create configmap portfolio-images \
  --from-file=public/images/projects/my-project-thumb.webp \
  -n choppa
```

### Direct Copy (Quick & Dirty)

```bash
# Find the running container
docker ps | grep portfolio

# Copy files
docker cp public/images/projects/my-project.jpg <container-id>:/var/www/html/public/images/projects/
```

---

## 5. Verify

After updating `config/media.json` and uploading images:

1. **Local dev:** Refresh `http://localhost:8080/media` — changes appear immediately (config is volume-mounted).
2. **Production:** The media page renders from `config/media.json` at request time — no rebuild needed.

### Checklist

- [ ] Image files exist at the paths specified in `media.json`
- [ ] Thumbnails are generated (use `generate-thumbnails.php`)
- [ ] `config/media.json` has the new entry with correct paths
- [ ] Media page shows the new item in the correct category tab
- [ ] Clicking a project opens the image lightbox
- [ ] Clicking a video opens the video lightbox with the Peertube embed

---

## Troubleshooting

### Images Not Showing

1. **Check the path:** The `image` and `thumbnail` paths in `media.json` are relative to the web root (`/`).
   - ✅ `/images/projects/my-project.webp`
   - ❌ `images/projects/my-project.webp` (missing leading slash)
   - ❌ `/public/images/projects/my-project.webp` (too many segments)

2. **Check the volume mount:** Ensure the images directory is mounted at runtime.
   ```bash
   docker compose exec app ls -la /var/www/html/public/images/projects/
   ```

3. **Check permissions:** The container runs as `www-data`.
   ```bash
   docker compose exec app chown -R www-data:www-data /var/www/html/public/images/
   ```

### Thumbnails Not Generated

```bash
# Check GD WebP support
docker compose exec app php -r "print_r(gd_info()['WebP Support']);"

# If false, rebuild with libwebp
# See "Requirements" section above
```

### Video Not Loading

1. Verify the `embedUrl` is correct and accessible:
   ```bash
   curl -I "https://tube.choppa.xyz/videos/embed/abc123"
   ```
2. Check that the Peertube instance is reachable from the cluster (for K8s deployments).
3. Check browser console for CORS errors.

---

## Adding a New Category

To add a new media category (e.g., "Gallery"):

1. Add to `config/media.json` `categories`:
   ```json
   { "id": "gallery", "label": "nav.media.gallery", "icon": "image" }
   ```

2. Add the translation key to `strings.en.json`:
   ```json
   "nav.media.gallery": "Gallery"
   ```

3. Add entries with `"category": "gallery"` to the `items` array.

No code changes needed — the template renders categories dynamically from the config.
