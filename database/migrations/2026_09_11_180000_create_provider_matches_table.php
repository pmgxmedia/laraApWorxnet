<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('provider_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_company_id')->constrained()->cascadeOnDelete();
            $table->decimal('match_score', 6, 2)->default(0);
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->enum('status', ['notified', 'viewed', 'responded', 'declined', 'expired'])->default('notified');
            $table->timestamps();

            $table->unique(['service_request_id', 'provider_company_id'], 'service_request_provider_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('provider_matches');
    }
};
