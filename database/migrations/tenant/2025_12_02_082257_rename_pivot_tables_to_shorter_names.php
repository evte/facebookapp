<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('facebook_connection_business_manager', 'facebook_business_managers');
        Schema::rename('facebook_connection_page', 'facebook_pages');
        Schema::rename('facebook_connection_ad_account', 'facebook_ad_accounts');
        Schema::rename('facebook_connection_pixel', 'facebook_pixels');
    }

    public function down(): void
    {
        Schema::rename('facebook_business_managers', 'facebook_connection_business_manager');
        Schema::rename('facebook_pages', 'facebook_connection_page');
        Schema::rename('facebook_ad_accounts', 'facebook_connection_ad_account');
        Schema::rename('facebook_pixels', 'facebook_connection_pixel');
    }
};
