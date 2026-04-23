<?php

namespace App\Filament\Resources\SubOrders\Tables;

use App\Enums\SubOrderStatus;
use App\Models\SubOrder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('order.id')
                    ->label('Order ID')
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
                TextColumn::make('total_final_price_amount')
                    ->label('Total')
                    ->state(fn (SubOrder $record): int => $record->totalFinalPrice())
                    ->money('ILS', divideBy: 100)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()->isSuperUser()),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()->isSuperUser()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isSuperUser()),
                ]),
            ]);
    }
}
