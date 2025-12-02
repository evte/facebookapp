<?php

return [
    App\Providers\AppServiceProvider::class,
    Stancl\Tenancy\TenancyServiceProvider::class,  // Tenancy package provider
    App\Providers\TenancyServiceProvider::class,   // Our custom tenancy provider
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\TenantPanelProvider::class,
];
