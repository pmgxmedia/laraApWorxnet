<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('provider_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('job_title')->nullable();
            $table->boolean('can_accept_jobs')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['provider_company_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('provider_team_members');
    }
};
