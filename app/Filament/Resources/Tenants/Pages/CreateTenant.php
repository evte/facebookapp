<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Database creation and migration are handled automatically by
        // TenancyServiceProvider's event listeners (TenantCreated event)
        // See: app/Providers/TenancyServiceProvider.php:26-38

        Notification::make()
            ->success()
            ->title('Tenant Created Successfully')
            ->body('Tenant database is being created and initialized.')
            ->send();
    }
}
