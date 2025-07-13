<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('gmaps', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('title', 25);
        $table->string('comment', 255);
        $table->double('latitude');
        $table->double('longitude');
        $table->string('picture_path');
        $table->string('magic_word')->nullable();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gmaps');
    }
};
