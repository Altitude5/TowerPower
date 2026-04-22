<?php

namespace App\Filament\Resources\Streets;

use App\Filament\Resources\Streets\Pages\CreateStreet;
use App\Filament\Resources\Streets\Pages\EditStreet;
use App\Filament\Resources\Streets\Pages\ListStreets;
use App\Filament\Resources\Streets\Pages\ViewStreet;
use App\Filament\Resources\Streets\Schemas\StreetForm;
use App\Models\Street;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('name'),
                TextColumn::make('city.name')->label('City'),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Details')->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('name'),
                TextEntry::make('city.name')->label('City'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStreets::route('/'),
            'create' => CreateStreet::route('/create'),
            'view' => ViewStreet::route('/{record}'),
            'edit' => EditStreet::route('/{record}/edit'),
        ];
    }
}
