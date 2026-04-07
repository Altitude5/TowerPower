<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\SubOrderStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'subOrders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('shop.name')
                    ->label('Shop')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (SubOrderStatus $state): string => match ($state) {
                        SubOrderStatus::Pending => 'gray',
                        SubOrderStatus::Processing => 'info',
                        SubOrderStatus::OutForDelivery => 'warning',
                        SubOrderStatus::Delivered => 'success',
                        SubOrderStatus::Completed => 'success',
                        SubOrderStatus::Cancelled => 'danger',
                        SubOrderStatus::Returned => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('totalFinalPrice')
                    ->label('Total')
                    ->money('ILS', divideBy: 100),
            ])
            ->filters([
                //
            ]);
    }
}
