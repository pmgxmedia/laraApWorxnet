<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->text('speciality_notes')->nullable();
            $table->json('brands')->nullable();
            $table->unsignedInteger('years_experience')->nullable();
            $table->timestamps();

            $table->unique(['provider_company_id', 'service_category_id'], 'provider_service_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('provider_services');
    }
};
