<?php

use App\Modules\Tenancy\Models\Tenant;

test('admin subdomain is allowed through without tenant resolution', function () {
    $response = $this->get('http://admin.mahir.test/');

    $response->assertRedirect();
});

test('www subdomain is allowed through without tenant resolution', function () {
    $response = $this->get('http://www.mahir.test/');

    $response->assertSuccessful();
});

test('base domain is allowed through without tenant resolution', function () {
    $response = $this->get('http://mahir.test/');

    $response->assertSuccessful();
});

test('valid tenant subdomain resolves and allows request', function () {
    $tenant = Tenant::factory()->create(['slug' => 'acme']);

    $response = $this->get('http://acme.mahir.test/');

    $response->assertSuccessful();
});

test('non-existent tenant subdomain returns 404', function () {
    $response = $this->get('http://nonexistent.mahir.test/');

    $response->assertNotFound();
});

test('inactive tenant subdomain returns 404', function () {
    Tenant::factory()->inactive()->create(['slug' => 'dormant']);

    $response = $this->get('http://dormant.mahir.test/');

    $response->assertNotFound();
});
