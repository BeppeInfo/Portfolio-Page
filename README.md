# Portfolio-Page

> A lightweight, self-hosted PHP portfolio website, containerized for Kubernetes.

**Author:** Beppe Info  
**Repo:** [github.com/BeppeInfo/Portfolio-Page](https://github.com/BeppeInfo/Portfolio-Page)  
**Docker:** [docker.io/ancapepe/portfolio](https://hub.docker.com/r/ancapepe/portfolio)  

---

## Overview

A data-driven, zero-dependency portfolio site built with pure PHP 8.2+, Nginx, and PHP-FPM. All content is managed through JSON configuration files — no database, no Composer, no frameworks. Designed to run as a single Docker image under 100 MB and deploy seamlessly to Kubernetes (k3s).

### Features

- **Multi-language** — English, Portuguese, Spanish (detects `Accept-Language`, switchable via nav)
- **Dark theme** — Custom CSS properties (dark gray `#0d0d1a`, yellow `#f5c518`, teal/cyan `#00b4d8`)
- **Data-driven** — Edit JSON files to update content; no code changes needed
- **Container-native** — Single Docker image with Nginx + PHP-FPM, non-root user, health checks
- **Peertube integration** — Video embeds via self-hosted Peertube instances
- **Thumbnail generation** — `scripts/generate-thumbnails.php` converts images to optimized WebP at multiple sizes
- **Media publishing guide** — Step-by-step instructions in `MEDIA.md`
- **Responsive** — Mobile-first CSS with hamburger navigation
- **Security hardened** — Deny access to config/includes/views, security headers, non-root container

---

## Quick Start

### Local Development

```bash
# Clone the repo
git clone https://github.com/BeppeInfo/Portfolio-Page.git
cd Portfolio-Page

# Build and run with Docker Compose
cd docker
docker compose up --build

# Open in browser
open http://localhost:8080
```

Editing `config/*.json` files reflects immediately (volume-mounted). No rebuild needed.

### Docker Image

```bash
# Build
docker build -t portfolio:latest -f docker/Dockerfile .

# Run (uses built-in defaults)
docker run -p 8080:80 portfolio:latest
```

Image size: **~98 MB** (multi-stage Alpine build).

---

## Configuration

### How It Works

The application uses a **two-tier configuration system**:

1. **Built-in defaults** — Generic placeholder data (`config-defaults/`) is baked into the Docker image.
2. **User overrides** — Custom `config/` files are mounted at runtime (via Docker volumes, Kubernetes ConfigMaps, or PVCs).

On container startup, the entrypoint script checks `config/` for user-provided files. Any file present in `config/` overrides the corresponding default. Missing files are copied from `config-defaults/`, so the application always has a complete set of configuration.

This means:
- **You don't need to mount every file** — only the ones you want to customize.
- **The image works out of the box** with generic "John Doe" placeholder data.
- **Your custom data never enters the Docker image** — it stays in your repo, your secrets, your cluster.

### File Reference

| File | Purpose |
|------|---------|
| `site.json` | Site title, navigation, social links, color palette, footer |
| `bio.json` | Name, nickname, headline, company, avatar, summary, highlights, skills |
| `education.json` | Education entries: degree, institution, year, description |
| `experience.json` | Work experience: role, company, period, achievements, technologies |
| `media.json` | Media showcase: projects (images) and videos (Peertube embeds) |
| `strings.en.json` | English translations (base language) |
| `strings.pt.json` | Portuguese translations (overrides base) |
| `strings.es.json` | Spanish translations (overrides base) |

### Docker Compose Setup

Edit the files in the `config/` directory to customize your portfolio. The `docker-compose.yml` mounts each file individually so changes reflect immediately:

```bash
# Edit your config
vim config/site.json
vim config/bio.json

# Restart to pick up changes
docker compose restart
```

You only need to mount the files you've customized. The entrypoint will copy any missing files from the built-in defaults.

### Kubernetes Setup

For production, create a ConfigMap with your custom configuration:

```bash
# Create a ConfigMap from your local config files
kubectl create configmap portfolio-config \
  --from-file=config/site.json \
  --from-file=config/bio.json \
  --from-file=config/education.json \
  --from-file=config/experience.json \
  --from-file=config/media.json \
  --from-file=config/strings.en.json \
  --from-file=config/strings.pt.json \
  --from-file=config/strings.es.json \
  -n choppa

# Apply remaining manifests
kubectl apply -f k8s/pvc.yaml -n choppa
kubectl apply -f k8s/deployment.yaml -n choppa
kubectl apply -f k8s/service.yaml -n choppa
kubectl apply -f k8s/ingress.yaml -n choppa
```

If you haven't customized `config/` yet, you can use the built-in defaults instead:

```bash
kubectl create configmap portfolio-config \
  --from-file=config-defaults/site.json \
  --from-file=config-defaults/bio.json \
  --from-file=config-defaults/education.json \
  --from-file=config-defaults/experience.json \
  --from-file=config-defaults/media.json \
  --from-file=config-defaults/strings.en.json \
  --from-file=config-defaults/strings.pt.json \
  --from-file=config-defaults/strings.es.json \
  -n choppa
```

The `deployment.yaml` mounts the ConfigMap at `/var/www/html/config`, which overrides the built-in defaults.

### Editing Configuration

#### site.json

```json
{
  "title": "Your Name — Portfolio",
  "tagline": "Your Role",
  "description": "A short description of yourself",
  "languages": ["en", "pt", "es"],
  "default_language": "en",
  "timezone": "America/Sao_Paulo",
  "navigation": [
    { "label": "nav.bio", "route": "bio" },
    { "label": "nav.education", "route": "education" },
    { "label": "nav.experience", "route": "experience" },
    { "label": "nav.media", "route": "media" }
  ],
  "social": {
    "github": "https://github.com/yourusername",
    "gitlab": "https://gitlab.com/yourusername",
    "linkedin": "https://linkedin.com/in/yourusername",
    "peertube": "https://your-tube.choppa.xyz",
    "email": "mailto:you@example.com"
  },
  "colors": {
    "primary": "#f5c518",
    "secondary": "#1a1a2e",
    "accent": "#00b4d8",
    "background": "#0d0d1a",
    "surface": "#16213e",
    "text": "#e0e0e0",
    "text-muted": "#a0a0b0",
    "border": "#2a2a4a"
  },
  "footer": "© 2026 Your Name. All rights reserved."
}
```

#### bio.json

```json
{
  "name": "Your Full Name",
  "nickname": "yournickname",
  "headline": "Your Job Title",
  "company": "Your Company or Self-Employed",
  "avatar": "/images/bio/avatar.webp",
  "summary": "A short bio paragraph...",
  "highlights": [
    { "icon": "code", "title": "Skill Area", "description": "Brief description" }
  ],
  "skills": [
    { "category": "Languages", "items": ["PHP", "JavaScript", "Python"] }
  ]
}
```

#### education.json

```json
[
  {
    "year": "2018 – 2022",
    "degree": "Bachelor of Science",
    "institution": "University Name",
    "location": "City, Country",
    "description": "Optional description",
    "icon": "graduation-cap"
  }
]
```

Valid `icon` values: `graduation-cap`, `certificate`, `check`.

#### experience.json

```json
[
  {
    "period": "2023 – Present",
    "role": "Your Role",
    "company": "Company Name",
    "location": "City, Country",
    "remote": true,
    "description": "Brief description of the role",
    "achievements": [
      "Key achievement 1",
      "Key achievement 2"
    ],
    "technologies": ["PHP", "Docker", "Kubernetes"],
    "link": "https://company.com"
  }
]
```

#### media.json

```json
{
  "categories": [
    { "id": "projects", "label": "nav.media.projects", "icon": "folder" },
    { "id": "videos",   "label": "nav.media.videos", "icon": "video" }
  ],
  "items": [
    {
      "id": "unique-id",
      "type": "image",
      "category": "projects",
      "title": "Project Title",
      "description": "Project description",
      "image": "/images/projects/project.webp",
      "thumbnail": "/images/projects/project-thumb.webp",
      "tags": ["PHP", "Docker"],
      "links": {
        "github": "https://github.com/user/project",
        "live": "https://example.com"
      },
      "date": "2025-01-01"
    },
    {
      "id": "video-id",
      "type": "video",
      "category": "videos",
      "title": "Video Title",
      "description": "Video description",
      "embedUrl": "https://tube.choppa.xyz/videos/embed/video-id",
      "thumbnail": "/images/media/video-thumb.webp",
      "tags": ["Demo"],
      "links": {
        "peertube": "https://tube.choppa.xyz/videos/watch/video-id"
      },
      "date": "2025-01-01"
    }
  ]
}
```

### Images

Images are stored in `public/images/` and gitignored (mounted at runtime). The expected structure:

```
public/images/
├── bio/
│   └── avatar.webp
├── projects/
│   ├── project-slug.webp
│   ├── project-slug-thumb.webp
│   ├── project-slug-medium.webp
│   └── project-slug-large.webp
└── media/
    └── video-slug-thumb.webp
```

Supported formats: WebP, PNG, JPEG.

### Thumbnail Generation

Use the built-in script to convert and resize images:

```bash
# Single file
php scripts/generate-thumbnails.php public/images/projects/my-project.jpg

# Entire directory (recursive)
php scripts/generate-thumbnails.php public/images/projects/

# Multiple files
php scripts/generate-thumbnails.php file1.jpg file2.png
```

For each source image, the script generates:
- `{name}.webp` — original quality, full resolution
- `{name}-thumb.webp` — 300×200 (gallery card)
- `{name}-medium.webp` — 800×533 (medium display)
- `{name}-large.webp` — 1600×1067 (lightbox)

All outputs are center-cropped to fit the target aspect ratio and saved as WebP at 85% quality.

> **Requirements:** PHP 8.2+ with GD extension and WebP support (`gd_info()['WebP Support']`).

### Adding Media Content

See **[MEDIA.md](../MEDIA.md)** for the complete publishing guide:

1. **Prepare images** — Use `generate-thumbnails.php` to create optimized WebP files
2. **Register entries** — Add items to `config/media.json` with correct paths
3. **Upload** — Place files in the mounted `public/images/` directory
4. **Verify** — Refresh the media page to see your content

---

## Project Structure

```
Portfolio-Page/
├── config/                        # User-provided config (gitignored, mounted at runtime)
│   ├── site.json                  # Site settings, navigation, social links, colors
│   ├── bio.json                   # Biography, highlights, skills
│   ├── education.json             # Education entries
│   ├── experience.json            # Work experience entries
│   ├── media.json                 # Media showcase (projects + videos)
│   ├── strings.en.json            # English translations (base)
│   ├── strings.pt.json            # Portuguese translations
│   └── strings.es.json            # Spanish translations
│
├── config-defaults/               # Built-in generic placeholders (baked into Docker image)
│   ├── site.json
│   ├── bio.json
│   ├── education.json
│   ├── experience.json
│   ├── media.json
│   ├── strings.en.json
│   ├── strings.pt.json
│   └── strings.es.json
│
├── public/                        # Web root
│   ├── index.php                  # Front controller / router
│   ├── assets/
│   │   ├── css/style.css          # Main stylesheet (dark theme)
│   │   ├── js/main.js             # Mobile nav toggle, media tabs, lightboxes
│   │   └── favicon.svg            # SVG favicon
│   └── images/                    # Image assets (gitignored, mounted at runtime)
│
├── includes/                      # PHP core (no framework)
│   ├── data-loader.php            # JSON file loader + language string merger
│   ├── helpers.php                # t(), base_url(), get_language(), get_route(), e()
│   ├── template.php               # Simple render() function
│   └── router.php                 # Route dispatch (bio, education, experience, media, 404)
│
├── views/                         # HTML templates
│   ├── layout.php                 # Master layout (nav, footer, social icons)
│   ├── bio.php                    # Bio page
│   ├── _bio_content.php           # Bio content (avatar, highlights, skills)
│   ├── education.php              # Education timeline
│   ├── experience.php             # Experience timeline
│   ├── media.php                  # Media showcase (gallery + video lightbox)
│   └── 404.php                    # Custom 404 page
│
├── docker/
│   ├── Dockerfile                 # php:8.2-fpm-alpine + nginx
│   ├── entrypoint.sh              # Merges user config overrides with defaults
│   ├── docker-compose.yml         # Local dev with volume mounts
│   ├── nginx.conf                 # Nginx base config (http block)
│   ├── server.conf                # Nginx server block (conf.d/)
│   └── www.conf                   # PHP-FPM pool config
│
├── k8s/                           # Kubernetes manifests (templates)
│   ├── deployment.yaml            # Deployment (1 replica, health probes)
│   ├── service.yaml               # ClusterIP service
│   ├── ingress.yaml               # Traefik ingress + cert-manager TLS
│   └── pvc.yaml                   # Persistent volume for images
│
├── MEDIA.md                       # Media publishing guide (step-by-step)
│
├── scripts/
│   └── generate-thumbnails.php    # Batch thumbnail generation (GD + WebP)
│
├── .github/workflows/docker.yml   # GitHub Actions: build + push Docker image
├── .gitignore
├── PLAN.md                        # Full project plan & architecture
└── README.md                      # This file
```

---

## Architecture

### Request Flow

```
Browser → Nginx (static assets) / PHP-FPM (dynamic pages)
                    │
                    ▼
            index.php (front controller)
                    │
          ┌─────────┼─────────┐
          ▼         ▼         ▼
    load_config  get_route  get_language
          │         │         │
          ▼         ▼         ▼
    router.php → dispatch() → render()
                    │
                    ▼
            HTML output
```

### Router

| URL Path | Route | View |
|----------|-------|------|
| `/` or `/bio` | `bio` | `views/bio.php` |
| `/education` | `education` | `views/education.php` |
| `/experience` | `experience` | `views/experience.php` |
| `/media` | `media` | `views/media.php` |
| Any other | `404` | `views/404.php` |

Language can be overridden via query parameter: `/?lang=pt` or `/?lang=es`.

---

## Customization

### Adding a New Page

1. Create `config-defaults/newpage.json` with your data
2. Create `views/newpage.php` template
3. Add case to `includes/router.php` dispatch switch
4. Add navigation entry to `config/site.json` (or `config-defaults/site.json`)

No changes to the framework layer needed.

### Language Files

Each `strings.{en,pt,es}.json` uses dot-notation keys matching `t('key.subkey')` calls:

```php
t('nav.bio')       → "Bio"
t('bio.headline')  → "Software Developer"
t('common.back')   → "Back"
```

The base language is `strings.en.json`. Other languages override specific keys via `array_replace_recursive`.

### Changing Colors

Edit the `colors` object in `site.json`. The CSS uses CSS custom properties that map directly:

| site.json key | CSS variable | Default |
|---------------|-------------|---------|
| `primary` | `--color-primary` | `#f5c518` |
| `secondary` | `--color-secondary` | `#1a1a2e` |
| `accent` | `--color-accent` | `#00b4d8` |
| `background` | `--color-bg` | `#0d0d1a` |
| `surface` | `--color-surface` | `#16213e` |
| `text` | `--color-text` | `#e0e0e0` |
| `text-muted` | `--color-text-muted` | `#a0a0b0` |
| `border` | `--color-border` | `#2a2a4a` |

---

## Docker & Kubernetes

### Docker Image

- **Base:** `php:8.2-fpm-alpine`
- **Size:** ~98 MB
- **PHP Extensions:** `intl`, `mbstring`, `gd` (configured with `--with-webp --with-jpeg --with-freetype` for full image support)
- **Runtime:** Nginx + PHP-FPM in a single container
- **User:** `www-data` (non-root)
- **Config Strategy:** Built-in defaults + runtime overrides via volume mount

### Kubernetes Deployment

The manifests in `k8s/` are configured for a **Traefik** ingress controller with **cert-manager** (Let's Encrypt) on a **k3s** cluster:

```bash
# Create ConfigMap with your custom config
kubectl create configmap portfolio-config \
  --from-file=config/site.json \
  --from-file=config/bio.json \
  --from-file=config/education.json \
  --from-file=config/experience.json \
  --from-file=config/media.json \
  --from-file=config/strings.en.json \
  --from-file=config/strings.pt.json \
  --from-file=config/strings.es.json \
  -n choppa

# Apply remaining manifests
kubectl apply -f k8s/pvc.yaml -n choppa
kubectl apply -f k8s/deployment.yaml -n choppa
kubectl apply -f k8s/service.yaml -n choppa
kubectl apply -f k8s/ingress.yaml -n choppa
```

**Prerequisites:**
- k3s cluster with Traefik ingress controller
- cert-manager with `letsencrypt` cluster issuer
- DNS A records for your domains pointing to cluster IP
- Docker Hub credentials in `secrets.DOCKERHUB_USERNAME` / `secrets.DOCKERHUB_TOKEN` (for CI/CD)

---

## CI/CD

GitHub Actions automatically builds and pushes the Docker image on every push to `main`:

- **Image:** `ancapepe/portfolio:latest` (also tagged with commit SHA)
- **Platform:** `linux/amd64`
- **Cache:** GitHub Actions cache (`type=gha`)

---

## Security

- `config/`, `includes/`, `views/` directories are denied via Nginx
- Security headers: `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`
- Non-root container user (`www-data`)
- `display_errors = Off` in production
- Server tokens hidden (`server_tokens off`)

---

## Color Palette

| Token | Hex | Usage |
|-------|-----|-------|
| Primary | `#f5c518` | Links, accents, highlights |
| Background | `#0d0d1a` | Page background |
| Surface | `#16213e` | Cards, nav, footer |
| Accent | `#00b4d8` | Secondary accents, skill tags |
| Text | `#e0e0e0` | Body text |
| Text Muted | `#a0a0b0` | Descriptions, secondary text |
| Border | `#2a2a4a` | Dividers, card borders |

---

## License

Private repository. All rights reserved.
