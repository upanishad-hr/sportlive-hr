# SPORT LIVE

Website for SPORT LIVE HR.

## Development

```bash
npm install
npm run dev      # Start dev server at localhost:4321
npm run build    # Build for production
```

## Deployment

Every branch is automatically deployed to its own subdomain.

| Branch | URL |
|--------|-----|
| `main` | https://sportlive.upanishad.hr |
| `develop` | https://develop.sportlive.upanishad.hr |
| `feature/x` | https://feature-x.sportlive.upanishad.hr |

**Branch name transformation:**
- Replace `/` and `_` with `-`
- Lowercase
- Max 63 characters

Deployments happen automatically on push via GitHub Actions. Branch deletion triggers automatic cleanup.

## Tech Stack

- [Astro](https://astro.build)
- [TailwindCSS](https://tailwindcss.com)
- TypeScript
