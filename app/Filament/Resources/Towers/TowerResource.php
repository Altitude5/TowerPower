<?php

namespace App\Filament\Resources\Towers;

use App\Filament\Resources\Towers\Pages\CreateTower;
use App\Filament\Resources\Towers\Pages\EditTower;
use App\Filament\Resources\Towers\Pages\ListTowers;
use App\Filament\Resources\Towers\Schemas\TowerForm;
use App\Filament\Resources\Towers\Tables\TowersTable;
use App\Models\Tower;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
                \Filament\Tables\Columns\TextColumn::make('name'),
                \Filament\Tables\Columns\TextColumn::make('fullAddress')->label('Address'),
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

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            \Filament\Infolists\Components\Section::make('Details')->schema([
                \Filament\Infolists\Components\TextEntry::make('id')->label('ID'),
                \Filament\Infolists\Components\TextEntry::make('name'),
                \Filament\Infolists\Components\TextEntry::make('fullAddress')->label('Address'),
                \Filament\Infolists\Components\TextEntry::make('created_at')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('updated_at')->dateTime(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTowers::route('/'),
            'create' => CreateTower::route('/create'),
            'view' => \App\Filament\Resources\Towers\Pages\ViewTower::route('/{record}'),
            'edit' => EditTower::route('/{record}/edit'),
        ];
    }
}
