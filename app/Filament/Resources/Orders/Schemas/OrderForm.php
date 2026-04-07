<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->required()
                    ->disabled(fn () => ! auth()->user()->isSuperUser()),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->disabled(),
                Select::make('tower_id')
                    ->relationship('tower', 'name')
                    ->required()
                    ->disabled(),
            ]);
    }
}
