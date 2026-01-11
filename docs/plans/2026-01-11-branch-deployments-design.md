# Branch-Based Deployment Design

## Overview

Automatic deployments for sportlive-hr based on git branch pushes to Hetzner server.

## URL Mapping

| Branch | URL |
|--------|-----|
| `main` | `sportlive.upanishad.hr` |
| `develop` | `develop.sportlive.upanishad.hr` |
| `feature/login` | `feature-login.sportlive.upanishad.hr` |
| `matej/cool-thing` | `matej-cool-thing.sportlive.upanishad.hr` |

**Branch name transformation:**
- Replace `/` and `_` with `-`
- Lowercase everything
- Truncate to 63 chars (DNS label limit)

## Architecture

```
GitHub Actions (build)
    ↓ rsync + ssh
Hetzner Server (95.217.135.225)
    ↓
Caddy (reverse proxy + static files + SSL)
    ↓
/var/www/sportlive-{branch}/
```

## Server Structure

```
/etc/caddy/
├── Caddyfile              # main config
├── environment            # NETLIFY_API_TOKEN for DNS challenge
└── sites/
    ├── nextcloud.caddy    # cloud.upanishad.hr
    ├── sportlive-main.caddy
    ├── sportlive-develop.caddy
    └── sportlive-{branch}.caddy

/var/www/
├── nextcloud/             # existing
├── sportlive/             # main branch
├── sportlive-develop/
└── sportlive-{branch}/
```

## Caddy Configuration

**Main Caddyfile:**
```caddyfile
{
    email admin@upanishad.hr
    acme_dns netlify {env.NETLIFY_API_TOKEN}
}

import /etc/caddy/sites/*.caddy
```

**Nextcloud (nextcloud.caddy):**
```caddyfile
cloud.upanishad.hr {
    root * /var/www/nextcloud
    php_fastcgi unix//run/php/php8.3-fpm.sock
    file_server

    redir /.well-known/carddav /remote.php/dav 301
    redir /.well-known/caldav /remote.php/dav 301

    header Strict-Transport-Security "max-age=31536000"
}
```

**Sportlive branch (sportlive-{branch}.caddy):**
```caddyfile
{subdomain}.sportlive.upanishad.hr {
    root * /var/www/sportlive-{branch}
    file_server
    try_files {path} /index.html

    header X-Frame-Options "DENY"
    header X-Content-Type-Options "nosniff"
    header Referrer-Policy "strict-origin-when-cross-origin"
}
```

## GitHub Actions Workflow

**Triggers:**
- `push` to any branch → deploy
- `delete` branch → cleanup

**Deploy steps:**
1. Checkout code
2. Setup Node, install deps, build Astro
3. Transform branch name → subdomain
4. SSH: Create directory if needed
5. Rsync `dist/` to `/var/www/sportlive-{branch}/`
6. SSH: Write Caddy config to `/etc/caddy/sites/`
7. SSH: `caddy reload`

**Cleanup steps (on branch delete):**
1. Transform deleted branch name → subdomain
2. SSH: Remove `/var/www/sportlive-{branch}/`
3. SSH: Remove `/etc/caddy/sites/sportlive-{branch}.caddy`
4. SSH: `caddy reload`

## GitHub Secrets

| Secret | Purpose |
|--------|---------|
| `SSH_PRIVATE_KEY` | SSH key for root@95.217.135.225 |
| `SERVER_HOST` | 95.217.135.225 |

## DNS Setup

Add wildcard record via Netlify API:
```
*.sportlive.upanishad.hr  →  A  →  95.217.135.225
```

## SSL

Caddy handles wildcard certificate automatically using:
- ACME DNS-01 challenge
- Netlify DNS plugin
- Token stored in `/etc/caddy/environment`

## Migration Steps

1. Install Caddy on server
2. Add wildcard DNS record
3. Create Caddyfile with Nextcloud config
4. Test Nextcloud via Caddy on alternate port
5. Stop Nginx, start Caddy on 80/443
6. Verify Nextcloud + sportlive work
7. Add GitHub secrets
8. Create GitHub Actions workflow
9. Test with push to develop branch
