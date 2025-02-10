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
        Schema::create('Config', function (Blueprint $table) {
            $table->id();
            $table->string('ai_model');
            $table->string('ai_api_key');
            $table->string('ai_endpoint');
            $table->json('ai_output_format');
            $table->json('ai_supported_language');
            $table->json('ai_system_messages');
            $table->integer('max_tokens');
            $table->float('temperature');
            $table->float('frequency_penalty');
            $table->float('presence_penalty');
            $table->integer('best_of');
            $table->float('top_p');

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
