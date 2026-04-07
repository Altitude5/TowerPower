<?php

namespace App\Filament\Resources\SubOrders;

use App\Filament\Resources\SubOrders\Pages\CreateSubOrder;
use App\Filament\Resources\SubOrders\Pages\EditSubOrder;
use App\Filament\Resources\SubOrders\Pages\ListSubOrders;
use App\Filament\Resources\SubOrders\Pages\ViewSubOrder;
use App\Filament\Resources\SubOrders\Schemas\SubOrderForm;
use App\Filament\Resources\SubOrders\Schemas\SubOrderInfolist;
use App\Filament\Resources\SubOrders\Tables\SubOrdersTable;
use App\Models\SubOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubOrderResource extends Resource
{
    protected static ?string $model = SubOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SubOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubOrdersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && auth()->user()->isCustomer()) {
            $query->whereHas('order', fn ($q) => $q->where('user_id', auth()->id()));
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubOrders::route('/'),
            'create' => CreateSubOrder::route('/create'),
            'view' => ViewSubOrder::route('/{record}'),
            'edit' => EditSubOrder::route('/{record}/edit'),
        ];
    }
}
