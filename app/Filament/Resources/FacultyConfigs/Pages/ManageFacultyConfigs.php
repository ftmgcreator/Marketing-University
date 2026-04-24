<?php

namespace App\Filament\Resources\FacultyConfigs\Pages;

use App\Filament\Resources\FacultyConfigs\FacultyConfigResource;
use App\Models\FacultyConfig;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageFacultyConfigs extends ManageRecords
{
    protected static string $resource = FacultyConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Aktiv hisobotdan sinxronlash')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (): void {
                    $report = Report::where('is_active', true)->orderByDesc('report_date')->first();
                    if (! $report) {
                        Notification::make()->title('Aktiv hisobot yo\'q')->warning()->send();
                        return;
                    }

                    $created = 0;
                    foreach ($report->faculties()->pluck('name') as $name) {
                        if (! FacultyConfig::where('name', $name)->exists()) {
                            FacultyConfig::create(['name' => $name, 'is_active' => true]);
                            $created++;
                        }
                    }

                    Notification::make()
                        ->title('Sinxronlandi')
                        ->body("Yangi: {$created} ta fakultet qo'shildi")
                        ->success()
                        ->send();
                }),

            CreateAction::make()->label('Yangi qo\'shish'),
        ];
    }
}
