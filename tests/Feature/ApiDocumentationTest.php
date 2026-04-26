<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Tenancy\Models\Tenant;
use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

beforeEach(function () {
    $this->withoutMiddleware([
        RestrictedDocsAccess::class,
        IdentifyTenant::class,
    ]);

    $this->tenant = Tenant::factory()->create();
    $this->tenantBaseUrl = 'http://'.$this->tenant->slug.'.'.config('multitenancy.base_domain');
});

test('api documentation ui page loads successfully', function () {
    $response = $this->get($this->tenantBaseUrl.'/docs/api');

    $response->assertSuccessful();
});

test('api documentation json spec loads successfully', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'openapi',
            'info' => ['title', 'version'],
            'paths',
            'components',
        ]);
});

test('api spec contains correct openapi version', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $response->assertSuccessful();

    $spec = $response->json();

    expect($spec['openapi'])->toStartWith('3.1.');
});

test('api spec contains correct api info', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $spec = $response->json();

    expect($spec['info']['version'])->toBe('1.0.0')
        ->and($spec['info']['title'])->not->toBeEmpty();
});

test('api spec contains bearer authentication security scheme', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $spec = $response->json();

    expect($spec['components']['securitySchemes'])->toHaveKey('http')
        ->and($spec['components']['securitySchemes']['http']['type'])->toBe('http')
        ->and($spec['components']['securitySchemes']['http']['scheme'])->toBe('bearer');
});

test('api spec documents all auth endpoints', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $paths = $response->json('paths');

    expect($paths)->toHaveKey('/auth/register')
        ->toHaveKey('/auth/login')
        ->toHaveKey('/auth/logout')
        ->toHaveKey('/auth/user');
});

test('api spec marks register and login as unauthenticated', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $paths = $response->json('paths');

    $registerSecurity = $paths['/auth/register']['post']['security'] ?? null;
    $loginSecurity = $paths['/auth/login']['post']['security'] ?? null;

    expect($registerSecurity)->toBe([])
        ->and($loginSecurity)->toBe([]);
});

test('api spec includes validation error responses', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $paths = $response->json('paths');

    $registerResponses = array_keys($paths['/auth/register']['post']['responses']);
    $loginResponses = array_keys($paths['/auth/login']['post']['responses']);

    expect($registerResponses)->toContain(422)
        ->and($loginResponses)->toContain(422);
});

test('api spec groups endpoints by tag', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $paths = $response->json('paths');

    $registerTags = $paths['/auth/register']['post']['tags'] ?? [];

    expect($registerTags)->toContain('Auth');
});

test('api spec documents request body fields for register endpoint', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $spec = $response->json();

    $registerSchema = $spec['components']['schemas']['RegisterRequest'] ?? null;

    expect($registerSchema)->not->toBeNull()
        ->and($registerSchema['properties'])->toHaveKey('name')
        ->and($registerSchema['properties'])->toHaveKey('email')
        ->and($registerSchema['properties'])->toHaveKey('password')
        ->and($registerSchema['properties']['name']['description'])->not->toBeEmpty()
        ->and($registerSchema['properties']['email']['description'])->not->toBeEmpty()
        ->and($registerSchema['properties']['password']['description'])->not->toBeEmpty();
});

test('api spec documents the total expected number of path entries', function () {
    $response = $this->getJson($this->tenantBaseUrl.'/docs/api.json');

    $paths = $response->json('paths');

    // ping, auth/register, auth/login, auth/logout, auth/user,
    // articles (index+store), articles/{article} (show+update+destroy),
    // articles/{article}/publish, articles/{article}/archive,
    // articles/{article}/comments (index+store), articles/{article}/comments/{comment} (destroy),
    // articles/{article}/revisions (index), articles/{article}/revisions/{revision} (show),
    // articles/{article}/restore-revision/{revision} (restore),
    // article-series (index+store), article-series/{series} (show+update+destroy),
    // portfolios (index+store), portfolios/{portfolio} (show+update+destroy),
    // portfolios/{portfolio}/publish, portfolios/{portfolio}/archive,
    // portfolios/{portfolio}/media (index+store), portfolios/{portfolio}/media/{media} (destroy),
    // portfolios/{portfolio}/media/reorder,
    // portfolio-categories (index+store), portfolio-categories/{category} (show+update+destroy)
    // testimonials (index+store), testimonials/{testimonial} (show+update+destroy),
    // testimonials/{testimonial}/publish,
    // testimonials/{testimonial}/media (index+store), testimonials/{testimonial}/media/{media} (destroy)
    expect(count($paths))->toBe(33);
});

test('api docs ui returns 404 on admin subdomain', function () {
    $response = $this->get('http://admin.mahir.test/docs/api');

    $response->assertNotFound();
});

test('api docs json returns 404 on admin subdomain', function () {
    $response = $this->getJson('http://admin.mahir.test/docs/api.json');

    $response->assertNotFound();
});
