# Render deploy image for point-note (Laravel + Inertia/React).
#
# PHP 8.4, not the 8.2 floor composer.json declares: composer.lock (built
# on this project's local PHP 8.4) resolved maennchen/zipstream-php and
# symfony/css-selector at versions that require PHP 8.3+/8.4+. `composer
# install` (unlike `update`) verifies the lock against the *exact* running
# PHP version and refuses to install on a mismatch — confirmed by actually
# running this build locally, not assumed. Matching local's PHP avoids
# fighting the lockfile for no benefit, since 8.4 already satisfies "8.2+".
#
# Single stage, not multi-stage: Node has to be present at *runtime*, not
# just build time — Browsershot (PDF export) shells out to a Node script
# (vendor/spatie/browsershot/bin/browser.cjs) on every export request, so
# there's no image-size win from splitting Node into a separate build stage.
FROM php:8.4-cli-bookworm

# System packages:
# - git/unzip/curl: needed by Composer and the PHP extension installer
# - chromium: PDF export renders via headless Chrome, not dompdf — dompdf
#   has no complex-text-shaping engine and can't render Khmer script
#   correctly (subscript consonant stacking, vowel reordering). See
#   app/Http/Controllers/PointTrackerController.php exportPdf().
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl chromium \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions this app actually needs — verified against the full
# dependency tree with `composer check-platform-reqs`, not guessed:
# pdo_pgsql for Supabase; mbstring/xml/dom/simplexml/xmlreader/xmlwriter/
# zip/gd for phpoffice/phpspreadsheet's .xlsx export; bcmath/opcache as
# standard Laravel extras. mlocati's installer resolves the right -dev
# system packages per Debian release so we don't have to hardcode them.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql mbstring xml dom simplexml xmlreader xmlwriter zip gd bcmath opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node 20, for `npm run build` at image build time AND for Browsershot at
# request time (see comment above).
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .

# Skip Puppeteer's own ~300MB Chromium download — use the apt-installed one
# above. Unlike Nix (hash-based store paths), apt installs Chromium to a
# fixed, known path, so this can be a plain build-time ENV rather than
# something resolved at container start.
ENV PUPPETEER_SKIP_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci \
    && npm run build \
    && npm prune --omit=dev

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# config/route cache read from env vars at *container start* (Render
# injects DB_*/APP_KEY/etc. at runtime, not build time) — caching them
# during the build would bake in blank values. Same reasoning as the
# earlier Railway nixpacks.toml. Migrations are run manually from Render's
# shell (see deployment checklist), not on every boot.
CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
