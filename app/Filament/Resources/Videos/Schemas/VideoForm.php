<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Schemas;

use App\Features\Feed\Enums\FeedPrivacyEnum;
use App\Features\Music\Models\Music;
use App\Features\Video\Models\Video;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        $user_id = auth()->id();
        abort_if($user_id === null, 404);

        return $schema
            ->components([
                FileUpload::make('video_path')
                    ->directory(Video::getVideoPath($user_id))
                    ->acceptedFileTypes([
                        'video/mp4',
                        'video/quicktime',
                    ])
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
                Group::make([
                    Select::make('privacy')
                        ->options(FeedPrivacyEnum::class)
                        ->default(FeedPrivacyEnum::PublicView)
                        ->required(),
                    Toggle::make('allow_comments')
                        ->default(true),
                    Textarea::make('title')
                        ->label('Description')
                        ->required()
                        ->columnSpanFull(),
                ])
                    ->relationship('feed')
                    ->columnSpanFull(),
            ]);
    }
}
