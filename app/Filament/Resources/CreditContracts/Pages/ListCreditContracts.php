<?php

namespace App\Filament\Resources\CreditContracts\Pages;

use App\Filament\Resources\CreditContracts\CreditContractResource;
use App\Jobs\ProcessCreditPaymentImport;
use App\Models\User;
use App\Services\CreditContractExporter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCreditContracts extends ListRecords
{
    protected static string $resource = CreditContractResource::class;

    public function getTitle(): string
    {
        return 'Shartnomalar';
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('primary')
                    ->action(function () {
                        $path = app(CreditContractExporter::class)->exportAll();

                        return response()->download($path, basename($path), [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])->deleteFileAfterSend(true);
                    }),

                Action::make('shablon')
                    ->label('Shablon')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('warning')
                    ->action(function () {
                        $path = app(CreditContractExporter::class)->generatePaymentTemplate();

                        return response()->download($path, 'tolov_shabloni.xlsx', [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])->deleteFileAfterSend(true);
                    }),

                Action::make('import')
                    ->label('Import')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->color('success')
                    ->modalHeading("To'lovlarni Excel orqali yuklash")
                    ->modalDescription("Ustunlar tartibi: A=JSHSHR, B=To'langan summa. Birinchi qator sarlavha sifatida e'tiborsiz qoldiriladi.")
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

                        ProcessCreditPaymentImport::dispatch(
                            $relativePath,
                            $originalName,
                            auth()->id(),
                        );

                        Notification::make()
                            ->title('Fayl qabul qilindi')
                            ->body("To'lovlar orqa fonda yuklanmoqda. Tugagach bildirishnoma keladi.")
                            ->info()
                            ->send();
                    }),
            ])
                ->label('Excel')
                ->icon(Heroicon::OutlinedTableCells)
                ->color('success')
                ->button()
                ->visible(fn (): bool => static::canUseExcel()),

            CreateAction::make()
                ->label('Yangi kredit modul')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected static function canUseExcel(): bool
    {
        $role = auth()->user()?->role;

        return in_array($role, [User::ROLE_SUPER, User::ROLE_MARKETING], true);
    }
}
