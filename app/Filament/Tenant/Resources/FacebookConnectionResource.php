<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\FacebookConnectionResource\Pages;
use App\Models\FacebookConnection;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class FacebookConnectionResource extends Resource
{
    protected static ?string $model = FacebookConnection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Facebook';

    protected static ?string $modelLabel = 'Facebook';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->disabled(),
                Forms\Components\TextInput::make('email')
                    ->disabled(),
                Forms\Components\TextInput::make('facebook_id')
                    ->disabled(),
                Forms\Components\Section::make('Connected Assets')
                    ->schema([
                        Forms\Components\Placeholder::make('business_managers_count')
                            ->label('Business Managers')
                            ->content(fn ($record) => $record ? $record->businessManagers()->count() : 0),
                        Forms\Components\Placeholder::make('pages_count')
                            ->label('Pages')
                            ->content(fn ($record) => $record ? $record->pages()->count() : 0),
                        Forms\Components\Placeholder::make('ad_accounts_count')
                            ->label('Ad Accounts')
                            ->content(fn ($record) => $record ? $record->adAccounts()->count() : 0),
                        Forms\Components\Placeholder::make('pixels_count')
                            ->label('Pixels')
                            ->content(fn ($record) => $record ? $record->pixels()->count() : 0),
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('facebook_id')
                    ->label('FB ID'),
                Tables\Columns\TextColumn::make('businessManagers_count')
                    ->counts('businessManagers')
                    ->label('BMs'),
                Tables\Columns\TextColumn::make('pages_count')
                    ->counts('pages')
                    ->label('Pages'),
                Tables\Columns\TextColumn::make('adAccounts_count')
                    ->counts('adAccounts')
                    ->label('Ad Accounts'),
                Tables\Columns\TextColumn::make('pixels_count')
                    ->counts('pixels')
                    ->label('Pixels'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListFacebookConnections::route('/'),
            'view' => Pages\ViewFacebookConnection::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
