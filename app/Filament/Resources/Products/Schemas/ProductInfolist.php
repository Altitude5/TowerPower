<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('shop.name')
                    ->label('Shop'),
                TextEntry::make('price')
                    ->formatStateUsing(fn (int $state): string => number_format($state / 100, 2).' ILS'),
                TextEntry::make('price_type')
                    ->label('Price Type'),
                TextEntry::make('priceUnit')
                    ->label('Price Unit')
                    ->state(fn (Product $record): string => $record->priceUnit()),
                ImageEntry::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->visibility('public')
                    ->placeholder('No image'),
                TextEntry::make('sku')
                    ->label('SKU')
                    ->placeholder('-'),
                TextEntry::make('category.name')
                    ->label('Category')
                    ->placeholder('No category'),
                TextEntry::make('stock_quantity')
                    ->placeholder('-'),
                TextEntry::make('stock_weight')
                    ->placeholder('-'),
                TextEntry::make('stock_volume')
                    ->placeholder('-'),
                IconEntry::make('available')
                    ->boolean(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Product $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
