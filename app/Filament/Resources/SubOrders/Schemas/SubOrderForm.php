<?php

namespace App\Filament\Resources\SubOrders\Schemas;

use App\Enums\SubOrderStatus;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SubOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options(SubOrderStatus::class)
                    ->required()
                    ->disabled(fn () => ! auth()->user()->isSuperUser()),
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->required()
                    ->disabled(),
                Select::make('shop_id')
                    ->relationship('shop', 'name')
                    ->required()
                    ->disabled(),
            ]);
    }
}
