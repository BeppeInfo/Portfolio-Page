#!/bin/sh
# entrypoint.sh — Merge user-provided config overrides with defaults.
#
# Strategy:
#   1. config-defaults/ contains generic placeholder data baked into the image.
#   2. config/ is the runtime config directory, exposed as a volume mount.
#   3. On startup, any file present in config/ overrides the corresponding
#      file in config-defaults/. Files only in config-defaults/ are copied
#      to config/ so the application always has a complete set of config files.
#
# Usage:
#   - For local dev: mount your custom config/*.json into /var/www/html/config/
#   - For Kubernetes: mount a ConfigMap/PVC containing your custom files
#   - If config/ is empty or missing, defaults are copied in automatically.

set -e

CONFIG_DIR="/var/www/html/config"
DEFAULTS_DIR="/var/www/html/config-defaults"

if [ -d "$DEFAULTS_DIR" ] && [ -d "$CONFIG_DIR" ]; then
    # Copy defaults into config/ for any files that don't exist yet
    for src in "$DEFAULTS_DIR"/*.json; do
        [ -f "$src" ] || continue
        filename=$(basename "$src")
        dest="$CONFIG_DIR/$filename"
        if [ ! -f "$dest" ]; then
            cp "$src" "$dest"
            echo "Copied default: $filename"
        else
            echo "Using user override: $filename"
        fi
    done
fi

exec "$@"
