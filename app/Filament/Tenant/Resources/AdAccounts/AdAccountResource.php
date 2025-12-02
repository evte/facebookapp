<?php

namespace App\Filament\Tenant\Resources\AdAccounts;

use App\Filament\Tenant\Resources\AdAccounts\Pages\CreateAdAccount;
use App\Filament\Tenant\Resources\AdAccounts\Pages\EditAdAccount;
use App\Filament\Tenant\Resources\AdAccounts\Pages\ListAdAccounts;
use App\Filament\Tenant\Resources\AdAccounts\Schemas\AdAccountForm;
use App\Filament\Tenant\Resources\AdAccounts\Tables\AdAccountsTable;
use App\Models\AdAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdAccountResource extends Resource
{
    protected static ?string $model = AdAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return AdAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdAccounts::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
