<?php

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Portfolio;

/*
|--------------------------------------------------------------------------
| PortfolioPolicy
|--------------------------------------------------------------------------
*/

test('any user can view any portfolios', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', Portfolio::class))->toBeTrue();
});

test('any user can view a specific portfolio', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create();

    expect($user->can('view', $portfolio))->toBeTrue();
});

test('any user can create portfolios', function () {
    $user = User::factory()->create();

    expect($user->can('create', Portfolio::class))->toBeTrue();
});

test('portfolio owner can update their portfolio', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    expect($user->can('update', $portfolio))->toBeTrue();
});

test('non-owner cannot update a portfolio', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $other->id]);

    expect($user->can('update', $portfolio))->toBeFalse();
});

test('portfolio owner can delete their portfolio', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    expect($user->can('delete', $portfolio))->toBeTrue();
});

test('non-owner cannot delete a portfolio', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $other->id]);

    expect($user->can('delete', $portfolio))->toBeFalse();
});

test('portfolio owner can publish their portfolio', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    expect($user->can('publish', $portfolio))->toBeTrue();
});

test('non-owner cannot publish a portfolio', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $other->id]);

    expect($user->can('publish', $portfolio))->toBeFalse();
});

test('portfolio owner can archive their portfolio', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

    expect($user->can('archive', $portfolio))->toBeTrue();
});

test('non-owner cannot archive a portfolio', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $other->id]);

    expect($user->can('archive', $portfolio))->toBeFalse();
});
