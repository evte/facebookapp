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
        Schema::table('domains', function (Blueprint $table) {
            $table->string('dns_status')->default('unchecked')->after('domain');
            $table->string('ssl_status')->default('none')->after('dns_status');
            $table->boolean('is_accessible')->default(false)->after('ssl_status');
            $table->timestamp('last_checked_at')->nullable()->after('is_accessible');
            $table->text('check_message')->nullable()->after('last_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn([
                'dns_status',
                'ssl_status',
                'is_accessible',
                'last_checked_at',
                'check_message'
            ]);
        });
    }
};
