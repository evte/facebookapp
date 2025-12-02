<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Configure basic tenant information')
                    ->schema([
                        Hidden::make('id')
                            ->default(fn () => Str::uuid()->toString())
                            ->dehydrated(),

                        TextInput::make('name')
                            ->label('Tenant Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn ($context) => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText('At least 8 characters')
                            ->placeholder('Required when creating, leave blank when editing to keep current password'),

                        TextInput::make('domain')
                            ->label('Domain')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('e.g.: tenant1.example.com')
                            ->placeholder('tenant1.example.com'),
                    ])
                    ->columns(2),

                Section::make('Application Credentials')
                    ->description('Application ID and secret key for API access and third-party integration')
                    ->schema([
                        TextInput::make('app_name')
                            ->label('App Name')
                            ->maxLength(255)
                            ->placeholder('e.g.: My Application')
                            ->helperText('Friendly name for the application'),

                        TextInput::make('app_id')
                            ->label('App ID')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter application ID')
                            ->helperText('Unique application identifier for tenant'),

                        TextInput::make('app_secret')
                            ->label('App Secret')
                            ->required()
                            ->maxLength(255)
                            ->password()
                            ->revealable()
                            ->placeholder('Enter application secret')
                            ->helperText('Secret key for API authentication, keep it secure'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
