# SPORT LIVE Project

## Branch-Based Deployment

Every git branch automatically gets deployed to a unique subdomain.

### IMPORTANT: Always Provide Deployment URL

**You MUST provide the deployment URL when:**
- Creating a new branch → immediately tell user the URL
- Creating a pull request → include URL in PR description and tell user
- User asks for URL → provide it

### URL Construction

**Step-by-step:**
1. Take the branch name (e.g., `feature/my_thing`)
2. Replace `/` and `_` with `-` → `feature-my-thing`
3. Convert to lowercase
4. Truncate to 63 characters if needed
5. URL = `https://{result}.sportlive.upanishad.hr`

**Special case:** `main` branch → `https://sportlive.upanishad.hr` (no subdomain)

**Examples:**

| Branch | URL |
|--------|-----|
| `main` | https://sportlive.upanishad.hr |
| `develop` | https://develop.sportlive.upanishad.hr |
| `feature/login` | https://feature-login.sportlive.upanishad.hr |
| `matej/cool_thing` | https://matej-cool-thing.sportlive.upanishad.hr |
| `fix/sportlive-bug` | https://fix-sportlive-bug.sportlive.upanishad.hr |

### Deployments Dashboard

View all active deployments at: https://deployments.sportlive.upanishad.hr
- **Auth:** admin / letsgo

## Infrastructure

### Hetzner Server
- **IP:** 95.217.135.225
- **Location:** Helsinki (hel1)
- **OS:** Ubuntu 24.04
- **Type:** CX22 (2 vCPU, 4GB RAM)

### Caddy + Wildcard SSL

Caddy handles wildcard domains with automatic SSL certificates via DNS-01 challenge.

**Main Caddyfile** (`/etc/caddy/Caddyfile`):
```caddyfile
{
    email admin@upanishad.hr
    acme_dns netlify {env.NETLIFY_API_TOKEN}
}

import /etc/caddy/sites/*.caddy
```

**Per-branch config** (`/etc/caddy/sites/sportlive-{branch}.caddy`):
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

**How wildcard SSL works:**
- DNS: `*.sportlive.upanishad.hr` → A → 95.217.135.225 (managed via Netlify)
- Caddy uses Netlify DNS plugin for ACME DNS-01 challenge
- Netlify API token stored in `/etc/caddy/environment`
- Each new subdomain automatically gets SSL without manual intervention

### Deployment Flow

```
Push to any branch
    ↓
GitHub Actions (.github/workflows/deploy.yml)
    ↓ npm ci && npm run build
    ↓ rsync dist/ to /var/www/sportlive-{branch}/
    ↓ Write Caddy config to /etc/caddy/sites/
    ↓ caddy reload
    ↓
Live at https://{branch}.sportlive.upanishad.hr
```

### Cleanup on Branch Delete

When a branch is deleted:
1. Remove `/var/www/sportlive-{branch}/`
2. Remove `/etc/caddy/sites/sportlive-{branch}.caddy`
3. Reload Caddy
4. Main branch deletion is protected (skipped)

### Secrets (GitHub Actions)

- `SSH_PRIVATE_KEY`: SSH key for root@95.217.135.225
- `SERVER_HOST`: 95.217.135.225

### Hetzner API Key

Located on server at `/root/.secrets/upanishad.env`. To retrieve:
```bash
scp root@95.217.135.225:/root/.secrets/upanishad.env .env
```

## Development

```bash
npm install
npm run dev      # Start dev server
npm run build    # Build for production
```

## Tech Stack

- Astro
- TailwindCSS
- TypeScript
