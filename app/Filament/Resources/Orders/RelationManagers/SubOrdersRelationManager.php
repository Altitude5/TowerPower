<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\SubOrderStatus;
use App\Filament\Resources\SubOrders\Schemas\SubOrderInfolist;
use App\Models\SubOrder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'subOrders';

    public function infolist(Schema $schema): Schema
    {
        return SubOrderInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('Sub-Order ID')
                    ->searchable(),
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
                TextColumn::make('total_final_price')
                    ->label('Total')
                    ->state(fn (SubOrder $record): int => $record->totalFinalPrice())
                    ->money('ILS', divideBy: 100),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
