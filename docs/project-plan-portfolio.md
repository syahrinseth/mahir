# Project Plan - Portfolio Module (Tenant-Scoped)
Created: 2026-02-28
Source: conversation plan mode

## Instructions
- Update this file every 5 completed items (checkpoint save)
- Do not commit this plan file -- it is your AI's working reference
- Commits are done manually by the user

## Architecture

### Overview

A **tenant-scoped Portfolio module** for showcasing projects/work. Follows the existing
module pattern (Article module as reference). Tenants manage portfolio items with
categories, media galleries, client info, technologies, and external links. API-only
(no Filament admin resources). Status workflow: Draft -> Published -> Archived.

### Module Structure

```
app/Modules/Portfolio/
  Actions/
    CreatePortfolioAction.php
    UpdatePortfolioAction.php
    DeletePortfolioAction.php
    PublishPortfolioAction.php
  DTOs/
    CreatePortfolioDTO.php
    UpdatePortfolioDTO.php
    CreatePortfolioCategoryDTO.php
    UpdatePortfolioCategoryDTO.php
  Enums/
    PortfolioStatus.php
  Events/
    (empty -- future use)
  Http/
    Controllers/
      PortfolioController.php
      PortfolioCategoryController.php
      PortfolioMediaController.php
    Requests/
      CreatePortfolioRequest.php
      UpdatePortfolioRequest.php
      CreatePortfolioCategoryRequest.php
      UpdatePortfolioCategoryRequest.php
      StorePortfolioMediaRequest.php
  Models/
    Portfolio.php
    PortfolioCategory.php
    PortfolioMedia.php
  Policies/
    PortfolioPolicy.php
  Providers/
    PortfolioServiceProvider.php
  Repositories/
    PortfolioRepository.php
    PortfolioCategoryRepository.php
  Services/
    PortfolioService.php
```

### Data Model

```
portfolios
  id                 bigint PK
  user_id            bigint FK -> users (author)
  category_id        bigint FK -> portfolio_categories (nullable)
  title              varchar(255)
  slug               varchar(255) unique
  description        text (rich text / case study)
  client_name        varchar(255) nullable
  project_url        varchar(2048) nullable
  featured_image     varchar(2048) nullable
  technologies       json nullable (array of strings: ["Laravel", "React", ...])
  status             varchar(20) default 'draft' (draft/published/archived)
  sort_order         integer default 0
  started_at         date nullable
  ended_at           date nullable
  published_at       datetime nullable
  created_at         datetime
  updated_at         datetime

  indexes: status, slug (unique), category_id, user_id, sort_order, published_at

portfolio_categories
  id                 bigint PK
  user_id            bigint FK -> users (creator)
  name               varchar(255)
  slug               varchar(255) unique
  description        text nullable
  sort_order         integer default 0
  created_at         datetime
  updated_at         datetime

  indexes: slug (unique), sort_order

portfolio_media
  id                 bigint PK
  portfolio_id       bigint FK -> portfolios (cascade delete)
  file_path          varchar(2048)
  file_name          varchar(255)
  mime_type          varchar(100)
  file_size          integer (bytes)
  sort_order         integer default 0
  caption            varchar(500) nullable
  created_at         datetime
  updated_at         datetime

  indexes: portfolio_id, sort_order
```

### Relationships

```
Portfolio
  |-- belongsTo  User (author)
  |-- belongsTo  PortfolioCategory (nullable)
  |-- hasMany    PortfolioMedia

PortfolioCategory
  |-- belongsTo  User (creator)
  |-- hasMany    Portfolio

PortfolioMedia
  |-- belongsTo  Portfolio
```

### API Endpoints (all under /api/v1/, auth:sanctum + IdentifyTenant)

