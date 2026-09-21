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
        Schema::table('provider_companies', function (Blueprint $table) {
            $table->index(['base_lat', 'base_lng'], 'provider_companies_base_lat_base_lng_index');
            $table->index(['is_active', 'verification_status'], 'provider_companies_active_verification_index');
        });

        Schema::table('provider_service_areas', function (Blueprint $table) {
            $table->index(['center_lat', 'center_lng'], 'provider_service_areas_center_lat_center_lng_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_companies', function (Blueprint $table) {
            $table->dropIndex('provider_companies_base_lat_base_lng_index');
            $table->dropIndex('provider_companies_active_verification_index');
        });

        Schema::table('provider_service_areas', function (Blueprint $table) {
            $table->dropIndex('provider_service_areas_center_lat_center_lng_index');
        });
    }
};
