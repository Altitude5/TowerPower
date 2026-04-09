<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class GeoImporter extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.geo-importer';
    protected static ?string $navigationLabel = 'Geo Importer';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('file')
                ->label('CSV File')
                ->required()
                ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                ->directory('geo-imports'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('import')
                ->label('Import')
                ->action('import'),
        ];
    }

    public function import(): void
    {
        $file = $this->data['file'];
        $path = Storage::path($file);

        try {
            Artisan::call('geo:import', ['file' => $path]);
            Notification::make()
                ->title('Import successful')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Import failed')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    public static function canAccess(): bool
    {
        return auth()->user()->isSuperUser();
    }
}
