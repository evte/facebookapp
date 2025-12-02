<?php

namespace App\Filament\Tenant\Resources\BusinessManagers\Pages;

use App\Filament\Tenant\Resources\BusinessManagers\BusinessManagerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessManager extends EditRecord
{
    protected static string $resource = BusinessManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
