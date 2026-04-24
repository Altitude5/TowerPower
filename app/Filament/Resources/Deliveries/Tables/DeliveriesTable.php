<?php

namespace App\Filament\Resources\Deliveries\Tables;

use App\Enums\DeliveryStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subOrder.id')
                    ->label('SubOrder')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tower.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shop.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deliveryPerson.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('time')
                    ->time()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (DeliveryStatus $state): string => match ($state) {
                        DeliveryStatus::Scheduled => 'gray',
                        DeliveryStatus::Departed => 'info',
                        DeliveryStatus::Completed => 'success',
                        DeliveryStatus::Failed => 'danger',
                        DeliveryStatus::Cancelled => 'danger',
                    })
                    ->searchable(),
                TextColumn::make('cancelledBy.name')
                    ->label('Cancelled By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('image_url')
                    ->label('Proof')
                    ->disk('public')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
