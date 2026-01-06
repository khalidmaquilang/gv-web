<?php

declare(strict_types=1);

namespace App\Features\Webhook\Controllers;

use App\Features\Webhook\Data\FfmpegData;
use App\Features\Webhook\Enums\WebhookEnum;
use App\Features\Webhook\Models\Interfaces\FfmpegInterface;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Log;

class WebhookFfmpegController extends Controller
{
    public function __invoke(FfmpegData $request): JsonResponse
    {
        /** @var Model $model */
        $model = app($request->model_type);
        if (! $model instanceof FfmpegInterface) {
            Log::error('This model does not have FfmpegInterface', $request->toArray());

            return response()->json(['message' => 'error'], 400);
        }

        if ($request->status === WebhookEnum::Error) {
            Log::error($request->message, $request->toArray());

            return response()->json(['message' => 'error'], 400);
        }

        $model::updateMediaStatus($request->model_id, $request->status, $request->duration, $request->path, $request->thumbnail_path);

        return response()->json(['message' => 'ok']);
    }
}
