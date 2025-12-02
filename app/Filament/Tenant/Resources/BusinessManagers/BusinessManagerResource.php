<?php

namespace App\Filament\Tenant\Resources\BusinessManagers;

use App\Filament\Tenant\Resources\BusinessManagers\Pages\CreateBusinessManager;
use App\Filament\Tenant\Resources\BusinessManagers\Pages\EditBusinessManager;
use App\Filament\Tenant\Resources\BusinessManagers\Pages\ListBusinessManagers;
use App\Filament\Tenant\Resources\BusinessManagers\Schemas\BusinessManagerForm;
use App\Filament\Tenant\Resources\BusinessManagers\Tables\BusinessManagersTable;
use App\Models\BusinessManager;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BusinessManagerResource extends Resource
{
    protected static ?string $model = BusinessManager::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Business';

    public static function form(Schema $schema): Schema
    {
        return BusinessManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessManagersTable::configure($table);
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
            'index' => ListBusinessManagers::route('/'),
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
