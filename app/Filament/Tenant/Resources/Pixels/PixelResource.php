<?php

namespace App\Filament\Tenant\Resources\Pixels;

use App\Filament\Tenant\Resources\Pixels\Pages\CreatePixel;
use App\Filament\Tenant\Resources\Pixels\Pages\EditPixel;
use App\Filament\Tenant\Resources\Pixels\Pages\ListPixels;
use App\Filament\Tenant\Resources\Pixels\Schemas\PixelForm;
use App\Filament\Tenant\Resources\Pixels\Tables\PixelsTable;
use App\Models\Pixel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PixelResource extends Resource
{
    protected static ?string $model = Pixel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracket;

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return PixelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PixelsTable::configure($table);
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
            'index' => ListPixels::route('/'),
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
