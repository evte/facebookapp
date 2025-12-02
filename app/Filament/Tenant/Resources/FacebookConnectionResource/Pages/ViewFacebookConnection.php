<?php

namespace App\Filament\Tenant\Resources\FacebookConnectionResource\Pages;

use App\Filament\Tenant\Resources\FacebookConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFacebookConnection extends ViewRecord
{
    protected static string $resource = FacebookConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
