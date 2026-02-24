<?php

namespace App\Shared\Traits;

/**
 * Assigns the landlord database connection to the model.
 *
 * Use this trait on any Eloquent model whose underlying table
 * lives in the landlord (central) database.
 */
trait UsesLandlordConnection
{
    public function getConnectionName(): ?string
    {
        return config('multitenancy.landlord_database_connection_name');
    }
}
