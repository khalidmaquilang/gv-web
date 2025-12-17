<?php

declare(strict_types=1);

namespace App\Filament\Resources\Music;

use App\Features\Music\Models\Music;
use App\Filament\Resources\Music\Pages\CreateMusic;
use App\Filament\Resources\Music\Pages\EditMusic;
use App\Filament\Resources\Music\Pages\ListMusic;
use App\Filament\Resources\Music\Schemas\MusicForm;
use App\Filament\Resources\Music\Tables\MusicTable;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MusicResource extends Resource
{
    protected static ?string $model = Music::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Music;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MusicForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MusicTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMusic::route('/'),
            'create' => CreateMusic::route('/create'),
            'edit' => EditMusic::route('/{record}/edit'),
        ];
    }
}
