# Project Plan - AI Chat-First Tenant Admin Panel (React PWA)
Created: 2026-02-28
Source: conversation plan mode

## Instructions
- Update this file every 5 completed items (checkpoint save)
- Do not commit this plan file -- it is your AI's working reference
- Commits are done manually by the user

## Architecture

### Overview

A **chat-first tenant admin panel** built as a **React PWA**, powered by **laravel/ai SDK** on the backend and **Vercel AI SDK** on the frontend. Tenants interact with their admin panel primarily through an AI chat interface that can perform CRUD, analytics, settings, file management, user management, and notifications. A traditional UI exists as fallback/reference.

### Key Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| laravel/ai version | v0.2.5 (pinned) | Pre-stable; pin to avoid breaking changes |
| Conversation tables | Tenant DB only | Conversations are per-tenant, scoped to tenant databases |
| API key model | Platform shared keys | Usage metering per tenant, no tenant-provided keys |
| Starting LLM provider | OpenAI | Primary provider; failover to others later |
| Module placement | app/Modules/AI/ | Follows existing module pattern |
| API prefix | /api/v1/ | Matches existing route convention |
| Class creation | Manual (no artisan make) | make:agent/make:tool may not exist in v0.2.5 |
| Frontend streaming | @laravel/stream-react OR Vercel AI SDK | Decide in Phase 6; @laravel/stream-react is native |

### System Diagram

```
React PWA (Frontend)
  |-- Chat Interface (Primary) -- useStream() from @laravel/stream-react
  |-- Traditional UI (Fallback) -- Dashboard, data tables, settings
  |
  | Sanctum Auth + SSE (Vercel AI SDK Protocol)
  |
Laravel 12 (Backend)
  |-- API Routes /api/v1/ (tenant-scoped via Sanctum + IdentifyTenant)
  |   |-- POST /api/v1/chat              -> Stream new conversation
  |   |-- POST /api/v1/chat/{id}         -> Continue conversation
  |   |-- GET  /api/v1/conversations     -> List conversations
  |   |-- POST /api/v1/chat/confirm      -> Execute confirmed action
  |   |-- GET  /api/v1/dashboard         -> Dashboard data
  |   |-- ...traditional CRUD endpoints...
  |
  |-- app/Modules/AI/
  |   |-- Agents/
  |   |   |-- TenantAssistant.php
  |   |   |   implements: Agent, Conversational, HasTools, HasMiddleware
  |   |   |   uses: Promptable, RemembersConversations
  |   |   |   attributes: #[Provider(Lab::OpenAI)], #[MaxSteps(10)]
  |   |   |
  |   |   |   instructions() -> Dynamic system prompt (tenant + user + perms)
  |   |   |
  |   |-- Tools/
  |   |   |-- ListRecordsTool.php      (query any model, tenant-scoped)
  |   |   |-- CreateRecordTool.php     (create records with confirmation)
  |   |   |-- UpdateRecordTool.php     (update records with confirmation)
  |   |   |-- DeleteRecordTool.php     (delete records with confirmation)
  |   |   |-- AnalyticsTool.php        (aggregate queries, counts)
  |   |   |-- SettingsTool.php         (read/update tenant settings)
  |   |   |-- FileManagementTool.php   (upload, list, delete files)
  |   |   |-- UserManagementTool.php   (invite, roles, permissions)
  |   |   |-- NotificationTool.php     (create/manage alerts)
  |   |
  |   |-- Middleware/
  |   |   |-- TenantScopingMiddleware.php
  |   |   |-- PermissionCheckMiddleware.php
  |   |   |-- RateLimitMiddleware.php
  |   |   |-- AuditLogMiddleware.php
  |   |
  |   |-- Http/
  |   |   |-- Controllers/ChatController.php
  |   |   |-- Requests/ (FormRequest classes)
  |   |
  |   |-- Models/
  |   |   |-- AiAuditLog.php (UsesTenantConnection)
  |   |
  |   |-- Services/ChatService.php
  |   |-- Providers/AIServiceProvider.php
  |
  |-- config/ai.php (Multi-Provider)
  |   |-- default: openai
  |   |-- Platform shared API keys (not per-tenant)
  |   |-- Usage metering per tenant via RateLimitMiddleware
  |
  |-- database/migrations/tenant/
      |-- agent_conversations (from laravel/ai, moved here)
      |-- agent_conversation_messages (from laravel/ai, moved here)
      |-- ai_audit_logs
```

