<?php

namespace App\Filament\Tenant\Resources\Pixels\Pages;

use App\Filament\Tenant\Resources\Pixels\PixelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPixel extends EditRecord
{
    protected static string $resource = PixelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
