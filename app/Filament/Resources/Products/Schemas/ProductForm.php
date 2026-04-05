<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->minLength(3)
                    ->maxLength(120),
                Select::make('shop_id')
                    ->relationship('shop', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Price in smallest currency unit (e.g. agorot). 8000 = 80.00 ILS'),
                Select::make('price_type')
                    ->options([
                        'Unit' => 'Unit (ILS)',
                        'Weight' => 'Weight (ILS/Kg)',
                        'Volume' => 'Volume (ILS/Litre)',
                    ])
                    ->required(),
                FileUpload::make('image_path')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                    ->maxSize(5120)
                    ->visibility('public'),
                TextInput::make('sku')
                    ->label('SKU')
                    ->nullable()
                    ->regex('/^[A-Z0-9-]{8,12}$/')
                    ->helperText('Uppercase alphanumeric with dashes, 8–12 characters'),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->placeholder('No category'),
                TextInput::make('stock_quantity')
                    ->numeric()
                    ->nullable()
                    ->minValue(0),
                TextInput::make('stock_weight')
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->helperText('In kilograms'),
                TextInput::make('stock_volume')
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->helperText('In litres'),
                Toggle::make('available')
                    ->default(true),
            ]);
    }
}