### Data Flow

```
User types: "Show me this month's revenue"
  |
  v
React Frontend
  useStream() hook (@laravel/stream-react)
  POST /api/v1/chat/{conversationId}
  Cookie: sanctum session (SPA mode)
  Body: { message: "Show me this month's revenue" }
  |
  v
Laravel Route + ChatController
  1. Auth::user() -> tenant-scoped user
  2. Resolve TenantAssistant agent
  3. Inject tenant context into agent
  4. return (new TenantAssistant($tenant, $user))
       ->continue($conversationId, as: $user)
       ->stream($request->message)
       ->usingVercelDataProtocol();
  |
  v
laravel/ai Agent Pipeline
  1. MIDDLEWARE CHAIN
     TenantScopingMiddleware -> sets tenant on prompt
     PermissionCheckMiddleware -> filters tools by user perms
     RateLimitMiddleware -> checks token budget
     AuditLogMiddleware -> logs prompt + response
  2. CONVERSATION LOADING (RemembersConversations)
     -> Loads last N messages from agent_conversation_messages
  3. PROMPT ASSEMBLY
     System: instructions() with tenant context
     Messages: previous conversation history
     User: "Show me this month's revenue"
     Tools: [AnalyticsTool, ListRecordsTool, ...]
  4. SEND TO LLM PROVIDER
     config/ai.php -> resolves OpenAI
  |
  v
OpenAI
  Decides to call: AnalyticsTool
  Arguments: { model: "Article", metric: "count",
               period: "this_month", aggregate: "count" }
  |
  v
Tool Execution (server-side, tenant-scoped)
  AnalyticsTool::handle($request)
  1. Validate against schema
  2. Build Eloquent query SCOPED to tenant (UsesTenantConnection)
  3. Return: "You have 45 articles this month, 12 published"
  |
  v
LLM Formats Final Response
  "You have 45 articles this month, with 12 published.
   That's up 20% compared to last month."
  + optional structured metadata for rich UI blocks
  |
  v
Streaming Back to Client (SSE via Vercel AI SDK Protocol)
  -> Tokens stream as they are generated
  -> .then() callback: save to agent_conversation_messages (auto)
  |
  v
React Frontend Renders
  useStream() receives streamed tokens
  - Text renders incrementally
  - Rich blocks render on metadata arrival
```

### Existing Tenant-Scoped Models (AI Tools will interact with these)

| Model | Module | Key Fields | Relationships |
|-------|--------|------------|---------------|
| Article | Article | title, slug, content, status (Draft/Published/Archived), views_count, published_at | author (User), series, comments, revisions |
| ArticleSeries | Article | title, slug, description | author (User), articles |
| ArticleComment | Article | content, is_approved | article, author (User) |
| ArticleRevision | Article | title, content, change_summary | article, author (User) |
| User | Auth | name, email, is_active, roles/permissions (Spatie) | HasApiTokens, HasRoles |

### Existing Enums

| Enum | Values |
|------|--------|
| ArticleStatus | Draft, Published, Archived |
| Role | Admin, User |
| Permission | UserViewAny, UserView, UserCreate, UserUpdate, UserDelete |

### Rich Response Blocks (Frontend Components)

| Block Type     | Use Case                                    |
|----------------|---------------------------------------------|
| TextBlock      | Plain conversational text                   |
| TableBlock     | Data tables with sorting/pagination         |
| ChartBlock     | Charts (bar, line, pie)                     |
| FormBlock      | Pre-filled forms for confirmation           |
| CardBlock      | Record summaries (article, user, etc.)      |
| ConfirmBlock   | "Are you sure?" before destructive actions  |
| FileBlock      | File previews, upload status                |
| AlertBlock     | Success/error/warning notifications         |

