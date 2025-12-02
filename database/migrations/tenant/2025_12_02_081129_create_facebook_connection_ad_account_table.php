<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('facebook_connection_ad_account', function (Blueprint $table) {
            $table->foreignId('facebook_connection_id')->constrained('facebook_connections', 'id', 'fc_ad_fc_fk')->onDelete('cascade');
            $table->foreignId('ad_account_id')->constrained('ad_accounts', 'id', 'fc_ad_ad_fk')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['facebook_connection_id', 'ad_account_id'], 'fc_ad_account_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_connection_ad_account');
    }
};
