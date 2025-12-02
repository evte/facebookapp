<?php

namespace App\Filament\Tenant\Resources\BusinessManagers\Pages;

use App\Filament\Tenant\Resources\BusinessManagers\BusinessManagerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessManager extends CreateRecord
{
    protected static string $resource = BusinessManagerResource::class;
}
