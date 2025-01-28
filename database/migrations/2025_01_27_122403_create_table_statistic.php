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
        Schema::create('statistic', function (Blueprint $table) {
            $table->id();
            $table->json('page_views');
            $table->json('registered_users');
            $table->json('active_users');
            $table->json('sales');

            // Development Purpose
            $table->json('api_requests');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistic');
    }
};
