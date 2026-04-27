<?php

namespace App\Filament\Resources\CreditContracts\Pages;

use App\Filament\Resources\CreditContracts\CreditContractResource;
use App\Models\CreditContract;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

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
            DeleteAction::make()->label('O\'chirish'),
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
