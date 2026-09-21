<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('property_type', [
                'home', 'complex', 'business', 'office', 'retail', 'estate',
            ])->default('home');
            $table->enum('urgency', ['emergency', '24h', 'week', 'planned'])->default('24h');
            $table->string('area_label')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('exact_address')->nullable();
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();
            $table->enum('status', [
                'draft', 'submitted', 'matched', 'viewed', 'accepted', 'quoted',
                'booked', 'in_progress', 'completed', 'cancelled', 'disputed', 'closed',
            ])->default('submitted');
            $table->foreignId('accepted_provider_company_id')->nullable()
                ->constrained('provider_companies')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_requests');
    }
};