### Mutation Confirmation Flow

```
User: "Delete all archived articles"
AI:   "I found 23 archived articles. Here they are:"
      [TableBlock: list of 23 articles]
      [ConfirmBlock: "Delete these 23 articles?" -> Yes / No]
User: clicks "Yes"
      POST /api/v1/chat/confirm { action_id: "...", confirmed: true }
AI:   "Done. 23 archived articles have been deleted."
```

### Security Boundaries

1. Tenant scoping -- every tool call scoped to authenticated tenant (UsesTenantConnection)
2. Permission checks -- tools check user perms via Laravel Policies + Spatie HasRoles
3. Input sanitization -- user messages sanitized before LLM, LLM output sanitized before render
4. Rate limiting -- token usage per tenant/user with configurable budgets (platform keys)
5. Audit logging -- every tool execution logged (who, what, when)

### laravel/ai v0.2.5 SDK Conventions

Agent class pattern:
```
implements Agent, Conversational, HasTools, HasMiddleware
uses Promptable, RemembersConversations
attributes: #[Provider(Lab::OpenAI)], #[MaxSteps(10)], #[Temperature(0.7)]
methods: instructions(), tools(), middleware()
conversations: ->forUser($user)->prompt() | ->continue($id, as: $user)->prompt()
streaming: ->stream($msg)->usingVercelDataProtocol()
testing: Agent::fake(), assertPrompted()
```

Tool class pattern:
```
implements Tool (Laravel\Ai\Contracts\Tool)
methods: description(), handle(Request $request), schema(JsonSchema $schema)
DI: constructor injection + handle() method injection supported
```

Middleware pattern:
```
handle(AgentPrompt $prompt, Closure $next)
post-response: return $next($prompt)->then(fn (AgentResponse $response) => ...)
```

### Tech Stack (Frontend)

| Layer           | Technology                                  |
|-----------------|---------------------------------------------|
| Framework       | React 19 + TypeScript                       |
| Build           | Vite                                        |
| Routing         | React Router v7                             |
| AI Chat         | @laravel/stream-react (useStream) or Vercel AI SDK |
| Server State    | TanStack Query v5                           |
| UI State        | Zustand                                     |
| UI Components   | Shadcn/ui + Tailwind CSS                    |
| Forms           | React Hook Form + Zod                       |
| Charts          | Recharts                                    |
| HTTP            | Axios (Sanctum auth)                        |
| PWA             | vite-plugin-pwa                             |
| Auth            | Laravel Sanctum (SPA cookie mode)           |

### Tech Stack (Backend)

| Layer           | Technology                              |
|-----------------|----------------------------------------|
| Framework       | Laravel 12                             |
| AI SDK          | laravel/ai v0.2.5 (pinned)            |
| Multi-tenancy   | Spatie Laravel Multitenancy v4         |
| Auth            | Sanctum v4                             |
| Admin (landlord)| Filament v5 (existing, unchanged)      |
| Database        | MySQL (separate DB per tenant)         |
| Streaming       | SSE via Vercel AI SDK Protocol         |
| Queue           | Laravel Queues (for heavy AI tasks)    |

### Frontend Directory Structure

Lives inside Laravel's resources directory -- single repo, single Vite config,
no CORS issues, Sanctum cookie auth works out of the box.

