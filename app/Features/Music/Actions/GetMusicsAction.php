<?php

declare(strict_types=1);

namespace App\Features\Music\Actions;

use App\Features\Music\Models\Music;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GetMusicsAction
{
    public function handle(string $title = ''): LengthAwarePaginator
    {
        return Music::query()
            ->published()
            ->when(fn (Builder $query) => $query->where('name', 'like', sprintf('%%%s%%', $title)))
            ->paginate(10);
    }
}
