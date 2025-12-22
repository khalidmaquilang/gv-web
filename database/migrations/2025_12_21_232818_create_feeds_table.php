<?php

use App\Features\Video\Enums\FeedPrivacyEnum;
use App\Features\Video\Enums\FeedStatusEnum;
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
        Schema::create('feeds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->uuidMorphs('content');
            $table->text('title')->nullable();
            $table->boolean('allow_comments')->default(true);
            $table->enum('privacy', FeedPrivacyEnum::toArray());
            $table->enum('status', FeedStatusEnum::toArray());
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
