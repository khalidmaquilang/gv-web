<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Tables;

use App\Features\Video\Models\Video;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;

class VideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('music.name'),
                TextColumn::make('title'),
                TextColumn::make('description')
                    ->lineClamp(2)
                    ->wrap(),
                TextColumn::make('video_path')
                    ->label('Video')
                    ->alignCenter()
                    ->formatStateUsing(fn (): string => '')
                    ->icon(LucideIcon::Play)
                    ->iconColor('success')
                    ->action(
                        MediaAction::make('video')
                            ->mediaType(MediaAction::TYPE_VIDEO)
                            ->media(fn (Video $record) => Storage::url($record->video_path))
                    ),
                TextColumn::make('privacy')
                    ->badge(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('views'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
