<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->disabled(),
                Select::make('status')
                    ->options(TransactionStatus::class)
                    ->required()
                    ->disabled(),
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->disabled(),
                TextInput::make('currency')
                    ->required()
                    ->disabled(),
                TextInput::make('gateway')
                    ->required()
                    ->disabled(),
                TextInput::make('transaction_reference')
                    ->disabled(),
            ]);
    }
}
