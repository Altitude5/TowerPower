<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tower.name')
                    ->label('Tower')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (OrderStatus $state): string => match ($state) {
                        OrderStatus::Pending => 'gray',
                        OrderStatus::Processing => 'info',
                        OrderStatus::Completed => 'success',
                        OrderStatus::Cancelled => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('total_final_price_amount')
                    ->label('Total')
                    ->state(fn (Order $record): int => $record->totalFinalPrice())
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()->isSuperUser()),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()->isSuperUser()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isSuperUser()),
                ]),
            ]);
    }
}
