<?php

declare(strict_types=1);

namespace App\Features\Video\Controllers;

use App\Features\Video\Data\VideoUploadData;
use App\Http\Controllers\Controller;

class VideoUploadController extends Controller
{
    public function __invoke(VideoUploadData $request) {}
}
