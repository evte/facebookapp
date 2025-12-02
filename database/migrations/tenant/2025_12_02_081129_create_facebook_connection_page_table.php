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
        Schema::create('facebook_connection_page', function (Blueprint $table) {
            $table->foreignId('facebook_connection_id')->constrained('facebook_connections', 'id', 'fc_page_fc_fk')->onDelete('cascade');
            $table->foreignId('page_id')->constrained('pages', 'id', 'fc_page_page_fk')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['facebook_connection_id', 'page_id'], 'fc_page_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_connection_page');
    }
};
