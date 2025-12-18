<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music\Schemas;

use App\Filament\Components\Fields\TextInput\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
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
                Toggle::make('active')
                    ->default(true),
                FileUpload::make('path')
                    ->label('Audio File')
                    ->directory('musics')
                    ->acceptedFileTypes([
                        // MP3
                        'audio/mpeg',
                        'audio/mp3',
                        // WAV (The culprit is usually one of these three)
                        'audio/wav',
                        'audio/x-wav',
                        'audio/vnd.wav',
                        // Others
                        'audio/ogg',
                        'audio/x-m4a',
                        'audio/aac',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
