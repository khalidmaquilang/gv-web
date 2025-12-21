<?php

declare(strict_types=1);

namespace App\Filament\Components\Fields\TextInput;

use Closure;
use Filament\Forms\Components\TextInput as BaseTextInput;

class TextInput extends BaseTextInput
{
    protected int|Closure|null $maxLength = 255;
}
