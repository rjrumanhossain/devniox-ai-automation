# Devniox AI Automation

Monorepo for the Devniox AI Automation SaaS platform and Laravel connector package.

## Structure

- `packages/ai-automation` - Composer package installed on client Laravel websites.
- `apps/saas-dashboard` - Devniox-owned Laravel + React SaaS dashboard.
- `docs` - product roadmap and build notes.

## Current Direction

The package becomes the website connector. The SaaS dashboard owns client accounts, API keys, packages, Messenger, WhatsApp, AI settings, product sync, usage, and billing.

## Package Commands

```bash
composer test
composer check-format
```

## SaaS Dashboard Commands

```bash
cd apps/saas-dashboard
php artisan serve
npm install
npm run dev
```

