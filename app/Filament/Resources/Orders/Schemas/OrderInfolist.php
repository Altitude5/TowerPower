<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Order ID'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (OrderStatus $state): string => match ($state) {
                                OrderStatus::Pending => 'gray',
                                OrderStatus::Processing => 'info',
                                OrderStatus::Completed => 'success',
                                OrderStatus::Cancelled => 'danger',
                            }),
                        TextEntry::make('user.name')
                            ->label('Customer'),
                        TextEntry::make('tower.name')
                            ->label('Tower'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ])->columns(2),

                Section::make('Financial Summary')
                    ->schema([
                        TextEntry::make('total_price_amount')
                            ->label('Subtotal')
                            ->state(fn (Order $record): int => $record->totalPrice())
                            ->money('ILS', divideBy: 100),
                        TextEntry::make('total_discount_amount')
                            ->label('Discount')
                            ->state(fn (Order $record): int => $record->totalDiscount())
                            ->money('ILS', divideBy: 100),
                        TextEntry::make('total_tax_amount')
                            ->label('Tax')
                            ->state(fn (Order $record): int => $record->totalTax())
                            ->money('ILS', divideBy: 100),
                        TextEntry::make('total_final_price_amount')
                            ->label('Total')
                            ->state(fn (Order $record): int => $record->totalFinalPrice())
                            ->money('ILS', divideBy: 100)
                            ->weight('bold'),
                    ])->columns(4),
            ]);
    }
}
