<?php

namespace App\Filament\Resources\Carts\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cart Information')->schema([
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->required()
                        ->disabledOn('edit'),
                    TextInput::make('tower_id')
                        ->label('Tower ID')
                        ->numeric(),
                ])->columns(2),

                Section::make('Items')->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('product_id')
                                ->relationship('product', 'name')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function (Get $get, Set $set, ?int $state) {
                                    if (! $state) {
                                        return;
                                    }
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('product_name', $product->name);
                                        $set('price', $product->price);
                                        $set('price_type', $product->price_type);
                                    }
                                }),
                            TextInput::make('product_name')
                                ->required()
                                ->label('Snapshot Name'),
                            TextInput::make('price')
                                ->numeric()
                                ->required()
                                ->label('Snapshot Price (cents)')
                                ->suffix('ILS cents'),
                            Select::make('price_type')
                                ->options([
                                    'Unit' => 'Unit',
                                    'Weight' => 'Weight',
                                    'Volume' => 'Volume',
                                ])
                                ->required(),
                            TextInput::make('quantity')
                                ->numeric()
                                ->step(0.001),
                            TextInput::make('weight')
                                ->numeric()
                                ->step(0.001),
                            TextInput::make('volume')
                                ->numeric()
                                ->step(0.001),
                        ])
                        ->columns(4)
                        ->defaultItems(0),
                ]),
            ]);
    }
}
