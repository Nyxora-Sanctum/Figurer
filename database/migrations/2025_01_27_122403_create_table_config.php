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
            $table->string('ai-model');
            $table->string('ai-api-key');
            $table->string('ai-endpoint');
            $table->json('ai-output-format');
            $table->json('ai-supported-language');
            $table->json('ai-system-messages');
            $table->integer('max-tokens');
            $table->float('temperature');
            $table->float('frequency-penalty');
            $table->float('presence-penalty');
            $table->integer('best-of');
            $table->float('top-p');

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
