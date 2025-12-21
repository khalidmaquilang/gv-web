<?php

declare(strict_types=1);

namespace App\Features\Shared\Filament\Traits;

trait RedirectTrait
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