```
Portfolios:
  GET    /api/v1/portfolios                -> index (list, filter, paginate)
  POST   /api/v1/portfolios                -> store (create draft)
  GET    /api/v1/portfolios/{portfolio}     -> show
  PUT    /api/v1/portfolios/{portfolio}     -> update
  DELETE /api/v1/portfolios/{portfolio}     -> destroy
  POST   /api/v1/portfolios/{portfolio}/publish  -> publish
  POST   /api/v1/portfolios/{portfolio}/archive  -> archive

Categories:
  GET    /api/v1/portfolio-categories               -> index
  POST   /api/v1/portfolio-categories               -> store
  GET    /api/v1/portfolio-categories/{category}     -> show
  PUT    /api/v1/portfolio-categories/{category}     -> update
  DELETE /api/v1/portfolio-categories/{category}     -> destroy

Media (nested under portfolio):
  GET    /api/v1/portfolios/{portfolio}/media            -> index
  POST   /api/v1/portfolios/{portfolio}/media            -> store (upload)
  DELETE /api/v1/portfolios/{portfolio}/media/{media}    -> destroy
  PUT    /api/v1/portfolios/{portfolio}/media/reorder     -> reorder
```

### Enum: PortfolioStatus

```
Draft     -> 'draft'     -> label: 'Draft'     -> color: 'gray'
Published -> 'published'  -> label: 'Published' -> color: 'success'
Archived  -> 'archived'   -> label: 'Archived'  -> color: 'warning'
```

### Policy Rules

```
PortfolioPolicy:
  viewAny  -> true (any authenticated tenant user)
  view     -> true
  create   -> true
  update   -> owner only (user_id === auth user)
  delete   -> owner only
  publish  -> owner only
  archive  -> owner only
```

### Key Conventions (from Article module reference)

- Models use `UsesTenantConnection` trait + `#[UseFactory]` attribute
- Casts defined via `casts()` method (not $casts property)
- Controllers are thin: FormRequest -> DTO -> Service
- Repositories use `Model::query()->` (never DB:: facade)
- Repositories eager-load relationships in read methods
- DTOs use constructor property promotion with `fromArray()` + `toArray()`
- Update DTOs filter nulls in `toArray()`
- Actions implement `ActionContract`, inject Service, have `execute()` method
- FormRequests use array-based rules with PHPDoc comments
- Service implements `ServiceContract`, injects repositories
- Migration naming: `{date}_{seq}_create_tenant_{table}_table.php`
- Factories in `database/factories/` namespace `Database\Factories`
- Seeders in `database/seeders/tenant/` namespace `Database\Seeders\Tenant`
- Provider registered at end of `bootstrap/providers.php`
- Routes follow existing prefix/naming pattern in `routes/api.php`

## Implementation Plan

### Phase 1: Database Layer (Migrations + Models)

- [x] Create migration: create_tenant_portfolio_categories_table
- [x] Create migration: create_tenant_portfolios_table
- [x] Create migration: create_tenant_portfolio_media_table
- [x] Create PortfolioStatus enum with Draft, Published, Archived cases
- [x] Create PortfolioCategory model with UsesTenantConnection, relationships, fillable
- [x] Create Portfolio model with UsesTenantConnection, relationships, casts, helper methods
- [x] Create PortfolioMedia model with UsesTenantConnection, relationships, fillable
- [x] Create PortfolioFactory with state methods (published, draft, archived, withClient, withTechnologies)
- [x] Create PortfolioCategoryFactory
- [x] Create PortfolioMediaFactory
- [x] Run tenant migrations to verify tables are created correctly

### Phase 2: Business Logic (DTOs, Repositories, Services, Actions)

- [x] Create CreatePortfolioDTO with fromArray() and toArray()
- [x] Create UpdatePortfolioDTO with nullable fields and null-filtered toArray()
- [x] Create CreatePortfolioCategoryDTO
- [x] Create UpdatePortfolioCategoryDTO
- [x] Create PortfolioRepository implementing RepositoryContract (with eager loading)
- [x] Create PortfolioCategoryRepository implementing RepositoryContract
- [x] Create PortfolioService implementing ServiceContract (portfolios + categories + media)
- [x] Create CreatePortfolioAction
- [x] Create UpdatePortfolioAction
- [x] Create DeletePortfolioAction
- [x] Create PublishPortfolioAction
- [x] Create PortfolioPolicy with owner-based authorization

