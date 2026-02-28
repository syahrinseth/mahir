<?php

use App\Modules\Tenancy\TenantFinder\SubdomainTenantFinder;
use Illuminate\Http\Request;

test('requiresTenant returns true for tenant subdomain', function () {
    $finder = new SubdomainTenantFinder;

    $request = Request::create('http://acme.mahir.test/');

    expect($finder->requiresTenant($request))->toBeTrue();
});

test('requiresTenant returns false for base domain', function () {
    $finder = new SubdomainTenantFinder;

    $request = Request::create('http://mahir.test/');

    expect($finder->requiresTenant($request))->toBeFalse();
});

test('requiresTenant returns false for admin subdomain', function () {
    $finder = new SubdomainTenantFinder;

    $request = Request::create('http://admin.mahir.test/');

    expect($finder->requiresTenant($request))->toBeFalse();
});

test('requiresTenant returns false for www subdomain', function () {
    $finder = new SubdomainTenantFinder;

    $request = Request::create('http://www.mahir.test/');

    expect($finder->requiresTenant($request))->toBeFalse();
});

test('requiresTenant returns true for non-reserved subdomain', function () {
    $finder = new SubdomainTenantFinder;

    $request = Request::create('http://my-company.mahir.test/');

    expect($finder->requiresTenant($request))->toBeTrue();
});
