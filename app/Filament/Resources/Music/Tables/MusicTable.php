<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music\Tables;

use App\Features\Music\Models\Music;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;

class MusicTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('path')
                    ->label('Audio')
                    ->formatStateUsing(fn (): string => '')
                    ->icon(LucideIcon::Play)
                    ->iconColor('success')
                    ->action(
                        MediaAction::make('audio')
                            ->mediaType(MediaAction::TYPE_AUDIO)
                            ->media(fn (Music $record) => Storage::url($record->path))
                    ),
                IconColumn::make('active')
                    ->boolean(),
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
            ]);
    }
}
