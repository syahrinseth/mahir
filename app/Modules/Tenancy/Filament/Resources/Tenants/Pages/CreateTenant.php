<?php

namespace App\Modules\Tenancy\Filament\Resources\Tenants\Pages;

use App\Modules\Tenancy\Actions\CreateTenantAction;
use App\Modules\Tenancy\Filament\Resources\Tenants\TenantResource;
use App\Shared\Exceptions\TenantDatabaseException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /**
     * Override default record creation to use CreateTenantAction,
     * which provisions the tenant database, runs migrations, and seeds data.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $action = app(CreateTenantAction::class);

        try {
            $tenant = $action->execute($data);

            Log::info('Tenant created via Filament', [
                'tenant_id' => $tenant->id,
                'slug' => $tenant->slug,
                'database' => $tenant->database,
            ]);

            Notification::make()
                ->success()
                ->title('Tenant database provisioned')
                ->body("Database [{$tenant->database}] created, migrated, and seeded successfully.")
                ->send();

            return $tenant;
        } catch (TenantDatabaseException $e) {
            Log::error('Tenant database provisioning failed in Filament', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            Notification::make()
                ->danger()
                ->title('Database provisioning failed')
                ->body($e->getMessage())
                ->persistent()
                ->send();

            $this->halt(shouldRollbackDatabaseTransaction: true);
        } catch (\Exception $e) {
            Log::error('Unexpected error during Filament tenant creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            Notification::make()
                ->danger()
                ->title('Tenant creation failed')
                ->body('An unexpected error occurred: '.$e->getMessage())
                ->persistent()
                ->send();

            $this->halt(shouldRollbackDatabaseTransaction: true);
        }

        /** @phpstan-ignore deadCode.unreachable */
        throw new \RuntimeException('Tenant creation halted unexpectedly.');
    }
}
