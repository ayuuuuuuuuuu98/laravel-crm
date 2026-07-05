# WhatsApp CRM Implementation Plan for Krayin CRM

## 1. Executive Summary

Krayin CRM is already structured as a modular Laravel application with package-based extensions under packages/Webkul. The cleanest way to build a WhatsApp CRM is to add a new package named WhatsApp rather than patching the core CRM modules directly.

This plan assumes a phased rollout:

1. Foundation package and schema
2. WhatsApp account configuration and webhook ingestion
3. Conversation UI and CRM linkage
4. Outbound messaging, templates, and automation
5. Advanced analytics and scaling

The implementation should reuse Krayin conventions already visible in the Lead, Contact, Email, and Admin packages.

---

## 2. Recommended Architecture

### High-level approach

Use a new package at packages/Webkul/WhatsApp with its own:

- service provider
- migrations
- models
- repositories
- services
- controllers
- routes
- views
- Vue assets

### Core architecture principles

- Keep all WhatsApp logic inside the new package.
- Reuse existing CRM entities such as Person, Lead, User, and Activity instead of creating parallel core data models.
- Introduce a provider abstraction so the module can support multiple WhatsApp backends later (Meta WhatsApp Business Platform, Twilio, or other APIs).
- Use queue-based processing for inbound webhook handling and outbound message sending.
- Keep webhook ingestion public and stateless; route all business logic through services.

### Suggested runtime flow

1. Admin configures a WhatsApp account.
2. Webhook endpoint receives inbound messages and status events.
3. A service normalizes the payload and creates or updates conversation records.
4. The system resolves the contact to a CRM entity (Person/Lead) using phone number matching.
5. The admin UI displays the conversation in a CRM context.
6. Outbound sending uses a queued service and provider adapter.

---

## 3. Where the Module Should Live

The WhatsApp module should live as a first-class package under:

- packages/Webkul/WhatsApp/

This matches the existing package layout used by Lead, Contact, Email, Product, and Automation.

### Why this is the right home

- It keeps the implementation upgrade-safe.
- It matches Krayin’s existing extension pattern.
- It avoids touching core modules for feature growth.
- It allows the feature to be enabled or disabled cleanly.

---

## 4. Proposed Folder Structure

```text
packages/Webkul/WhatsApp/
├── composer.json
├── src/
│   ├── Config/
│   │   ├── acl.php
│   │   ├── menu.php
│   │   ├── core_config.php
│   │   └── providers.php
│   ├── Contracts/
│   │   ├── WhatsAppAccount.php
│   │   ├── WhatsAppConversation.php
│   │   ├── WhatsAppMessage.php
│   │   └── WhatsAppProvider.php
│   ├── Database/
│   │   └── Migrations/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── WebhookController.php
│   │   │   └── ApiController.php
│   │   ├── Requests/
│   │   └── Middleware/
│   ├── Models/
│   │   ├── WhatsAppAccount.php
│   │   ├── WhatsAppConversation.php
│   │   ├── WhatsAppMessage.php
│   │   ├── WhatsAppTemplate.php
│   │   └── WhatsAppWebhookEvent.php
│   ├── Providers/
│   │   └── WhatsAppServiceProvider.php
│   ├── Repositories/
│   │   ├── WhatsAppAccountRepository.php
│   │   ├── WhatsAppConversationRepository.php
│   │   ├── WhatsAppMessageRepository.php
│   │   └── WhatsAppTemplateRepository.php
│   ├── Resources/
│   │   ├── assets/
│   │   │   └── js/
│   │   ├── lang/
│   │   └── views/
│   │       ├── admin/
│   │       │   ├── conversations/
│   │       │   ├── templates/
│   │       │   └── settings/
│   │       └── components/
│   ├── Routes/
│   │   ├── admin.php
│   │   ├── api.php
│   │   └── webhook.php
│   ├── Services/
│   │   ├── Providers/
│   │   │   ├── MetaProviderService.php
│   │   │   ├── TwilioProviderService.php
│   │   │   └── ProviderFactory.php
│   │   ├── ContactResolverService.php
│   │   ├── ConversationService.php
│   │   ├── MessageDispatchService.php
│   │   ├── WebhookProcessorService.php
│   │   └── TemplateService.php
│   └── Tests/
└── package.json (optional if a dedicated frontend build is introduced)
```

---

## 5. Database Design

### Core tables

#### whatsapp_accounts

Purpose: store each configured WhatsApp business account.

Suggested columns:

- id
- name
- provider (meta/twilio/custom)
- phone_number_id
- business_account_id
- access_token (encrypted or stored in secrets layer)
- webhook_secret
- status (active/inactive/error)
- settings (json)
- user_id (owner/admin)
- created_at / updated_at

#### whatsapp_conversations

Purpose: hold the conversation thread per contact or CRM entity.

Suggested columns:

- id
- account_id
- contact_identifier (normalized phone number)
- person_id (nullable)
- lead_id (nullable)
- last_message_at
- unread_count
- status (open/closed/pending)
- metadata (json)
- created_at / updated_at

