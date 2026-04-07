<?php

namespace App\Filament\Resources\SubOrders\RelationManagers;

use App\Models\OrderItem;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('ILS', divideBy: 100),
                TextColumn::make('price_type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->visible(fn ($record) => $record?->quantity !== null),
                TextColumn::make('weight')
                    ->label('Weight')
                    ->visible(fn ($record) => $record?->weight !== null),
                TextColumn::make('volume')
                    ->label('Volume')
                    ->visible(fn ($record) => $record?->volume !== null),
                TextColumn::make('total_price_amount')
                    ->label('Total')
                    ->state(fn (OrderItem $record): int => $record->totalPrice())
                    ->money('ILS', divideBy: 100),
            ])
            ->filters([
                //
            ]);
    }
}
