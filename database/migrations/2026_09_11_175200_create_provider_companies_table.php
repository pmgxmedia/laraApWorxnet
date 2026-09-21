<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('provider_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['individual', 'company'])->default('individual');
            $table->string('display_name');
            $table->string('slug')->unique();
            $table->string('registration_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->text('bio')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_area')->nullable();
            $table->decimal('base_lat', 10, 7)->nullable();
            $table->decimal('base_lng', 10, 7)->nullable();
            $table->unsignedInteger('travel_radius_km')->default(20);
            $table->boolean('emergency_available')->default(false);
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('completed_jobs')->default(0);
            $table->unsignedInteger('response_minutes_avg')->nullable();
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'rejected', 'suspended'])
                ->default('unverified');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('provider_companies');
    }
};
