<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                Select::make('delivery_person_id')
                    ->relationship('deliveryPerson', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('type')
                    ->options([
                        'positive' => 'Positive (Will visit)',
                        'negative' => 'Negative (Will NOT visit / Block)',
                    ])
                    ->required(),
                Select::make('recurrence')
                    ->options([
                        'one_time' => 'One-time',
                        'daily' => 'Daily',
                        'weekdays_sunday' => 'Sun-Thu (Israeli week)',
                        'weekdays_monday' => 'Mon-Fri (Western week)',
                        'weekly_single_day' => 'Weekly (specific day)',
                        'weekend_friday' => 'Fri + Sat',
                        'weekend_saturday' => 'Sat + Sun',
                    ])
                    ->required()
                    ->live(),
                Select::make('day_of_week')
                    ->options([
                        0 => 'Sunday',
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                    ])
                    ->required(fn (Get $get): bool => $get('recurrence') === 'weekly_single_day')
                    ->visible(fn (Get $get): bool => $get('recurrence') === 'weekly_single_day'),
                DatePicker::make('date')
                    ->required(fn (Get $get): bool => $get('recurrence') === 'one_time')
                    ->visible(fn (Get $get): bool => $get('recurrence') === 'one_time'),
            ]);
    }
}
