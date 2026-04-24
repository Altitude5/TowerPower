<?php

namespace App\Filament\Resources\Deliveries\Schemas;

use App\Enums\DeliveryStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class DeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sub_order_id')
                    ->relationship('subOrder', 'id')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('schedule_id')
                    ->relationship('schedule', 'id')
                    ->searchable()
                    ->preload(),
                Select::make('delivery_person_id')
                    ->relationship('deliveryPerson', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('tower_id')
                    ->relationship('tower', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('shop_id')
                    ->relationship('shop', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('city_id')
                    ->relationship('city', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                DatePicker::make('date')
                    ->required(),
                TimePicker::make('time'),
                Select::make('status')
                    ->options(DeliveryStatus::class)
                    ->required(),
                Select::make('cancelled_by_user_id')
                    ->relationship('cancelledBy', 'name')
                    ->searchable()
                    ->preload(),
                FileUpload::make('image_url')
                    ->image()
                    ->disk('public')
                    ->directory('deliveries'),
            ]);
    }
}
