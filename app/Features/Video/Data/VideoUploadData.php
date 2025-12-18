<?php

declare(strict_types=1);

namespace App\Features\Video\Data;

use App\Features\Video\Enums\VideoPrivacyEnum;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class VideoUploadData extends Data
{
    public function __construct(
        public string $description,
        public string $thumbnail,
        public bool $allow_comments,
        public VideoPrivacyEnum $privacy,
        public ?UploadedFile $video = null,
        public ?array $images = null,
        public ?string $music_id = null,
        public ?string $title = null,
    ) {}

    public static function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'allow_comments' => ['required', 'boolean'],
            'privacy' => ['required', 'in:'.implode(',', VideoPrivacyEnum::toArray())],

            // VIDEO (mutually exclusive with images)
            'video' => [
                'required_without:images',
                'prohibits:images',
                'file',
                'mimetypes:video/mp4,video/quicktime',
                'max:81920', // 80MB
            ],

            // IMAGES (mutually exclusive with video)
            'images' => [
                'required_without:video',
                'prohibits:video',
                'array',
                'min:1',
            ],

            'images.*' => [
                'image',
                'max:10240', // 10MB per image
            ],

            // Required only for image posts
            'title' => [
                'required_with:images',
                'string',
                'max:255',
            ],

            // Optional
            'music_id' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'string'],
        ];
    }
}
