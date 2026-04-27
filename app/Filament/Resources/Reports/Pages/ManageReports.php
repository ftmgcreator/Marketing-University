<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Jobs\ProcessReportImport;
use App\Models\ImportJob;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

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
                    $originalName = $data['original_name'][$relativePath] ?? basename($relativePath);

                    $job = ImportJob::create([
                        'user_id' => auth()->id(),
                        'original_name' => $originalName,
                        'file_path' => $relativePath,
                        'report_date' => $data['report_date'],
                        'status' => ImportJob::STATUS_PENDING,
                    ]);

                    ProcessReportImport::dispatch($job->id);

                    Notification::make()
                        ->title('Fayl qabul qilindi')
                        ->body('Hisobot orqa fonda yuklanmoqda. Tugagach bildirishnoma keladi.')
                        ->info()
                        ->send();
                }),
        ];
    }
}
