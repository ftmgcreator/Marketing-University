<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Services\ReportImporter;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ManageReports extends ManageRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Excel yuklash')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('primary')
                ->modalHeading('Yangi hisobot yuklash')
                ->modalSubmitActionLabel('Yuklash')
                ->schema([
                    DatePicker::make('report_date')
                        ->label('Hisobot sanasi')
                        ->required()
                        ->default(now())
                        ->native(false),

                    FileUpload::make('file')
                        ->label('Excel fayl (.xlsx)')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->disk('local')
                        ->directory('imports')
                        ->maxSize(50 * 1024)
                        ->storeFileNamesIn('original_name'),
                ])
                ->action(function (array $data): void {
                    $relativePath = is_array($data['file']) ? reset($data['file']) : $data['file'];
                    $absolutePath = Storage::disk('local')->path($relativePath);
                    $originalName = $data['original_name'][$relativePath] ?? basename($relativePath);

                    try {
                        $importer = new ReportImporter();
                        $report = $importer->import($absolutePath, $originalName, new \DateTimeImmutable($data['report_date']));

                        Notification::make()
                            ->title('Hisobot muvaffaqiyatli yuklandi')
                            ->body(sprintf(
                                '%d kafedra, %d guruh, %d talaba',
                                $report->departments()->count(),
                                $report->groups()->count(),
                                $report->students()->count(),
                            ))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Yuklashda xatolik')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($relativePath);
                    }
                }),
        ];
    }
}