```
resources/
  js/
    tenant-app/
      app/
        routes/              -- React Router v7
        providers/           -- Auth, Theme, QueryClient
        layout/              -- Shell with chat sidebar
      features/
        chat/
          ChatView.tsx             -- Main chat interface
          MessageThread.tsx        -- Message list
          MessageInput.tsx         -- Input bar
          blocks/                  -- Rich response blocks
            TextBlock.tsx
            TableBlock.tsx
            ChartBlock.tsx
            FormBlock.tsx
            ConfirmBlock.tsx
            FileBlock.tsx
          hooks/
            useChat.ts             -- Chat state + SSE streaming
            useConversations.ts
        dashboard/                 -- Traditional dashboard widgets
        articles/                  -- Traditional CRUD pages (fallback)
        users/                     -- Traditional user management
        settings/                  -- Traditional settings pages
      shared/
        api/                 -- Axios + Sanctum config
        components/          -- Shadcn/ui components
        stores/              -- Zustand (UI state)
      service-worker/        -- PWA + offline support
      main.tsx               -- Entry point
```

### Module Structure (app/Modules/AI/)

Follows existing module pattern from Auth, Article, Tenancy, Subscription modules.

```
app/Modules/AI/
  Agents/
    TenantAssistant.php
  Tools/
    ListRecordsTool.php
    CreateRecordTool.php
    UpdateRecordTool.php
    DeleteRecordTool.php
    AnalyticsTool.php
    SettingsTool.php
    FileManagementTool.php
    UserManagementTool.php
    NotificationTool.php
  Middleware/
    TenantScopingMiddleware.php
    PermissionCheckMiddleware.php
    RateLimitMiddleware.php
    AuditLogMiddleware.php
  Http/
    Controllers/
      ChatController.php
    Requests/
      StreamChatRequest.php
      ContinueChatRequest.php
      ConfirmActionRequest.php
  Models/
    AiAuditLog.php
  DTOs/
    (as needed)
  Services/
    ChatService.php
  Providers/
    AIServiceProvider.php
```

## Implementation Plan

### Phase 1: Backend Foundation (laravel/ai Setup)

- [ ] Install laravel/ai SDK: composer require laravel/ai:0.2.5
- [ ] Publish config and migrations: php artisan vendor:publish
- [ ] Move published migrations to database/migrations/tenant/ (tenant DB only)
- [ ] Run tenant migrations to create agent_conversations and agent_conversation_messages
- [ ] Configure config/ai.php with OpenAI as default provider (platform shared key)
- [ ] Add OPENAI_API_KEY to .env
- [ ] Create app/Modules/AI/ directory structure following existing module pattern
- [ ] Create AIServiceProvider and register in bootstrap/providers.php
- [ ] Create TenantAssistant agent class manually (Agent, Conversational, HasTools, HasMiddleware)
- [ ] Implement TenantAssistant instructions() with dynamic tenant/user context
- [ ] Implement TenantAssistant tools() returning empty array (placeholder)
- [ ] Implement TenantAssistant middleware() returning empty array (placeholder)

### Phase 2: AI Tools (Business Logic)

- [ ] Create ListRecordsTool (query any tenant model with filters, pagination)
- [ ] Create CreateRecordTool (create records, return confirmation metadata)
- [ ] Create UpdateRecordTool (update records, return confirmation metadata)
- [ ] Create DeleteRecordTool (delete records, return confirmation metadata)
- [ ] Create AnalyticsTool (aggregate queries: count, sum, avg, group by, periods)
- [ ] Create SettingsTool (read/update tenant settings)
- [ ] Create FileManagementTool (upload, list, delete files via Storage)
- [ ] Create UserManagementTool (invite, list, update roles/permissions via Spatie)
- [ ] Create NotificationTool (create/manage user notifications)
- [ ] Wire all tools into TenantAssistant tools() method
- [ ] Implement tenant-scoping in each tool (all queries via UsesTenantConnection models)

### Phase 3: Agent Middleware (Security)

- [ ] Create TenantScopingMiddleware (enforce tenant context on every prompt)
- [ ] Create PermissionCheckMiddleware (filter available tools by user permissions)
- [ ] Create RateLimitMiddleware (token budget per tenant, platform key metering)
- [ ] Create AuditLogMiddleware (log all tool executions with user/tenant/action)
- [ ] Wire middleware into TenantAssistant middleware() method
- [ ] Create ai_audit_logs tenant migration
- [ ] Create AiAuditLog model with UsesTenantConnection

