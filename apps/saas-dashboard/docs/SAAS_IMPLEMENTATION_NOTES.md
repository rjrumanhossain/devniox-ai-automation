# Devniox AI SaaS Notes

## Product Modules

- Super Admin: customers, plans, usage, channels, billing, platform AI cost.
- Customer: own business, own AI keys, own channels, website package, auto replies.
- Home: product signal, channels, package entry.
- Dashboard: client usage, channels, providers, automation queue.
- Clients: business account, plan, API key, channel map.
- Plans: monthly and yearly package limits.
- Channels: Messenger, Instagram, WhatsApp, Website Chat.
- AI Providers: Devniox managed key, OpenAI BYOK, Claude BYOK.
- Billing: MRR, trial, past due, yearly summary.
- Docs: setup checklist for client, channel, AI, support flow.
- Settings: tenant domains, webhook secret, token guard, realtime events.

## Backend Shape

- `GET /api/v1/overview`: dashboard data.
- `GET /api/v1/super-admin/overview`: platform owner data.
- `GET /api/v1/customer/overview`: customer business data.
- `GET /api/v1/documentation`: setup documentation data.
- `users.role`: `super_admin` or `customer`.
- `plans`: package and limits.
- `businesses`: client workspace.
- `business_api_keys`: client package auth.
- `channel_connections`: Messenger, Instagram, WhatsApp, Website Chat credentials.
- `ai_provider_credentials`: OpenAI, Claude, managed platform credentials.
- `usage_ledgers`: messages, tokens, cost tracking.

## Real Integration Order

1. Auth and owner account.
2. Role guard for super admin and customer.
3. Business create and plan attach.
4. API key generate and package validation.
5. Customer OpenAI and Claude key vault.
6. Meta OAuth for Facebook pages.
7. Messenger webhook receive and reply.
8. Instagram DM webhook receive and reply.
9. WhatsApp Cloud API connect and reply.
10. Website live chat receive and reply.
11. Token budget guard.
12. Live handoff and realtime notification.
13. Billing cycle and invoice.
14. Production deployment.

## Realtime Plan

- Use Laravel events for message received, AI replied, handoff requested.
- Use Laravel Reverb or Pusher for React notifications.
- Keep webhook receive fast and push AI processing to queue.
- Store all provider credentials encrypted.

## Package Plan

- Client installs `devniox/ai-automation`.
- Client adds Devniox API key from SaaS dashboard.
- Package validates website domain and business key.
- Package sends website chat events to SaaS API.
- SaaS replies through package widget or external channels.

## Auto Reply Flow

- Customer connects own OpenAI or Claude key.
- Customer connects Messenger, Instagram, WhatsApp, or Website Chat.
- Incoming customer message is stored under the business.
- SaaS reads business knowledge, product data, FAQ, and conversation memory.
- SaaS generates the answer with the selected AI provider.
- SaaS sends the answer back to the same channel.
- Human handoff starts only when AI confidence, package rule, or customer setting requires it.
