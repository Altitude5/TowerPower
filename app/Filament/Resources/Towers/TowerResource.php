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
        return TowersTable::configure($table);
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
            'index' => ListTowers::route('/'),
            'create' => CreateTower::route('/create'),
            'edit' => EditTower::route('/{record}/edit'),
        ];
    }
}
