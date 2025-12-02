<?php

namespace App\Filament\Tenant\Resources\Pixels\Pages;

use App\Filament\Tenant\Resources\Pixels\PixelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPixels extends ListRecords
{
    protected static string $resource = PixelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
