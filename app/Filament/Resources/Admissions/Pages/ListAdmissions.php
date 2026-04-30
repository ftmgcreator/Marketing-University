<?php

namespace App\Filament\Resources\Admissions\Pages;

use App\Filament\Resources\Admissions\AdmissionResource;
use App\Jobs\ProcessAdmissionImport;
use App\Models\User;
use App\Services\AdmissionExporter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListAdmissions extends ListRecords
{
    protected static string $resource = AdmissionResource::class;

    public function getTitle(): string
    {
        return 'Talabalarning ro\'yxati';
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
                        $path = app(AdmissionExporter::class)->exportAll();

                        return response()->download($path, basename($path), [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])->deleteFileAfterSend(true);
                    }),

                Action::make('shablon')
                    ->label('Shablon')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('warning')
                    ->action(function () {
                        $path = app(AdmissionExporter::class)->generateTemplate();

                        return response()->download($path, 'talabalar_shabloni.xlsx', [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])->deleteFileAfterSend(true);
                    }),

                Action::make('import')
                    ->label('Excel orqali import')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->color('success')
                    ->modalHeading('Talabalarni Excel orqali yuklash')
                    ->modalDescription("\"Qabul\" varagʻining 1-qatori sarlavha sifatida o‘tkazib yuboriladi. Ustunlar tartibi: B=F.I.SH, C=JSHSHIR, D=Pasport, E=Telefon, F=Mut.kodi, G=Mut.nomi, H=Ta'lim turi, I=Qabul statusi, J=Ta'lim shakli, K=Kursi, L=Yashash hududi, M=Tel 2, N=Shartnoma summasi, O=IZOH, P=Fakultet, Q=Sana.")
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

                        ProcessAdmissionImport::dispatch(
                            $relativePath,
                            $originalName,
                            auth()->id(),
                        );

                        Notification::make()
                            ->title('Fayl qabul qilindi')
                            ->body('Talabalar orqa fonda yuklanmoqda. Tugagach bildirishnoma keladi.')
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
                ->label('Yangi talaba qo\'shish')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected static function canUseExcel(): bool
    {
        $role = auth()->user()?->role;

        return in_array($role, [User::ROLE_SUPER, User::ROLE_MARKETING, User::ROLE_ZAMDEKAN], true);
    }
}
