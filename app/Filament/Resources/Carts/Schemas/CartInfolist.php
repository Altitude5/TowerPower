<?php

namespace App\Filament\Resources\Carts\Schemas;

use App\Models\CartItem;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CartInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cart Information')->schema([
                    TextEntry::make('id')
                        ->label('ID'),
                    TextEntry::make('user.name')
                        ->label('User'),
                    TextEntry::make('tower_id')
                        ->label('Tower ID')
                        ->placeholder('-'),
                    TextEntry::make('created_at')
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->dateTime(),
                ])->columns(2),

                RepeatableEntry::make('items')
                    ->label('Cart Items')
                    ->schema([
                        TextEntry::make('product_name')
                            ->label('Product'),
                        TextEntry::make('quantity')
                            ->placeholder('-'),
                        TextEntry::make('weight')
                            ->placeholder('-'),
                        TextEntry::make('volume')
                            ->placeholder('-'),
                        TextEntry::make('price')
                            ->formatStateUsing(fn (int $state): string => number_format($state / 100, 2).' ILS'),
                        TextEntry::make('price_type')
                            ->label('Type'),
                        TextEntry::make('total_price')
                            ->label('Total')
                            ->state(fn (CartItem $record): string => number_format($record->totalPrice() / 100, 2).' ILS')
                            ->weight('bold'),
                    ])->columns(7),
            ]);
    }
}
