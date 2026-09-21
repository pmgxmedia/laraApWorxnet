<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('property_type', [
                'home', 'complex', 'business', 'office', 'retail', 'estate',
            ])->default('home');
            $table->enum('budget_type', ['fixed', 'range', 'quote'])->default('range');
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();
            $table->boolean('budget_visible_to_providers')->default(true);
            $table->string('area_label')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('exact_address')->nullable();
            $table->timestamp('bid_deadline')->nullable();
            $table->enum('status', [
                'draft', 'pending_review', 'live', 'paused', 'awarded',
                'booked', 'in_progress', 'completed', 'cancelled', 'disputed',
            ])->default('live');
            $table->foreignId('awarded_provider_company_id')->nullable()
                ->constrained('provider_companies')->nullOnDelete();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
