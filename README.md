# Portfolio-Page

> A lightweight, self-hosted PHP portfolio website, containerized for Kubernetes.

**Author:** LeonardoJC  
**Repo:** [github.com/BeppeInfo/Portfolio-Page](https://github.com/BeppeInfo/Portfolio-Page)  
**Docker:** [docker.io/ancapope/portfolio](https://hub.docker.com/r/ancapope/portfolio)  
**Target Domains:** `services.choppa.xyz`, `beppeinfo.choppa.xyz`

---

## Overview

A data-driven, zero-dependency portfolio site built with pure PHP 8.2+, Nginx, and PHP-FPM. All content is managed through JSON configuration files — no database, no Composer, no frameworks. Designed to run as a single Docker image under 100 MB and deploy seamlessly to Kubernetes (k3s).

### Features

- **Multi-language** — English, Portuguese, Spanish (detects `Accept-Language`, switchable via nav)
- **Dark theme** — Custom CSS properties (dark gray `#0d0d1a`, yellow `#f5c518`, teal/cyan `#00b4d8`)
- **Data-driven** — Edit JSON files to update content; no code changes needed
- **Container-native** — Single Docker image with Nginx + PHP-FPM, non-root user, health checks
- **Peertube integration** — Video embeds via `https://tube.choppa.xyz`
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

# Run
docker run -p 8080:80 portfolio:latest
```

Image size: **~98 MB** (multi-stage Alpine build).

---

## Project Structure

```
Portfolio-Page/
├── config/                        # All editable data files (JSON)
│   ├── site.json                  # Site settings, navigation, social links, colors
│   ├── bio.json                   # Biography, highlights, skills
│   ├── strings.en.json            # English translations
│   ├── strings.pt.json            # Portuguese translations
│   └── strings.es.json            # Spanish translations
│
├── public/                        # Web root
│   ├── index.php                  # Front controller / router
│   ├── assets/
│   │   ├── css/style.css          # Main stylesheet (dark theme)
│   │   ├── js/main.js             # Mobile nav toggle, language switcher
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
│   ├── education.php              # Education (placeholder)
│   ├── experience.php             # Experience (placeholder)
│   ├── media.php                  # Media showcase (placeholder)
│   └── 404.php                    # Custom 404 page
│
├── docker/
│   ├── Dockerfile                 # Multi-stage: php:8.2-fpm-alpine + nginx
│   ├── docker-compose.yml         # Local dev with volume mounts
│   ├── nginx.conf                 # Nginx base config (http block)
│   ├── server.conf                # Nginx server block (conf.d/)
│   └── www.conf                   # PHP-FPM pool config
│
├── k8s/                           # Kubernetes manifests
│   ├── deployment.yaml            # Deployment (1 replica, health probes)
│   ├── service.yaml               # ClusterIP service
│   ├── ingress.yaml               # Traefik ingress + cert-manager TLS
│   ├── pvc.yaml                   # Persistent volume for images
│   └── configmap.yaml             # ConfigMap with site.json
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

## Configuration

### Adding a New Page

1. Create `config/newpage.json` with your data
2. Create `views/newpage.php` template
3. Add case to `includes/router.php` dispatch switch
4. Add navigation entry to `config/site.json`

No changes to the framework layer needed.

### Language Files

Each `strings.{en,pt,es}.json` uses dot-notation keys matching `t('key.subkey')` calls:

```php
t('nav.bio')       → "Bio"
t('bio.headline')  → "Software Developer"
t('common.back')   → "Back"
```

The base language is `strings.en.json`. Other languages override specific keys via `array_replace_recursive`.

---

## Docker & Kubernetes

### Docker Image

- **Base:** `php:8.2-fpm-alpine`
- **Size:** ~98 MB
- **PHP Extensions:** `intl`, `mbstring`, `gd` (with WebP/JPEG/PNG support)
- **Runtime:** Nginx + PHP-FPM in a single container
- **User:** `www-data` (non-root)

### Kubernetes Deployment

The manifests in `k8s/` are configured for a **Traefik** ingress controller with **cert-manager** (Let's Encrypt) on a **k3s** cluster:

```bash
# Apply all manifests to the choppa namespace
kubectl apply -f k8s/configmap.yaml -n choppa
kubectl apply -f k8s/pvc.yaml -n choppa
kubectl apply -f k8s/deployment.yaml -n choppa
kubectl apply -f k8s/service.yaml -n choppa
kubectl apply -f k8s/ingress.yaml -n choppa
```

**Prerequisites:**
- k3s cluster with Traefik ingress controller
- cert-manager with `letsencrypt` cluster issuer
- DNS A records for `services.choppa.xyz` and `beppeinfo.choppa.xyz` pointing to cluster IP
- Docker Hub credentials in `secrets.DOCKERHUB_USERNAME` / `secrets.DOCKERHUB_TOKEN` (for CI/CD)

---

## CI/CD

GitHub Actions automatically builds and pushes the Docker image on every push to `main`:

- **Image:** `ancapope/portfolio:latest` (also tagged with commit SHA)
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
