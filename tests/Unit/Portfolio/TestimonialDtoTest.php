<?php

use App\Modules\Portfolio\DTOs\CreateTestimonialDTO;
use App\Modules\Portfolio\DTOs\UpdateTestimonialDTO;

/*
|--------------------------------------------------------------------------
| CreateTestimonialDTO
|--------------------------------------------------------------------------
*/

test('CreateTestimonialDTO fromArray creates instance with required fields', function () {
    $dto = CreateTestimonialDTO::fromArray([
        'user_id' => 1,
        'client_name' => 'Jane Doe',
        'content' => 'Great work!',
    ]);

    expect($dto)
        ->userId->toBe(1)
        ->clientName->toBe('Jane Doe')
        ->content->toBe('Great work!')
        ->portfolioId->toBeNull()
        ->clientPosition->toBeNull()
        ->clientCompany->toBeNull()
        ->rating->toBeNull()
        ->isFeatured->toBeFalse()
        ->sortOrder->toBe(0)
        ->publishedAt->toBeNull();
});

test('CreateTestimonialDTO fromArray accepts all optional fields', function () {
    $dto = CreateTestimonialDTO::fromArray([
        'user_id' => 1,
        'client_name' => 'John Smith',
        'content' => 'Excellent service.',
        'portfolio_id' => 5,
        'client_position' => 'CEO',
        'client_company' => 'Acme Corp',
        'rating' => 5,
        'is_featured' => true,
        'sort_order' => 3,
        'published_at' => '2026-03-01 10:00:00',
    ]);

    expect($dto)
        ->portfolioId->toBe(5)
        ->clientPosition->toBe('CEO')
        ->clientCompany->toBe('Acme Corp')
        ->rating->toBe(5)
        ->isFeatured->toBeTrue()
        ->sortOrder->toBe(3)
        ->publishedAt->toBe('2026-03-01 10:00:00');
});

test('CreateTestimonialDTO toArray returns all fields', function () {
    $dto = new CreateTestimonialDTO(
        userId: 1,
        clientName: 'Test Client',
        content: 'Great project.',
        rating: 4,
    );

    $array = $dto->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKey('user_id', 1)
        ->toHaveKey('client_name', 'Test Client')
        ->toHaveKey('content', 'Great project.')
        ->toHaveKey('rating', 4)
        ->toHaveKey('is_featured', false)
        ->toHaveKey('sort_order', 0)
        ->toHaveKey('portfolio_id', null)
        ->toHaveKey('published_at', null);
});

/*
|--------------------------------------------------------------------------
| UpdateTestimonialDTO
|--------------------------------------------------------------------------
*/

test('UpdateTestimonialDTO fromArray creates instance with partial fields', function () {
    $dto = UpdateTestimonialDTO::fromArray([
        'client_name' => 'Updated Name',
    ]);

    expect($dto)
        ->clientName->toBe('Updated Name')
        ->content->toBeNull()
        ->portfolioId->toBeNull()
        ->rating->toBeNull()
        ->isFeatured->toBeNull();
});

test('UpdateTestimonialDTO toArray filters null values', function () {
    $dto = UpdateTestimonialDTO::fromArray([
        'client_name' => 'Only Name',
    ]);

    $array = $dto->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKey('client_name', 'Only Name')
        ->not->toHaveKey('content')
        ->not->toHaveKey('portfolio_id')
        ->not->toHaveKey('rating');
});

test('UpdateTestimonialDTO toArray includes all non-null values', function () {
    $dto = UpdateTestimonialDTO::fromArray([
        'client_name' => 'Updated',
        'content' => 'Updated review.',
        'rating' => 5,
        'is_featured' => true,
    ]);

    $array = $dto->toArray();

    expect($array)
        ->toHaveCount(4)
        ->toHaveKey('client_name', 'Updated')
        ->toHaveKey('content', 'Updated review.')
        ->toHaveKey('rating', 5)
        ->toHaveKey('is_featured', true);
});

test('UpdateTestimonialDTO toArray preserves falsy values for provided fields', function () {
    $dto = UpdateTestimonialDTO::fromArray([
        'is_featured' => false,
        'sort_order' => 0,
        'rating' => 0,
    ]);

    $array = $dto->toArray();

    expect($array)
        ->toHaveCount(3)
        ->toHaveKey('is_featured', false)
        ->toHaveKey('sort_order', 0)
        ->toHaveKey('rating', 0);
});

test('UpdateTestimonialDTO toArray only includes explicitly provided fields', function () {
    $dto = UpdateTestimonialDTO::fromArray([
        'client_name' => 'Updated Name',
        'is_featured' => false,
    ]);

    $array = $dto->toArray();

    expect($array)
        ->toHaveCount(2)
        ->toHaveKey('client_name', 'Updated Name')
        ->toHaveKey('is_featured', false)
        ->not->toHaveKey('content')
        ->not->toHaveKey('rating')
        ->not->toHaveKey('sort_order');
});
