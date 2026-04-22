<?php

namespace App\Filament\Resources\Towers;

use App\Filament\Resources\Towers\Pages\CreateTower;
use App\Filament\Resources\Towers\Pages\EditTower;
use App\Filament\Resources\Towers\Pages\ListTowers;
use App\Filament\Resources\Towers\Pages\ViewTower;
use App\Filament\Resources\Towers\Schemas\TowerForm;
use App\Models\Tower;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TowerResource extends Resource
{
    protected static ?string $model = Tower::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TowerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('full_address')
                    ->label('Address')
                    ->state(fn (Tower $record): string => $record->full_address()),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Details')->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('name'),
                TextEntry::make('full_address')
                    ->label('Address')
                    ->state(fn (Tower $record): string => $record->full_address()),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTowers::route('/'),
            'create' => CreateTower::route('/create'),
            'view' => ViewTower::route('/{record}'),
            'edit' => EditTower::route('/{record}/edit'),
        ];
    }
}
