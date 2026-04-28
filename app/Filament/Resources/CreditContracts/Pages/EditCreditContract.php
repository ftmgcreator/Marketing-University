<?php

namespace App\Filament\Resources\CreditContracts\Pages;

use App\Filament\Resources\CreditContracts\CreditContractResource;
use App\Models\CreditContract;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCreditContract extends EditRecord
{
    protected static string $resource = CreditContractResource::class;

    public function getTitle(): string
    {
        return 'Shartnomani tahrirlash';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('PDF yuklab olish')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('success')
                ->action(function () {
                    /** @var CreditContract $record */
                    $record = $this->getRecord();

                    $pdf = Pdf::loadView('pdf.credit_contract', ['contract' => $record])
                        ->setPaper('a4');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        "shartnoma-{$record->contract_number}.pdf",
                        ['Content-Type' => 'application/pdf']
                    );
                }),

            DeleteAction::make()
                ->label('O\'chirish')
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() === true),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $credits = (int) ($data['credits_count'] ?? 0);

        $data['price_per_credit'] = CreditContract::PRICE_PER_CREDIT;
        $data['total_amount'] = $credits * CreditContract::PRICE_PER_CREDIT;

        return $data;
    }
}
