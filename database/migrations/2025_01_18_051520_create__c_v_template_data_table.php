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
        Schema::create('template', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unique_cv_id');
            $table->string('price');
            $table->string('template-link')->default('null');
            $table->string('template-preview')->default('null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template');
    }
};
