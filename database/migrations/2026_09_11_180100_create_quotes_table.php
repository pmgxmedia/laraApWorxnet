<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_company_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->json('line_items')->nullable();
            $table->boolean('vat_included')->default(false);
            $table->text('exclusions')->nullable();
            $table->string('warranty_terms')->nullable();
            $table->date('valid_until')->nullable();
            $table->enum('status', ['submitted', 'accepted', 'declined', 'withdrawn', 'expired'])->default('submitted');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotes');
    }
};
