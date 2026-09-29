# Devniox AI Automation SaaS Roadmap

## Product Direction

Devniox AI Automation will become a central SaaS platform. Clients will not manage the automation from their own admin panels. They will buy a package, receive a client API key, connect their website, Facebook Messenger, and WhatsApp from the Devniox dashboard, and all automation will run from our cloud.

## Core Architecture

- `devniox/ai-automation` package: installed on client Laravel websites as a connector.
- SaaS dashboard: Laravel API + React dashboard owned by Devniox.
- Client API key: generated per subscription and used by the client package.
- Cloud API: receives website chat, product sync, leads, and usage data.
- Meta webhooks: Messenger and WhatsApp webhooks point to the SaaS dashboard.
- AI gateway: OpenAI-compatible provider runs from the SaaS account, not the client server.
- Billing and limits: package, token, channel, and usage limits enforced centrally.

## Dashboard Rules

- No marketing homepage inside the app.
- No explanatory paragraphs inside operational screens.
- Use compact page titles, tabs, tables, badges, forms, drawers, and modals.
- Use a quiet professional SaaS interface.
- Use icon buttons for actions.
- Dashboard first screen must show business operations, not a landing page.

## 45 Day Build Plan

### Days 1-3: SaaS Foundation

- Create Laravel + React SaaS dashboard app.
- Add authentication foundation.
- Add owner, business, team, package, subscription, and API key models.
- Add clean dashboard shell.
- Add internal design system primitives.

### Days 4-7: Client Accounts And Packages

- Client registration/login.
- Package plan management.
- API key generation, rotation, disable, and expiry.
- Subscription state: trial, active, past_due, canceled.
- Usage counters per account.

### Days 8-11: Package Cloud Mode

- Add package cloud mode config.
- Add signed request client in package.
- Forward website chat to SaaS API when cloud mode is enabled.
- Add product sync endpoints.
- Add package health check.
- Add local fallback behavior if SaaS is unavailable.

### Days 12-16: Website Chat Cloud API

- SaaS receives website chat messages.
- SaaS resolves client by API key.
- SaaS runs FAQ, knowledge, product, AI fallback.
- SaaS returns clickable product links.
- Conversation and message storage in SaaS.
- Lead capture and export.

### Days 17-22: Messenger SaaS Integration

- Facebook Page connection screen.
- Store page access tokens encrypted.
- Messenger webhook verification.
- Incoming Messenger message processing.
- Auto reply through Messenger API.
- Page connection health check.
- Test message tool.

### Days 23-28: WhatsApp SaaS Integration

- WhatsApp Business Cloud API connection screen.
- Store access token, phone number ID, app secret, verify token.
- WhatsApp webhook verification.
- Incoming WhatsApp message processing.
- Auto reply through WhatsApp API.
- Connection health check.
- Test message tool.

### Days 29-33: Knowledge And Product Automation

- FAQ manager.
- Knowledge manager.
- Product catalog sync from client package.
- Product search, price, stock, slug links.
- Product response templates.
- Low-token local-first answering.

### Days 34-37: AI Gateway And Cost Control

- Central AI key settings.
- Per-package AI model rules.
- Token budget per client.
- Usage logging.
- Rate limiting.
- AI fallback control by channel.

### Days 38-41: Billing And Admin Operations

- Package purchase workflow.
- Manual payment status support first.
- Invoice records.
- Client package upgrade/downgrade.
- API key auto-disable on expired package.
- Owner/admin dashboard.

### Days 42-45: Production Hardening

- Webhook replay protection.
- Signature verification.
- Audit logs.
- Queue jobs for outbound channel replies.
- Retry failed sends.
- Error dashboard.
- Deployment checklist.
- Client onboarding checklist.

## Regular Work Order

1. Finish one feature slice.
2. Run tests.
3. Test with `boneekbd`.
4. Commit and tag package changes when needed.
5. Keep roadmap updated.

## First Milestone

Milestone 1 is a usable SaaS dashboard shell with:

- Login-ready Laravel + React app.
- Dashboard layout.
- Businesses table.
- API keys table.
- Packages table.
- Channel connection screens.
- Package cloud mode plan prepared.

