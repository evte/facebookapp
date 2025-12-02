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
        Schema::create('facebook_connection_pixel', function (Blueprint $table) {
            $table->foreignId('facebook_connection_id')->constrained('facebook_connections', 'id', 'fc_pixel_fc_fk')->onDelete('cascade');
            $table->foreignId('pixel_id')->constrained('pixels', 'id', 'fc_pixel_px_fk')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['facebook_connection_id', 'pixel_id'], 'fc_pixel_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_connection_pixel');
    }
};