#### whatsapp_messages

Purpose: store each message event.

Suggested columns:

- id
- conversation_id
- account_id
- direction (inbound/outbound/system)
- message_type (text/image/template/button)
- content
- external_message_id
- status (queued/sent/delivered/read/failed)
- sender_name
- recipient_phone
- metadata (json)
- created_at / updated_at

#### whatsapp_templates

Purpose: hold reusable WhatsApp message templates.

Suggested columns:

- id
- account_id
- name
- language
- body
- category
- status (draft/approved/disabled)
- metadata (json)
- created_at / updated_at

#### whatsapp_webhook_events

Purpose: audit webhook events and debugging.

Suggested columns:

- id
- account_id
- event_type
- payload (json)
- processed_at
- success
- error_message
- created_at

#### whatsapp_contact_mappings (recommended)

Purpose: normalize and link WhatsApp numbers to CRM records.

Suggested columns:

- id
- phone_number
- normalized_phone_number
- person_id (nullable)
- lead_id (nullable)
- organization_id (nullable)
- source
- confidence
- created_at / updated_at

### Notes on design

- Use migrations only; do not edit the database directly.
- Normalize phone numbers before storing them.
- Keep the schema decoupled from a specific provider by storing provider-specific details in JSON metadata.

---

## 6. Models

### Core models

- WhatsAppAccount
  - belongsTo User
  - hasMany WhatsAppConversation
  - hasMany WhatsAppMessage
  - hasMany WhatsAppTemplate

- WhatsAppConversation
  - belongsTo WhatsAppAccount
  - belongsTo Person (nullable)
  - belongsTo Lead (nullable)
  - hasMany WhatsAppMessage

- WhatsAppMessage
  - belongsTo WhatsAppConversation
  - belongsTo WhatsAppAccount

- WhatsAppTemplate
  - belongsTo WhatsAppAccount

- WhatsAppWebhookEvent
  - belongsTo WhatsAppAccount

### Integration strategy

The module should not duplicate existing CRM identities. Instead, it should attach WhatsApp conversations to existing leads and persons through nullable foreign keys and optional mapping records.

---

## 7. Controllers

### Admin controllers

- WhatsAppConversationController
  - index: show the inbox/list view
  - show: show a single conversation detail view
  - storeReply: send an outbound message
  - markAsRead: update read state
  - archive: archive conversations

- WhatsAppTemplateController
  - index, create, store, edit, update, delete

- WhatsAppSettingController
  - index, store account configuration

### Webhook controller

- WebhookController
  - receives inbound events from WhatsApp provider
  - validates signatures or tokens
  - dispatches processing to services
  - writes audit records

### API controller

- ApiController
  - exposes JSON APIs for the Vue frontend
  - supports conversation list, message fetch, send, and status updates

---

## 8. Services

### Provider abstraction

A provider interface should be introduced to keep the module backend-agnostic.

Suggested services:

- WhatsAppProviderInterface
- MetaProviderService
- TwilioProviderService
- ProviderFactory

### Business services

- ContactResolverService
  - resolves a phone number to a Person/Lead using mappings and fuzzy matching

- ConversationService
  - creates or updates conversation threads and unread state

- MessageDispatchService
  - queues outbound messages and handles retries

- WebhookProcessorService
  - normalizes incoming payloads into CRM-friendly events

- TemplateService
  - sends template-based messages and validates content placeholders

### Queue strategy

Use queued jobs for:

- webhook processing
- outbound message dispatch
- status sync polling
- retrying failed messages

---

## 9. Routes

### Admin web routes

Create dedicated admin routes for:

- /whatsapp/conversations
- /whatsapp/conversations/{id}
- /whatsapp/templates
- /whatsapp/settings

### API routes

Create API endpoints for:

- fetching conversations
- fetching messages in a thread
- sending a response
- updating conversation state
- loading account templates

### Webhook routes

Create a public webhook route such as:

- /whatsapp/webhook/{account_id}

This route should be protected by signature validation and not depend on the admin session layer.

---

## 10. Vue Pages and Frontend Structure

Krayin already boots Vue in the admin UI, so the WhatsApp workspace should use the same pattern rather than introducing a completely separate frontend.

### Recommended Vue pages

1. Conversations inbox page
   - left sidebar for conversation list
   - center pane for active thread
   - right pane for CRM context (lead/person details)

2. Conversation detail page
   - message history
   - compose box
   - quick actions for attaching to lead/contact
   - status and metadata display

3. Templates page
   - list templates
   - create/edit modal or drawer
   - preview and approval state

4. Settings page
   - WhatsApp account setup
   - provider credentials and webhook URL
   - delivery and notification preferences

### Frontend implementation strategy

Use a Blade wrapper page that mounts a Vue app for the WhatsApp workspace. Keep the Vue layer focused on state, conversation rendering, and API interaction. Reuse the existing admin layout and theme.

