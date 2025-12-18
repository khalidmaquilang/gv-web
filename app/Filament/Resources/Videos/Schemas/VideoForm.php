<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Schemas;

use App\Features\Music\Models\Music;
use App\Features\Video\Enums\VideoPrivacyEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('video_path')
                    ->directory('video/')
                    ->columnSpanFull(),
                Select::make('music_id')
                    ->live()
                    ->relationship('music', 'name')
                    ->suffixAction(
                        MediaAction::make('audio')
                            ->icon(LucideIcon::Play)
                            ->color('success')
                            ->mediaType(MediaAction::TYPE_AUDIO)
                            ->media(function (Get $get) {
                                $music = Music::find($get('music_id') ?? '');
                                if (! $music) {
                                    return '';
                                }

                                return Storage::url($music->path);
                            })
                            ->visible(fn (Get $get): bool => $get('music_id') !== null)
                    ),
                Select::make('privacy')
                    ->options(VideoPrivacyEnum::class)
                    ->default(VideoPrivacyEnum::PublicView)
                    ->required(),
                Toggle::make('allow_comments')
                    ->default(true),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('thumbnail')
                    ->directory('video/')
                    ->image()
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
