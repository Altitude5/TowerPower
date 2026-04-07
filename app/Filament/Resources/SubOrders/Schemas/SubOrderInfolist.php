<?php

namespace App\Filament\Resources\SubOrders\Schemas;

use App\Enums\SubOrderStatus;
use App\Models\SubOrder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SubOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sub-Order Information')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Sub-Order ID'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (SubOrderStatus $state): string => match ($state) {
                                SubOrderStatus::Pending => 'gray',
                                SubOrderStatus::Processing => 'info',
                                SubOrderStatus::OutForDelivery => 'warning',
                                SubOrderStatus::Delivered => 'success',
                                SubOrderStatus::Completed => 'success',
                                SubOrderStatus::Cancelled => 'danger',
                                SubOrderStatus::Returned => 'danger',
                            }),
                        TextEntry::make('shop.name')
                            ->label('Shop'),
                        TextEntry::make('order.id')
                            ->label('Order ID'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ])->columns(2),

                Section::make('Financial Summary')
                    ->schema([
                        TextEntry::make('total_price_amount')
                            ->label('Subtotal')
                            ->state(fn (SubOrder $record): int => $record->totalPrice())
                            ->money('ILS', divideBy: 100),
                        TextEntry::make('total_discount_amount')
                            ->label('Discount')
                            ->state(fn (SubOrder $record): int => $record->totalDiscount())
                            ->money('ILS', divideBy: 100),
                        TextEntry::make('total_tax_amount')
                            ->label('Tax')
                            ->state(fn (SubOrder $record): int => $record->totalTax())
                            ->money('ILS', divideBy: 100),
                        TextEntry::make('total_final_price_amount')
                            ->label('Total')
                            ->state(fn (SubOrder $record): int => $record->totalFinalPrice())
                            ->money('ILS', divideBy: 100)
                            ->weight('bold'),
                    ])->columns(4),
            ]);
    }
}