---

## 11. Best Extension Points in the Existing Codebase

These are the best places to plug the new module in without rewriting core behavior.

### 1. Admin package integration

- Use package-level service provider registration for routes, views, config, and migrations.
- Use the admin layout and auth middleware already provided by the Admin package.

### 2. Menu integration

- Add menu items through config merging rather than editing core menu definitions directly.
- Suggested menu grouping: WhatsApp > Conversations, Templates, Settings.

### 3. Lead and contact linkage

- Hook into the existing Lead and Person domain objects through optional linking rather than changing their existing behavior.
- Use a mapping table to connect phone numbers to existing CRM entities.

### 4. Activity system

- Optionally log WhatsApp interactions as activities to stay consistent with the existing activity-driven CRM experience.
- This is an extension point, not a reason to change core activity models.

### 5. Configuration layer

- Add module-specific config entries for provider settings, default template language, webhook secrets, and notification preferences.

---

## 12. Files That Should Never Be Modified Directly

To keep the implementation upgrade-safe, avoid editing these core areas directly:

- [packages/Webkul/Lead/src/Models/Lead.php](../packages/Webkul/Lead/src/Models/Lead.php)
- [packages/Webkul/Contact/src/Models/Person.php](../packages/Webkul/Contact/src/Models/Person.php)
- [packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php](../packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php)
- [packages/Webkul/Admin/src/Providers/AdminServiceProvider.php](../packages/Webkul/Admin/src/Providers/AdminServiceProvider.php)
- [packages/Webkul/Admin/src/Config/menu.php](../packages/Webkul/Admin/src/Config/menu.php)
- [bootstrap/app.php](../bootstrap/app.php)
- [config/app.php](../config/app.php)
- [public/index.php](../public/index.php)

Instead, the WhatsApp package should register new routes, views, config, and migrations through its own service provider and package-level hooks.

---

## 13. Development Phases

### Phase 1 — Foundation

Scope:

- create the WhatsApp package scaffold
- register service provider and package routes
- create migrations
- create core models and repositories
- add admin menu and route entry points

Deliverable:

- a working empty module shell with database tables and admin navigation

### Phase 2 — Provider and Webhook Integration

Scope:

- add provider abstraction
- implement webhook receiver
- store inbound messages and message status updates
- create contact resolution logic

Deliverable:

- inbound WhatsApp messages appear as CRM conversations

### Phase 3 — Admin UI and CRM Linkage

Scope:

- build conversation inbox and detail view
- connect phone number to Person/Lead
- add message sending and thread management
- show CRM context in the sidebar

Deliverable:

- agents can view and reply to WhatsApp conversations from the admin panel

### Phase 4 — Templates and Automation

Scope:

- add template management
- outbound automation rules
- quick replies and status updates
- queue-based retries and failure handling

Deliverable:

- automated and templated outbound messaging workflows

### Phase 5 — Hardening and Scale

Scope:

- performance tuning
- webhook security hardening
- analytics and reporting
- optional multi-account support

Deliverable:

- production-ready operational support

---

## 14. Risks and Mitigations

### Risk 1: Provider API changes

Mitigation:

- keep provider logic behind an interface
- isolate provider-specific code in dedicated service classes

### Risk 2: Webhook security

Mitigation:

- validate signatures and use webhook secrets
- log all events for debugging and auditing

### Risk 3: Contact resolution inaccuracies

Mitigation:

- use normalized phone number matching
- support explicit user confirmation for ambiguous matches

### Risk 4: UI complexity

Mitigation:

- start with a simple inbox and thread detail view
- expand to richer CRM context after the core flow is stable

### Risk 5: Database growth

Mitigation:

- archive old conversations and limit webhook event retention
- use indexes on conversation_id, account_id, and phone number columns

---

## 15. Estimated Files to Create

A realistic first implementation is likely to involve around 35 to 45 new files, including:

- 1 package service provider
- 1 package composer manifest
- 5 to 7 migrations
- 5 models
- 4 to 5 repositories
- 4 to 6 controllers
- 6 to 8 service classes
- 3 route files
- 4 to 6 Blade view files
- 6 to 8 Vue components/pages
- 2 to 3 config files
- 4 to 6 tests

This is a substantial but manageable module for this codebase and fits the existing package architecture well.

---

## 16. Recommended Implementation Strategy

The safest and fastest path is:

1. Create the WhatsApp package skeleton.
2. Create the database foundation and basic admin routes.
3. Implement webhook ingestion and message persistence.
4. Build the conversation inbox UI.
5. Connect messages to leads and persons.
6. Add outbound sending and templates.

This sequence minimizes risk and delivers visible value early.

---

## 17. Final Recommendation

Build WhatsApp as a new package under packages/Webkul/WhatsApp, keep all business logic inside that package, and integrate with the existing CRM through optional mapping to Person and Lead records. This approach is the most consistent with the current Krayin architecture and the least risky for long-term maintenance.