### Phase 3: API Layer (Controllers, Requests, Routes)

- [x] Create CreatePortfolioRequest with validation rules and messages
- [x] Create UpdatePortfolioRequest with 'sometimes' rules
- [x] Create CreatePortfolioCategoryRequest
- [x] Create UpdatePortfolioCategoryRequest
- [x] Create StorePortfolioMediaRequest (file validation)
- [x] Create PortfolioController (index, store, show, update, destroy, publish, archive)
- [x] Create PortfolioCategoryController (index, store, show, update, destroy)
- [x] Create PortfolioMediaController (index, store, destroy, reorder)
- [x] Add portfolio routes to routes/api.php under auth:sanctum group
- [x] Create PortfolioServiceProvider (register repos, service; boot policy)
- [x] Register PortfolioServiceProvider in bootstrap/providers.php

### Phase 4: Seeders

- [x] Create PortfolioCategorySeeder (seed default categories)
- [x] Create PortfolioSeeder (seed sample portfolios with media)
- [x] Register seeders in tenant DatabaseSeeder if applicable (N/A -- seeders run independently)

### Phase 5: Testing

- [x] Write feature tests for PortfolioController (CRUD, publish, archive)
- [x] Write feature tests for PortfolioCategoryController (CRUD)
- [x] Write feature tests for PortfolioMediaController (upload, delete, reorder)
- [x] Write unit tests for PortfolioPolicy (owner-based authorization)
- [x] Write unit tests for PortfolioService (business logic)
- [x] Write unit tests for DTOs (fromArray, toArray, null filtering)
- [x] Ensure all existing tests still pass
- [x] Run Pint to format all new PHP files

## Progress Log

2026-02-28 - Plan created. Portfolio module designed as tenant-scoped project showcase with categories, media gallery, technologies, client info, external links, and status workflow. API-only (no Filament). Follows Article module patterns exactly.
2026-02-28 - Phase 1 complete. All 3 migrations created and verified on test tenant. PortfolioStatus enum, 3 models (Portfolio, PortfolioCategory, PortfolioMedia), and 3 factories (PortfolioFactory, PortfolioCategoryFactory, PortfolioMediaFactory) created. All tables, columns, indexes, and foreign keys verified correct.
2026-02-28 - Phase 2 complete. 4 DTOs (Create/Update Portfolio, Create/Update PortfolioCategory), 2 repositories (Portfolio, PortfolioCategory), PortfolioService with full portfolio/category/media logic, 4 actions (Create/Update/Delete/Publish), and PortfolioPolicy with owner-based auth created.
2026-02-28 - Phase 3 complete. 5 Form Requests, 3 controllers (Portfolio, PortfolioCategory, PortfolioMedia), PortfolioServiceProvider, routes (16 endpoints under /api/v1/), and provider registration. All routes verified working.
2026-02-28 - Phase 4 complete. Combined PortfolioCategorySeeder + PortfolioSeeder into single PortfolioSeeder.php following ArticleSeeder pattern. Seeds 3 categories, 6 portfolios (3 published, 2 draft, 1 archived), and media galleries. Tenant seeders run independently (no TenantSeeder registration needed).
2026-02-28 - Phase 5 complete. 6 test files created: PortfolioCrudTest (22 tests), PortfolioCategoryTest (12 tests), PortfolioMediaTest (12 tests), PortfolioPolicyTest (11 tests), PortfolioServiceTest (16 tests), PortfolioDtoTest (16 tests). Total 89 tests, 247 assertions. Fixed bug in PortfolioMediaController::reorder (Request::validated vs Request::validate). Updated ApiDocumentationTest path count from 16 to 25. All 273 tests pass. Pint clean.
