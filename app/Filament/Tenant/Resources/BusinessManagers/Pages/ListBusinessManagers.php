<?php

namespace App\Filament\Tenant\Resources\BusinessManagers\Pages;

use App\Filament\Tenant\Resources\BusinessManagers\BusinessManagerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusinessManagers extends ListRecords
{
    protected static string $resource = BusinessManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
