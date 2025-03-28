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
        Schema::create('favorite_places', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('place_id', 50);
            $table->string('latitude');
            $table->string('longitude');
            $table->string('prefecture');
            $table->string('area');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('comment',500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_places');
    }
};
