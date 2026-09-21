<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_company_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->text('scope_included')->nullable();
            $table->text('exclusions')->nullable();
            $table->string('warranty_terms')->nullable();
            $table->date('estimated_start')->nullable();
            $table->unsignedInteger('estimated_duration_days')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['submitted', 'shortlisted', 'awarded', 'declined', 'withdrawn'])
                ->default('submitted');
            $table->timestamps();

            $table->unique(['project_id', 'provider_company_id'], 'project_provider_bid_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_bids');
    }
};