### Phase 4: API Routes and Controller

- [ ] Create ChatController with stream, continue, listConversations, confirm methods
- [ ] Add chat API routes under /api/v1/ prefix (POST chat, POST chat/{id}, GET conversations, POST chat/confirm)
- [ ] Apply auth:sanctum + IdentifyTenant middleware to chat routes
- [ ] Implement Vercel AI SDK Protocol streaming in controller
- [ ] Create StreamChatRequest FormRequest class
- [ ] Create ContinueChatRequest FormRequest class
- [ ] Create ConfirmActionRequest FormRequest class
- [ ] Create ChatService to orchestrate agent interactions

### Phase 5: Testing (Backend)

- [ ] Write feature tests for ChatController (stream, continue, confirm) using Agent::fake()
- [ ] Write unit tests for each AI tool (tenant scoping, schema validation)
- [ ] Write tests for agent middleware (tenant isolation, permission filtering, rate limiting)
- [ ] Write tests for TenantAssistant agent (instructions, tool wiring)
- [ ] Ensure all existing tests still pass

### Phase 6: React PWA Scaffold

- [ ] Initialize React app inside resources/js/tenant-app/ (Vite + React 19 + TypeScript)
- [ ] Install dependencies: @laravel/stream-react, React Router v7, TanStack Query, Zustand, Shadcn/ui, Tailwind CSS
- [ ] Set up project directory structure (features/, shared/, app/)
- [ ] Configure Vite for tenant-app entry point
- [ ] Set up Axios with Sanctum SPA cookie auth
- [ ] Set up React Router with route structure
- [ ] Set up TanStack Query provider
- [ ] Create app shell layout (chat sidebar + content area)

### Phase 7: Chat Interface (Frontend)

- [ ] Implement ChatView component using useStream() from @laravel/stream-react
- [ ] Implement MessageThread component (message list with auto-scroll)
- [ ] Implement MessageInput component (text input + file attach button)
- [ ] Create TextBlock component
- [ ] Create TableBlock component (with sorting/pagination)
- [ ] Create ChartBlock component (using Recharts)
- [ ] Create FormBlock component (pre-filled form for confirmation)
- [ ] Create ConfirmBlock component (yes/no confirmation dialog)
- [ ] Create FileBlock component (file preview + upload status)
- [ ] Create CardBlock component (record summary cards)
- [ ] Implement conversation list sidebar
- [ ] Implement new conversation creation
- [ ] Connect chat to POST /api/v1/chat endpoints with Sanctum auth

### Phase 8: Traditional UI (Fallback)

- [ ] Create Dashboard page with widget components
- [ ] Create data table pages for article CRUD browsing
- [ ] Create detail/edit pages for articles
- [ ] Create Settings page
- [ ] Create User management page
- [ ] Integrate traditional pages with chat (link from chat responses to detail pages)

### Phase 9: PWA Features

- [ ] Configure service worker for offline caching
- [ ] Add PWA manifest (app name, icons, theme)
- [ ] Implement offline chat history viewing
- [ ] Implement push notification support (for AI alerts)
- [ ] Test PWA install flow on mobile/desktop

### Phase 10: Polish and Production Readiness

- [ ] Add loading states and error handling throughout frontend
- [ ] Implement dark/light theme toggle
- [ ] Add responsive design for mobile/tablet/desktop
- [ ] Write frontend tests (React Testing Library)
- [ ] Performance audit (bundle size, lazy loading, code splitting)
- [ ] Security review (input sanitization, auth flows)
- [ ] Documentation (API docs, deployment guide)

## Progress Log

2026-02-28 - Plan created from conversation discussion. Architecture defined with laravel/ai SDK integration.
2026-02-28 - Plan updated with final decisions: laravel/ai v0.2.5 pinned, OpenAI default, app/Modules/AI/ structure, /api/v1/ prefix, tenant-only migrations, platform shared keys, manual class creation. Added SDK conventions reference, existing model inventory, and @laravel/stream-react as frontend option.
