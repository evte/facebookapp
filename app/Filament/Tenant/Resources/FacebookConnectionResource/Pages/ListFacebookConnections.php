<?php

namespace App\Filament\Tenant\Resources\FacebookConnectionResource\Pages;

use App\Filament\Tenant\Resources\FacebookConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacebookConnections extends ListRecords
{
    protected static string $resource = FacebookConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
             Actions\Action::make('connect_facebook')
                ->label('Connect New Account')
                ->icon('heroicon-o-link')
                ->color('primary')
                ->url(route('tenant.facebook.connect')),
        ];
    }
}
