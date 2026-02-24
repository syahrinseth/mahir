<?php

namespace App\Shared\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base contract for all repository classes.
 *
 * Repositories abstract data access for a single Eloquent model.
 */
interface RepositoryContract
{
    /**
     * @return Collection<int, Model>
     */
    public function all(): Collection;

    public function findById(int $id): ?Model;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Model;

    public function delete(int $id): bool;
}
