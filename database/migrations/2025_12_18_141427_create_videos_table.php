<?php

use App\Features\Video\Enums\VideoPrivacyEnum;
use App\Features\Video\Enums\VideoStatusEnum;
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
        Schema::create('videos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('music_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title')->nullable()->comment('for photos');
            $table->text('description');
            $table->string('thumbnail');
            $table->string('video_path')->nullable();
            $table->json('images')->nullable();
            $table->boolean('allow_comments')->default(true);
            $table->enum('privacy', VideoPrivacyEnum::toArray());
            $table->enum('status', VideoStatusEnum::toArray());
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
