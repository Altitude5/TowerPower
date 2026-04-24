<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\TransactionStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
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
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('ILS', divideBy: 100)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TransactionStatus $state): string => match ($state) {
                        TransactionStatus::Pending => 'gray',
                        TransactionStatus::Completed => 'success',
                        TransactionStatus::Failed => 'danger',
                        TransactionStatus::Cancelled => 'danger',
                        TransactionStatus::Refunded => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('gateway')
                    ->sortable(),
                TextColumn::make('transaction_reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->toolbarActions([
                // No bulk actions allowed for transactions
            ]);
    }
}
