<?php

declare(strict_types=1);

namespace App\Features\User\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\Image;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UploadProfileImageData extends Data
{
    public function __construct(
        #[Required]
        #[Image]
        #[Max(10240)] // 10MB in kilobytes
        public UploadedFile $image,
    ) {}
}
