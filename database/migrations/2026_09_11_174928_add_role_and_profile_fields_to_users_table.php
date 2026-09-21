<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['customer', 'provider', 'provider_team', 'admin', 'super_admin'])
                ->default('customer')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->enum('status', ['active', 'suspended', 'deactivated'])->default('active')->after('phone');
            $table->string('avatar_path')->nullable()->after('status');
            $table->string('preferred_area')->nullable()->after('avatar_path');
            $table->decimal('last_known_lat', 10, 7)->nullable()->after('preferred_area');
            $table->decimal('last_known_lng', 10, 7)->nullable()->after('last_known_lat');
            $table->timestamp('location_shared_at')->nullable()->after('last_known_lng');
            $table->boolean('marketing_opt_in')->default(false)->after('location_shared_at');
            $table->unsignedBigInteger('provider_company_id')->nullable()->after('marketing_opt_in');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'phone', 'status', 'avatar_path', 'preferred_area',
                'last_known_lat', 'last_known_lng', 'location_shared_at', 'marketing_opt_in',
                'provider_company_id',
            ]);
        });
    }
};
