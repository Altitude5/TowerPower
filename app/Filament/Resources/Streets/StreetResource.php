<?php

namespace App\Filament\Resources\Streets;

use App\Filament\Resources\Streets\Pages\CreateStreet;
use App\Filament\Resources\Streets\Pages\EditStreet;
use App\Filament\Resources\Streets\Pages\ListStreets;
use App\Filament\Resources\Streets\Schemas\StreetForm;
use App\Filament\Resources\Streets\Tables\StreetsTable;
use App\Models\Street;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StreetResource extends Resource
{
    protected static ?string $model = Street::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StreetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name'),
                \Filament\Tables\Columns\TextColumn::make('city.name')->label('City'),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            \Filament\Infolists\Components\Section::make('Details')->schema([
                \Filament\Infolists\Components\TextEntry::make('id')->label('ID'),
                \Filament\Infolists\Components\TextEntry::make('name'),
                \Filament\Infolists\Components\TextEntry::make('city.name')->label('City'),
                \Filament\Infolists\Components\TextEntry::make('created_at')->dateTime(),
                \Filament\Infolists\Components\TextEntry::make('updated_at')->dateTime(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStreets::route('/'),
            'create' => CreateStreet::route('/create'),
            'view' => \App\Filament\Resources\Streets\Pages\ViewStreet::route('/{record}'),
            'edit' => EditStreet::route('/{record}/edit'),
        ];
    }
}
