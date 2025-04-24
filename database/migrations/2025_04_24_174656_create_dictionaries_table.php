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
        Schema::create('dictionaries', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->string('definition')->nullable();
            $table->string('lang')->nullable();
            $table->timestamps();

            $table->index(['word', 'lang'], 'word_lang_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictionaries');
    }
};
