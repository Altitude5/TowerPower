<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction Details')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Transaction ID'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (TransactionStatus $state): string => match ($state) {
                                TransactionStatus::Pending => 'gray',
                                TransactionStatus::Completed => 'success',
                                TransactionStatus::Failed => 'danger',
                                TransactionStatus::Cancelled => 'danger',
                                TransactionStatus::Refunded => 'warning',
                            }),
                        TextEntry::make('amount')
                            ->money('ILS', divideBy: 100),
                        TextEntry::make('currency'),
                        TextEntry::make('gateway'),
                        TextEntry::make('transaction_reference')
                            ->label('Reference')
                            ->placeholder('Pending gateway assignment...'),
                        TextEntry::make('order.id')
                            ->label('Order ID'),
                        TextEntry::make('customer.name')
                            ->label('Customer'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }
}
