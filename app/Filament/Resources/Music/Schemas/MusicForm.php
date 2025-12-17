<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music\Schemas;

use App\Filament\Components\Fields\TextInput\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class MusicForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('artist')
                    ->required(),
                FileUpload::make('path')
                    ->label('Audio File')
                    ->directory('musics')
                    ->acceptedFileTypes([
                        'audio/mpeg',
                        'audio/wav',
                        'audio/ogg',
                        'audio/x-m4a',
                        'audio/aac',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
