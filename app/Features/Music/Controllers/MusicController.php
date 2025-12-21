<?php

declare(strict_types=1);

namespace App\Features\Music\Controllers;

use App\Features\Music\Actions\GetMusicsAction;
use App\Features\Music\Data\MusicData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MusicController extends Controller
{
    public function __construct(protected GetMusicsAction $get_musics_action) {}

    public function __invoke(Request $request)
    {
        $musics = $this->get_musics_action->handle($request->title ?? '');

        return response()->json([
            'data' => MusicData::collect($musics),
        ]);
    }
}
