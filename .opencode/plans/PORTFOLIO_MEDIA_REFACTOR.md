# Project Plan - Portfolio Media Refactor to Spatie

Created: 2026-03-02
Source: manual

## Instructions

- Update this file every 5 completed items (checkpoint save)
- Do not commit this plan file -- it is your AI's working reference
- Commits are done after user approval (usually one commit per phase)
- This refactor maintains identical API response shape but adopts Spatie's internal implementation

## Goal

Migrate the Portfolio module's custom `PortfolioMedia` model to **Spatie Laravel Media Library** v10+, providing:
- Polymorphic media attachment (reusable for future modules like Article)
- Automatic image conversions (thumbnails, responsive images)
- Built-in file lifecycle management and cleanup
- Named collections (gallery, featured)
- Custom properties for caption, sort_order metadata
- Multi-tenant isolation via tenant-aware path generator

## Architecture

### Current System (Custom)
```
Portfolio (has_many)
    └─ PortfolioMedia
        ├── file_path (stored path)
        ├── file_name
        ├── file_size
        ├── mime_type
        ├── caption
        ├── sort_order
        └── (manual file storage/cleanup)
```

### New System (Spatie)
```
Portfolio (HasMedia)
    └─ Media (polymorphic, single table)
        ├── collection_name (gallery, featured)
        ├── custom_properties (JSON: caption, sort_order)
        ├── disk (public, s3, etc)
        ├── manipulations (conversion metadata)
        ├── responsive_images (variant metadata)
        └── (automatic conversions, cleanup)
```

### Key Changes
1. Drop `PortfolioMedia` model entirely
2. Add Spatie `Media` model (global, used by all models)
3. Implement `HasMedia` interface + `InteractsWithMedia` trait on `Portfolio`
4. Define media collections: `gallery` (multi-file), `featured` (single)
5. Register conversions: `thumb` (300x300), `medium` (600x400), `display` (1200x600)
6. Add responsive images to gallery collection
7. Store caption & sort_order in `custom_properties` JSON
8. Update all queries/methods to use Spatie API
9. Maintain API response shape (clients see no difference)

### File Changes Summary
| File | Action | Notes |
|------|--------|-------|
| `PortfolioMedia` model | Delete | Spatie handles this |
| `PortfolioMediaController` | Rewrite | Use `addMediaFromRequest()`, `getMedia()`, custom properties |
| `PortfolioService` | Update | Simplify media logic, use Spatie API |
| `PortfolioRepository` | Update | Remove media queries (Spatie handles) |
| `PortfolioMediaFactory` | Delete | No longer needed |
| Migrations | Create | Publish Spatie schema, add tenant_id, remove old media table |
| `PortfolioSeeder` | Update | Use Spatie API to seed media |
| Tests (feature) | Rewrite | Test new Spatie endpoints, custom properties |
| Tests (unit) | Update | Test service with Spatie methods |
| Config | Create | Publish & customize media-library.php |

## Implementation Plan

### Phase 1: Package Setup & Database

- [ ] Install `spatie/laravel-medialibrary` package via composer
- [ ] Publish Spatie migrations (creates `media` table)
- [ ] Create custom migration: add `tenant_id` to media table, add index on tenant_id
- [ ] Create custom migration: drop `portfolio_media` table (old custom model)
- [ ] Publish media-library config file (config/media-library.php)
- [ ] Update config: set disk to 'public', enable queue conversions, verify paths

### Phase 2: Multi-Tenant Configuration

- [ ] Create `MultiTenantPathGenerator` class in app/Support/MediaLibrary/
- [ ] Implement `getPath()`, `getPathForConversions()`, `getPathForResponsiveImages()`
- [ ] Register custom path generator in bootstrap/providers.php or service provider
- [ ] Create tenant-aware Media model (add tenant_id on create, global scope)
- [ ] Test path generation: verify media stored in tenants/{id}/ directory

### Phase 3: Portfolio Model Refactor

