# Portfolio Page — Project Plan

> A lightweight, self-hosted PHP portfolio site, containerized for Kubernetes.

---

## Table of Contents

1. [Goals & Principles](#goals--principles)
2. [Architecture Overview](#architecture-overview)
3. [Project Structure](#project-structure)
4. [Data Model & Configuration](#data-model--configuration)
5. [Pages & Features](#pages--features)
6. [Media Handling](#media-handling)
7. [Styling & Theming](#styling--theming)
8. [Docker & Kubernetes](#docker--kubernetes)
9. [Development Workflow](#development-workflow)
10. [Implementation Phases](#implementation-phases)
11. [Future Extensibility](#future-extensibility)

---

## 1. Goals & Principles

| Principle | Detail |
|-----------|--------|
| **Zero dependencies** | No Composer, no frameworks — pure PHP 8.2+ with the built-in server for dev, Nginx for prod |
| **Data-driven** | All content lives in JSON/YAML config files — edit a file, refresh the page |
| **Static-first** | The entire site is essentially static HTML generated from data files; no database needed |
| **Container-native** | Single Docker image, non-root user, health checks, readiness probes |
| **Minimal footprint** | Target image size < 100 MB; no unnecessary layers |
| **Extensible** | New page types or sections can be added by creating a new data file + template |

---

## 2. Architecture Overview

```
┌──────────────────────────────────────────────────────┐
│                    Kubernetes                        │
│                                                      │
│  ┌─────────────┐     ┌─────────────────────────┐    │
│  │   Nginx     │────▶│        PHP              │    │
│  │  (static    │     │  (FastCGI / php-fpm)    │    │
│  │   assets)   │     │                         │    │
│  └──────┬──────┘     └──────────┬──────────────┘    │
│         │                       │                   │
│         │          ┌────────────┴────────┐          │
│         │          │   data/             │          │
│         │          │   ├── bio.json      │          │
│         │          │   ├── education.json│          │
│         │          │   ├── experience.json│         │
│         │          │   ├── media.json    │          │
│         │          │   └── site.json     │          │
│         │          └─────────────────────┘          │
│         │          ┌────────────┴────────┐          │
│         │          │   assets/           │          │
│         │          │   ├── images/       │          │
│         │          │   └── videos/       │          │
│         │          └─────────────────────┘          │
│                                                      │
└──────────────────────────────────────────────────────┘
```

### Request Flow

1. **Nginx** serves all static assets directly (images, CSS, JS, favicon)
2. **Nginx** proxies PHP requests to `php-fpm`
3. **PHP router** parses the URL, loads the corresponding data file(s), renders a template
4. **Output** is plain HTML — no API layer, no client-side JS framework needed

### Router Design

A single entry point (`index.php`) acts as a front controller:

```
/index.php?route=bio        → renders bio page
/index.php?route=education  → renders education page
/index.php?route=experience → renders experience page
/index.php?route=media      → renders media showcase
/index.php?route=media&type=video&id=123 → renders a single video detail
```

URLs can be made clean via Nginx rewrite rules:
```
/bio            → index.php?route=bio
/education      → index.php?route=education
/experience     → index.php?route=experience
/media          → index.php?route=media
/media/video/123 → index.php?route=media&type=video&id=123
```

---

## 3. Project Structure

```
Portfolio-Page/
├── PLAN.md                        # This file
├── README.md                      # Project readme
├── .gitignore
│
├── config/                        # All editable data files
│   ├── site.json                  # Site-wide settings (title, nav, colors, etc.)
│   ├── bio.json                   # Biography data
│   ├── education.json             # Education timeline
│   ├── experience.json            # Professional experience timeline
│   └── media.json                 # Media catalog (images + videos)
│
├── public/                        # Web root
│   ├── index.php                  # Front controller / router
│   ├── .htaccess                  # Apache fallback (optional)
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css          # Main stylesheet
│   │   ├── js/
│   │   │   └── main.js           # Minimal vanilla JS (lightbox, tabs, etc.)
│   │   └── fonts/                # Optional self-hosted fonts
│   │
│   ├── images/                   # Uploaded/managed images
│   │   ├── bio/                  # Profile photos, headshots
│   │   ├── projects/             # Project screenshots
│   │   └── media/                # Gallery images
│   │
│   └── favicon.ico
│
├── includes/                      # PHP includes (no framework)
│   ├── router.php                # URL parsing & dispatch
│   ├── template.php              # Template rendering engine
│   ├── helpers.php               # Utility functions
│   ├── data-loader.php           # JSON/YAML loader with caching
│   └── media-renderer.php        # Image gallery + video embed builder
│
├── views/                         # HTML template files
│   ├── layout.php                # Master layout (head, nav, footer)
│   ├── bio.php                   # Bio page template
│   ├── education.php             # Education page template
│   ├── experience.php            # Experience page template
│   ├── media.php                 # Media gallery template
│   ├── media/video.php           # Single video embed template
│   ├── media/image.php           # Single image detail template
│   └── 404.php                   # Not found page
│
├── docker/
│   ├── Dockerfile                # Multi-stage build
│   ├── docker-compose.yml        # Local dev (Nginx + PHP-FPM)
│   ├── nginx.conf                # Nginx configuration
│   └── php.ini                   # PHP-FPM tuning
│
├── k8s/                          # Kubernetes manifests
│   ├── deployment.yaml
│   ├── service.yaml
│   ├── ingress.yaml
│   ├── pvc.yaml                  # Persistent volume for media uploads
│   └── configmap.yaml            # Optional: externalize config
│
└── scripts/
    ├── generate-thumbnails.php   # Batch thumbnail generation
    └── validate-config.php       # Validate JSON config on deploy
```

---

## 4. Data Model & Configuration

### `config/site.json` — Site-wide settings

```json
{
  "title": "LeonardoJC — Portfolio",
  "tagline": "Software Developer & Engineer",
  "description": "Professional portfolio of LeonardoJC",
  "language": "en",
  "languages": ["en", "pt", "es"],
  "default_language": "en",
  "timezone": "America/Sao_Paulo",
  "navigation": [
    { "label": "Bio",       "route": "bio" },
    { "label": "Education", "route": "education" },
    { "label": "Experience","route": "experience" },
    { "label": "Media",     "route": "media" }
  ],
  "social": {
    "github":  "https://github.com/leonardojc",
    "linkedin":"https://linkedin.com/in/leonardojc",
    "peertube":"https://tube.choppa.xyz",
    "email":   "mailto:you@example.com"
  },
  "colors": {
    "primary":   "#f5c518",
    "secondary": "#1a1a2e",
    "accent":    "#00b4d8",
    "background":"#0d0d1a",
    "surface":   "#16213e",
    "text":      "#e0e0e0",
    "text-muted":"#a0a0b0"
  },
  "footer": "© 2025 LeonardoJC. All rights reserved."
}
```

### `config/bio.json` — Biography

```json
{
  "name": "LeonardoJC",
  "headline": "Software Developer",
  "avatar": "/images/bio/avatar.webp",
  "summary": "Full description of who you are, your philosophy, interests...",
  "highlights": [
    { "icon": "code", "title": "Full Stack", "description": "PHP, JavaScript, Docker..." },
    { "icon": "cloud", "title": "Cloud Native", "description": "Kubernetes, CI/CD..." },
    { "icon": "terminal", "title": "DevOps", "description": "Infrastructure as Code..." }
  ],
  "skills": [
    { "category": "Languages", "items": ["PHP", "JavaScript", "Python", "SQL"] },
    { "category": "Frameworks", "items": ["Laravel", "Silex", "Node.js"] },
    { "category": "Infrastructure", "items": ["Docker", "Kubernetes", "Nginx"] }
  ]
}
```

### `config/education.json` — Education timeline

```json
[
  {
    "year": "2015 – 2020",
    "degree": "Bachelor of Computer Science",
    "institution": "University Name",
    "location": "City, Country",
    "description": "Brief description, thesis, notable achievements",
    "icon": "graduation-cap"
  },
  {
    "year": "2021",
    "degree": "AWS Solutions Architect – Associate",
    "institution": "Amazon Web Services",
    "location": "Online",
    "description": "Certification details",
    "icon": "certificate"
  }
]
```

### `config/experience.json` — Professional experience

```json
[
  {
    "period": "2022 – Present",
    "role": "Senior Software Developer",
    "company": "Company Name",
    "location": "City, Country",
    "remote": true,
    "description": "Key responsibilities and achievements",
    "achievements": [
      "Led migration from monolith to microservices",
      "Reduced CI/CD pipeline time by 60%",
      "Mentored 5 junior developers"
    ],
    "technologies": ["PHP", "Docker", "Kubernetes", "PostgreSQL", "Redis"],
    "link": "https://company.com"
  },
  {
    "period": "2020 – 2022",
    "role": "Software Developer",
    "company": "Previous Company",
    "location": "City, Country",
    "remote": false,
    "description": "Description here",
    "achievements": [
      "Built REST API serving 100K+ requests/day"
    ],
    "technologies": ["Laravel", "MySQL", "Docker"],
    "link": "https://company.com"
  }
]
```

### `config/media.json` — Media catalog

```json
{
  "categories": [
    { "id": "projects", "label": "Projects", "icon": "folder" },
    { "id": "gallery",  "label": "Gallery",  "icon": "image" },
    { "id": "videos",   "label": "Videos",   "icon": "video" }
  ],
  "items": [
    {
      "id": "project-alpha",
      "type": "image",
      "category": "projects",
      "title": "Project Alpha",
      "description": "Open-source project description",
      "image": "/images/projects/alpha.webp",
      "thumbnail": "/images/projects/alpha-thumb.webp",
      "tags": ["PHP", "Laravel", "Docker"],
      "links": {
        "github": "https://github.com/leonardojc/project-alpha",
        "live":   "https://project-alpha.example.com"
      },
      "date": "2024-06-15"
    },
    {
      "id": "talk-about-kubernetes",
      "type": "video",
      "category": "videos",
      "title": "Talk: Kubernetes Best Practices",
      "description": "My talk at Conference 2024",
      "embedUrl": "https://your-peertube-instance.tube/videos/watch/abc123",
      "thumbnail": "/images/media/talk-k8s-thumb.webp",
      "tags": ["Kubernetes", "DevOps", "Talk"],
      "date": "2024-03-20"
    },
    {
      "id": "demo-app-dashboard",
      "type": "video",
      "category": "videos",
      "title": "Demo: Real-time Dashboard",
      "description": "Live demo of the real-time dashboard",
      "embedUrl": "https://your-peertube-instance.tube/videos/watch/def456",
      "thumbnail": "/images/media/demo-dashboard-thumb.webp",
      "tags": ["PHP", "WebSocket", "Demo"],
      "date": "2024-08-10"
    }
  ]
}
```

### Key design decisions for data:

- **JSON format** — native to PHP (`json_decode`), no external libraries needed
- **Arrays for timelines** (education, experience) — naturally ordered
- **Categorized items** (media) — flexible filtering
- **All dates as ISO 8601 strings** — easy sorting
- **External links for videos** — no video files stored locally; Peertube base URL: `https://tube.choppa.xyz`
- **Thumbnails stored locally** — even for Peertube embeds, for gallery view consistency

---

## 5. Pages & Features

### 5.1 Navigation Bar

- Responsive (hamburger on mobile)
- Active state highlighting
- Social icons in header/footer
- Smooth scroll for single-page fallback

### 5.2 Bio Page (`/bio`)

- Profile image (left on desktop, top on mobile)
- Headline + summary paragraph
- Skills grid (categorized)
- Highlight cards (3-4 key strengths)
- Headline + summary paragraph
- Skills grid (categorized)
- Highlight cards (3-4 key strengths)

### 5.3 Education Page (`/education`)

- Vertical timeline layout
- Each entry: year range, degree, institution, location
- Expandable descriptions
- Icons per entry type (degree, certificate, course)

### 5.4 Experience Page (`/experience`)

- Reverse-chronological timeline
- Each entry: period, role, company, location badge (remote/hybrid/on-site)
- Collapsible achievements list
- Technology tags (pill-style badges)
- Optional company link

### 5.5 Media Showcase (`/media`)

- **Category tabs** (Projects, Gallery, Videos)
- **Grid layout** with thumbnails
- **Filter by tags** (optional, JS-powered)
- **Lightbox** for images
- **Video lazy-load** for Peertube embeds (thumbnail placeholder → click to load iframe)
- **Infinite scroll or pagination** (if many items)

### 5.6 Image Detail View (`/media/image/{id}`)

- Full-size image
- Title + description
- Tags
- Related images (same category)
- Download button

### 5.7 Video Detail View (`/media/video/{id}`)

- Embedded Peertube player (iframe)
- Title + description
- Tags
- Link to original Peertube video
- Related videos

### 5.8 404 Page

- Clean, branded not-found page
- Link back to home

---

## 6. Media Handling

### 6.1 Images

| Aspect | Detail |
|--------|--------|
| **Format** | WebP preferred, with PNG/JPG fallback |
| **Storage** | Local `public/images/` directory |
| **Thumbnails** | Pre-generated via GD or Imagick (`generate-thumbnails.php`) |
| **Responsive** | Serve `srcset` with multiple sizes |
| **Lazy loading** | `loading="lazy"` on all images below the fold |
| **Lightbox** | Vanilla JS lightbox (no library) — click thumbnail → overlay with full image |

### 6.2 Videos (Peertube Embeds)

| Aspect | Detail |
|--------|--------|
| **Hosting** | External Peertube instance — no video files stored |
| **Embed method** | `<iframe>` using Peertube embed URL format: `{base}/videos/embed/{videoId}` |
| **Lazy load** | Placeholder thumbnail shown first; iframe loaded on click (improves initial page load) |
| **Thumbnail** | Local thumbnail image for gallery consistency |
| **Fallback** | Link to Peertube video if iframe fails |

### 6.3 Video Lazy-Load Pattern

```html
<!-- Gallery placeholder -->
<div class="video-placeholder" data-embed-url="https://peertube.example.com/videos/watch/abc123">
  <img src="/images/media/thumb.webp" alt="Video thumbnail" loading="lazy">
  <div class="play-button">▶</div>
</div>

<!-- On click, replace with iframe -->
<script>
document.querySelector('.video-placeholder').addEventListener('click', function() {
  const iframe = document.createElement('iframe');
  iframe.src = this.dataset.embedUrl;
  iframe.setAttribute('allow', 'autoplay; fullscreen');
  this.replaceWith(iframe);
});
</script>
```

### 6.4 Image Optimization Pipeline

```
Raw image → convert to WebP → generate thumbnails (small, medium, large) → deploy to public/images/
```

```php
// scripts/generate-thumbnails.php usage
php generate-thumbnails.php public/images/projects/alpha.jpg
// Outputs:
//   public/images/projects/alpha.webp        (original quality)
//   public/images/projects/alpha-thumb.webp  (300x200)
//   public/images/projects/alpha-medium.webp (800x533)
//   public/images/projects/alpha-large.webp  (1600x1067)
```

---

## 7. Styling & Theming

### 7.1 CSS Architecture

- **Single file** (`assets/css/style.css`) — no build step needed
- **CSS Custom Properties** for theming (colors, spacing, typography)
- **CSS Grid + Flexbox** for layouts
- **Mobile-first** responsive design
- **No external CSS frameworks** — keeps it lightweight

### 7.2 Dark Theme — Custom Properties

```css
:root {
  --color-primary:   #f5c518;   /* Yellow */
  --color-secondary: #1a1a2e;   /* Dark navy */
  --color-accent:    #00b4d8;   /* Teal/cyan */
  --color-bg:        #0d0d1a;   /* Near black */
  --color-surface:   #16213e;   /* Dark card bg */
  --color-text:      #e0e0e0;   /* Light gray text */
  --color-text-muted:#a0a0b0;   /* Muted text */
  --color-border:    #2a2a4a;
  --font-sans:       'Inter', system-ui, -apple-system, sans-serif;
  --font-mono:       'JetBrains Mono', monospace;
  --radius:          8px;
  --shadow:          0 2px 8px rgba(0,0,0,0.4);
  --max-width:       1200px;
}
```

### 7.3 Multi-Language Support

All translatable strings live in language files:

```
config/
├── site.json
├── strings.en.json   # English (default)
├── strings.pt.json   # Portuguese
└── strings.es.json   # Spanish
```

Language detection: `Accept-Language` header → fallback to `en`.
Language switcher in nav bar.

### 7.4 JavaScript (Vanilla, Minimal)

| Feature | Implementation |
|---------|---------------|
| Mobile nav toggle | Class toggle on `.nav` |
| Language switcher | JS reload with `?lang=pt` query param |
| Smooth scroll | `scroll-behavior: smooth` + JS for offset |
| Lightbox | Custom overlay with prev/next navigation |
| Video lazy-load | Click-to-iframe swap (see §6.3) |
| Tab filtering | CSS class toggle on gallery items |
| **Total JS** | < 300 lines, no dependencies |

### 7.4 JavaScript (Vanilla, Minimal)

| Feature | Implementation |
|---------|---------------|
| Mobile nav toggle | Class toggle on `.nav` |
| Smooth scroll | `scroll-behavior: smooth` + JS for offset |
| Lightbox | Custom overlay with prev/next navigation |
| Video lazy-load | Click-to-iframe swap (see §6.3) |
| Tab filtering | CSS class toggle on gallery items |
| **Total JS** | < 300 lines, no dependencies |

---

## 8. Docker & Kubernetes

### 8.1 Dockerfile (Multi-Stage)

```dockerfile
# Stage 1: Build (not needed for pure PHP, but kept for thumbnail generation)
FROM php:8.2-fpm-alpine AS base
RUN apk add --no-cache \
      icu-data-full \
      icu-libs \
    && docker-php-ext-install \
       intl \
       mbstring \
       gd

# Stage 2: Production
FROM nginx:1.25-alpine AS production
LABEL maintainer="LeonardoJC <you@example.com>"

# Copy Nginx config
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Copy application
COPY --from=base /usr/local/bin/docker-php-ext-* /usr/local/bin/
COPY . /var/www/html

# Set permissions
RUN chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html/public

# Expose ports
EXPOSE 80

USER nginx

CMD ["nginx", "-g", "daemon off;"]
```

### 8.2 docker-compose.yml (Local Development)

```yaml
version: '3.8'
services:
  app:
    build:
      context: .
      dockerfile: docker/Dockerfile
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html:cached
    environment:
      - APP_ENV=development
    restart: unless-stopped
```

### 8.3 Nginx Configuration

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php;

    # Gzip
    gzip on;
    gzip_types text/css application/javascript image/svg+xml;

    # Static assets — long cache
    location /assets/ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Images — medium cache
    location /images/ {
        expires 7d;
        add_header Cache-Control "public";
    }

    # PHP
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to config
    location /config/ {
        deny all;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Clean URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 8.4 Kubernetes Manifests

**Deployment:**
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: portfolio
spec:
  replicas: 1
  selector:
    matchLabels:
      app: portfolio
  template:
    metadata:
      labels:
        app: portfolio
    spec:
      containers:
        - name: portfolio
          image: leonardojc/portfolio:latest
          ports:
            - containerPort: 80
          resources:
            requests:
              memory: "64Mi"
              cpu: "50m"
            limits:
              memory: "128Mi"
              cpu: "100m"
          readinessProbe:
            httpGet:
              path: /
              port: 80
            initialDelaySeconds: 5
            periodSeconds: 10
          livenessProbe:
            httpGet:
              path: /
              port: 80
            initialDelaySeconds: 10
            periodSeconds: 30
```

**Service:**
```yaml
apiVersion: v1
kind: Service
metadata:
  name: portfolio
spec:
  selector:
    app: portfolio
  ports:
    - port: 80
      targetPort: 80
  type: ClusterIP
```

**Ingress:**
```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: portfolio
  annotations:
    nginx.ingress.kubernetes.io/rewrite-target: /
spec:
  rules:
    - host: services.choppa.xyz
    - host: beppeinfo.choppa.xyz
      http:
        paths:
          - path: /
            pathType: Prefix
            backend:
              service:
                name: portfolio
                port:
                  number: 80
```

### 8.5 Persistent Volume (for media uploads)

```yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: portfolio-media
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
---
# Mount in deployment:
# volumes:
#   - name: media
#     persistentVolumeClaim:
#       claimName: portfolio-media
# volumeMounts:
#   - name: media
#     mountPath: /var/www/html/public/images
```

### 8.6 CI/CD Pipeline (GitHub Actions)

```yaml
name: Deploy
on:
  push:
    branches: [main]
jobs:
  build-and-push:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Build Docker image
        run: docker build -t portfolio:latest .
      - name: Push to registry
        run: docker push portfolio:latest
  deploy:
    needs: build-and-push
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to Kubernetes
        run: kubectl apply -f k8s/ && kubectl set image deployment/portfolio portfolio=portfolio:latest
```

---

## 9. Development Workflow

### 9.1 Local Development

```bash
# 1. Clone & setup
cd Portfolio-Page

# 2. Start with Docker Compose
docker compose up --build

# 3. Open browser
open http://localhost:8080

# 4. Edit config files → refresh browser (no rebuild needed with volume mount)
```

### 9.2 Adding a New Page

1. Create `config/newpage.json` with your data
2. Create `views/newpage.php` template (extend `layout.php`)
3. Add route to `includes/router.php`
4. Add navigation entry to `config/site.json`
5. Done — no code changes in the framework layer needed

### 9.3 Adding Media

1. Place image in `public/images/` (or record Peertube embed URL)
2. Add entry to `config/media.json`
3. Thumbnail auto-generated (or manually via script)
4. Refresh — appears in gallery

### 9.4 Config Validation

```bash
php scripts/validate-config.php
# Validates all JSON files for syntax and required fields
# Run before deployment or via CI
```

---

## 10. Implementation Phases

### Phase 1 — Foundation (Core)

| # | Task | Estimated Effort |
|---|------|-----------------|
| 1 | Project structure & `.gitignore` | 30 min |
| 2 | `config/site.json` + `config/bio.json` | 30 min |
| 3 | Front controller (`index.php`) + router | 1 hour |
| 4 | Template engine (`includes/template.php`) | 1 hour |
| 5 | Master layout (`views/layout.php`) | 1 hour |
| 6 | Bio page template + data | 1 hour |
| 7 | Basic CSS (reset, typography, nav, layout) | 2 hours |
| 8 | Docker setup (Dockerfile, nginx.conf, compose) | 1.5 hours |
| 9 | Local dev with `docker compose` | 30 min |
| 10 | README.md | 30 min |

**Phase 1 deliverable:** A working site with navigation and bio page, running in Docker.

### Phase 2 — Content Pages

| # | Task | Estimated Effort |
|---|------|-----------------|
| 1 | `config/education.json` + education template | 1 hour |
| 2 | `config/experience.json` + experience template | 1.5 hours |
| 3 | Timeline UI (CSS) | 2 hours |
| 4 | Responsive design polish | 2 hours |
| 5 | Mobile nav (hamburger) | 1 hour |

**Phase 2 deliverable:** All content pages working, responsive on mobile.

### Phase 3 — Media Showcase

| # | Task | Estimated Effort |
|---|------|-----------------|
| 1 | `config/media.json` structure | 30 min |
| 2 | Media gallery template + CSS grid | 2 hours |
| 3 | Image lightbox (vanilla JS) | 1.5 hours |
| 4 | Video lazy-load + Peertube embed | 1.5 hours |
| 5 | Thumbnail generation script | 1 hour |
| 6 | Category tabs + tag filtering | 1 hour |

**Phase 3 deliverable:** Full media showcase with images and video embeds.

### Phase 4 — Production Hardening

| # | Task | Estimated Effort |
|---|------|-----------------|
| 1 | Kubernetes manifests | 2 hours |
| 2 | CI/CD pipeline (GitHub Actions) | 1.5 hours |
| 3 | Config validation script | 30 min |
| 4 | Performance optimization (gzip, caching, srcset) | 1 hour |
| 5 | Security hardening (deny access to config, headers) | 30 min |
| 6 | 404 page | 30 min |
| 7 | Final polish + documentation | 2 hours |

**Phase 4 deliverable:** Production-ready, deployable to Kubernetes.

---

## 11. Future Extensibility

### 11.1 Adding New Page Types

The data-driven architecture makes this trivial:

```
New page "Projects"
  ├── config/projects.json       ← data file
  ├── views/projects.php         ← template
  └── entry in site.json nav     ← registration
```

### 11.2 Plugin Hooks (Future)

Reserved hooks in the template engine for future features:

```php
// hooks available in template engine
before_content    // inject sidebar, etc.
after_content     // inject related content, comments, etc.
before_footer     // inject newsletter signup, etc.
```

### 11.3 Possible Future Features

| Feature | Complexity | Notes |
|---------|-----------|-------|
| **Blog** | Medium | Add `config/posts.json`, list view, detail view |
| **Contact form** | Medium | Server-side validation, email via SMTP or form endpoint |
| **Multi-language** | Implemented | `config/strings.{en,pt,es}.json` — detect `Accept-Language`, switcher in nav |
| **Search** | Low | Client-side JSON search (small dataset) |
| **Analytics** | Low | Plausible or Umami (self-hosted, privacy-friendly) |
| **Admin panel** | High | Password-protected JSON editor — probably overkill |
| **RSS feed** | Low | Generate from `config/experience.json` or blog posts |
| **Sitemap** | Low | Auto-generate from `config/site.json` navigation |

---

## Appendix A — File Size Targets

| Component | Target Size |
|-----------|-------------|
| Docker image (Nginx + PHP-FPM + app) | < 100 MB |
| CSS (minified) | < 15 KB |
| JS (minified) | < 5 KB |
| Total page weight (bio page) | < 50 KB (no images) |
| Total page weight (media page) | < 200 KB (thumbnails only) |

## Appendix B — PHP Version Requirements

| Extension | Purpose |
|-----------|---------|
| `intl` | Date formatting, sorting, locale detection |
| `gd` | Image thumbnail generation |
| `json` | Native — no install needed |
| `mbstring` | Multibyte string handling (UTF-8 for PT/ES) |

All included in `php:8.2-fpm-alpine` base image.

## Appendix C — Security Checklist

- [ ] `config/` directory not web-accessible (Nginx `deny all`)
- [ ] No `.git` exposure
- [ ] No PHP error exposure in production (`display_errors = Off`)
- [ ] `X-Content-Type-Options: nosniff` header
- [ ] `X-Frame-Options: DENY` header
- [ ] Non-root container user
- [ ] Read-only filesystem where possible (except `public/images/`)
- [ ] Input validation on any user-facing data

---

*Document created: 2025*
*Last updated: 2025-08-30*
*Status: Approved — ready for implementation*

### Updated Requirements Summary

| Item | Decision |
|------|----------|
| Languages | English (default), Portuguese, Spanish |
| Peertube instance | `https://tube.choppa.xyz` |
| Domains | `services.choppa.xyz` + `beppeinfo.choppa.xyz` |
| Image processing | PHP GD (built-in, lighter image) |
| CV download | Not needed |
| Theme | Dark — dark gray (`#0d0d1a`), yellow (`#f5c518`), teal/cyan (`#00b4d8`) |
| Repo host | GitHub |
| Multi-language | Implemented via `strings.{en,pt,es}.json` + `Accept-Language` detection + nav switcher |
