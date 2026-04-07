<?php

namespace App\Filament\Resources\SubOrders\Pages;

use App\Filament\Resources\SubOrders\SubOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSubOrder extends EditRecord
{
    protected static string $resource = SubOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
