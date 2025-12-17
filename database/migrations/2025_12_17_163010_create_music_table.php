<?php

use App\Features\Music\Enums\MusicStatusEnum;
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
        Schema::create('music', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('artist');
            $table->string('path');
            $table->unsignedInteger('duration')->nullable()->index()->comment('in seconds');
            $table->enum('status', MusicStatusEnum::toArray());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music');
    }
};
