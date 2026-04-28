<?php

namespace App\Filament\Resources\Specialities\Pages;

use App\Filament\Resources\Specialities\SpecialityResource;
use App\Jobs\ProcessSpecialityImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ManageSpecialities extends ManageRecords
{
    protected static string $resource = SpecialityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Excel dan yuklash')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('success')
                ->modalHeading('Mutaxassisliklarni Excel orqali yuklash')
                ->modalDescription('Ustunlar tartibi: A=Ta\'lim turi, B=Fakultet, C=Shifr, D=Mutaxassislik, E=Ta\'lim shakli, F=Shartnoma summasi.')
                ->modalSubmitActionLabel('Yuklash')
                ->schema([
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

                    ProcessSpecialityImport::dispatch(
                        $relativePath,
                        $originalName,
                        auth()->id(),
                    );

                    Notification::make()
                        ->title('Fayl qabul qilindi')
                        ->body('Mutaxassisliklar orqa fonda yuklanmoqda. Tugagach bildirishnoma keladi.')
                        ->info()
                        ->send();
                }),

            CreateAction::make()
                ->label('Yangi mutaxassislik')
                ->modalWidth(Width::TwoExtraLarge),
        ];
    }
}
