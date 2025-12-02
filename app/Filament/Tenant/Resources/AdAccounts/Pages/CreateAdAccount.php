<?php

namespace App\Filament\Tenant\Resources\AdAccounts\Pages;

use App\Filament\Tenant\Resources\AdAccounts\AdAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdAccount extends CreateRecord
{
    protected static string $resource = AdAccountResource::class;
}
