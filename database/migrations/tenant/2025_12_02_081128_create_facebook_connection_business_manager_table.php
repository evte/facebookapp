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
        Schema::create('facebook_connection_business_manager', function (Blueprint $table) {
            $table->foreignId('facebook_connection_id')->constrained('facebook_connections', 'id', 'fc_bm_fc_fk')->onDelete('cascade');
            $table->foreignId('business_manager_id')->constrained('business_managers', 'id', 'fc_bm_bm_fk')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['facebook_connection_id', 'business_manager_id'], 'fc_bm_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_connection_business_manager');
    }
};
