<?php

namespace App\Filament\Resources\SubOrders\Pages;

use App\Filament\Resources\SubOrders\SubOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSubOrder extends ViewRecord
{
    protected static string $resource = SubOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