- [ ] Remove `PortfolioMedia` model file
- [ ] Add `HasMedia` interface to Portfolio model
- [ ] Add `InteractsWithMedia` trait to Portfolio model
- [ ] Implement `registerMediaCollections()` method:
  - Define 'gallery' collection (multi-file, jpg/png/webp)
  - Define 'featured' collection (singleFile, jpg/png)
  - Register conversions for both collections
- [ ] Implement `registerMediaConversions()` method:
  - Create 'thumb' conversion (300x300, crop, queued)
  - Create 'medium' conversion (600x400, contain, queued)
  - Create 'display' conversion (1200x600, crop)
  - Enable responsive images for gallery
- [ ] Delete `PortfolioMediaFactory` file
- [ ] Delete `hasMedia()` relationship method from Portfolio (use Spatie's `getMedia()` instead)

### Phase 4: Service & Repository Layer

- [ ] Update `PortfolioService.uploadMedia()` method:
  - Accept file upload + caption, sort_order, isPublished
  - Use `portfolio->addMediaFromRequest()` with custom properties
  - Return media URL + metadata
- [ ] Update `PortfolioService.deleteMedia()` method:
  - Accept media ID
  - Fetch media, authorize, call `$media->delete()`
- [ ] Update `PortfolioService.reorderMedia()` method:
  - Accept array of {id, sort_order} pairs
  - Batch update custom properties via `setCustomProperty()`
  - No longer need separate database query
- [ ] Update `PortfolioService.getPortfolioWithMedia()` method:
  - Use `portfolio->getMedia('gallery')` instead of `portfolio->media`
  - Use `portfolio->getFirstMedia('featured')` instead of `portfolio->featuredMedia`
  - Transform response: id, url, thumb, caption, sort_order
- [ ] Update `PortfolioRepository.getWithMedia()` method:
  - Simplify: just fetch portfolio, let service handle media
  - Or remove entirely (service can call model directly)
- [ ] Verify no N+1 queries (eager load with Spatie)

### Phase 5: Controller Refactor

- [ ] Rewrite `PortfolioMediaController.storeGallery()`:
  - Validate request: image required, max 10MB
  - Call service: `uploadMedia()` with collection='gallery'
  - Return JSON: {id, url, thumbnail, caption}
- [ ] Rewrite `PortfolioMediaController.storeFeatured()`:
  - Validate request: image required
  - Call service: `uploadMedia()` with collection='featured'
  - Featured is singleFile, so it replaces previous
  - Return JSON: {url, display}
- [ ] Rewrite `PortfolioMediaController.update()`:
  - Accept media ID + {caption, sort_order, isPublished}
  - Find media, authorize, update custom properties
  - Return {success: true}
- [ ] Rewrite `PortfolioMediaController.delete()`:
  - Accept media ID
  - Find media, authorize, call service.deleteMedia()
  - Return {success: true}
- [ ] Rewrite `PortfolioMediaController.reorder()`:
  - Accept array of {id, sort_order} pairs
  - Call service.reorderMedia()
  - Return {success: true}

### Phase 6: Seeder Refactor

- [ ] Update `PortfolioSeeder` to use Spatie API:
  - Remove `PortfolioMediaFactory` references
  - Use `portfolio->addMedia()` or `addMediaFromDisk()`
  - Populate custom properties: caption, sort_order, isPublished
  - Seed featured image for each portfolio (different image than gallery)
  - Seed 5-8 gallery images per portfolio with varied captions

### Phase 7: Test Refactor - Feature Tests

- [ ] Rewrite `PortfolioMediaTest.testUploadGalleryImage()`:
  - POST /portfolios/{id}/media/gallery with image
  - Assert 200 response
  - Assert media created in 'gallery' collection
  - Assert custom properties stored
- [ ] Rewrite `PortfolioMediaTest.testUploadFeaturedImage()`:
  - POST /portfolios/{id}/media/featured with image
  - Assert single file (replaces previous if exists)
- [ ] Rewrite `PortfolioMediaTest.testUpdateMediaCaption()`:
  - PATCH /media/{id} with caption, sort_order, isPublished
  - Assert custom properties updated in database
- [ ] Rewrite `PortfolioMediaTest.testDeleteMedia()`:
  - DELETE /media/{id}
  - Assert media deleted from database
  - Assert files removed from disk
- [ ] Rewrite `PortfolioMediaTest.testReorderMedia()`:
  - POST /portfolios/{id}/media/reorder with {id, sort_order} array
  - Assert sort_order custom property updated
  - Assert gallery query order preserved
- [ ] Add new test: `testUnauthorizedMediaDelete()` - non-owner cannot delete
- [ ] Add new test: `testMediaConversionsGenerated()` - thumb/medium created
- [ ] Add new test: `testMultiTenantIsolation()` - tenant A cannot access tenant B media

### Phase 8: Test Refactor - Unit Tests

- [ ] Update `PortfolioServiceTest.testUploadMedia()`:
  - Mock request file
  - Assert service calls addMediaFromRequest()
  - Assert custom properties set
- [ ] Update `PortfolioServiceTest.testDeleteMedia()`:
  - Mock Media model
  - Assert delete() called
- [ ] Update `PortfolioServiceTest.testReorderMedia()`:
  - Mock media items
  - Assert setCustomProperty() called for each
- [ ] Remove old tests referencing PortfolioMediaFactory

### Phase 9: API Consistency Check

- [ ] Verify all current API responses still work:
  - GET /portfolios/{id} returns featured + gallery with same shape
  - POST /portfolios/{id}/media/gallery returns media object
  - PATCH /media/{id} returns updated media
  - DELETE /media/{id} returns success
- [ ] Compare response shapes: old vs new
  - Should be identical from client perspective
  - Just internal implementation changed
- [ ] Test media URLs: verify getUrl() works correctly
- [ ] Test conversions: verify thumb/medium URLs resolve

### Phase 10: Configuration & Optimization

- [ ] Review config/media-library.php:
  - Disk: 'public' (or custom 'media' disk)
  - Max file size: 10MB (or adjust as needed)
  - Queue conversions: true (async image processing)
  - Image driver: 'gd' or 'imagick' (verify installed)
- [ ] Update .gitignore to exclude media files:
  - /public/media
  - /storage/app/media
- [ ] Verify responsive images enabled in gallery collection
- [ ] Test image conversion queueing: check queue jobs created

### Phase 11: Cleanup & Migration

- [ ] Delete `PortfolioMediaFactory` file
- [ ] Delete old `portfolio_media` migration (if exists alongside new one)
- [ ] Remove `PortfolioMedia` model import from anywhere it's referenced
- [ ] Remove `PortfolioMedia` from any container bindings
- [ ] Verify no remaining references to old model in tests
- [ ] Check codebase for any comments mentioning custom media model

### Phase 12: Verification & Deployment Readiness

- [ ] Run full test suite: `php artisan test --compact` (all 273+ tests pass)
- [ ] Run formatter: `vendor/bin/pint --dirty` (code style compliance)
- [ ] Manual testing:
  - Create portfolio in API
  - Upload gallery images (test custom properties)
  - Upload featured image
  - Test reorder endpoint
  - Test update caption
  - Test delete (verify files removed)
  - Test multi-tenant isolation (two tenants don't see each other's media)
- [ ] Verify database:
  - Media table properly populated
  - Tenant isolation working (media.tenant_id set)
  - Custom properties JSON formatted correctly
- [ ] Documentation: add notes to ARCHITECTURE.md about Spatie setup
- [ ] Final commit message: "Refactor: Migrate Portfolio media to Spatie Media Library"

## Progress Log

2026-03-02 - Plan created with 12 phases, 51 detailed tasks covering database, models, controllers, tests, and verification
