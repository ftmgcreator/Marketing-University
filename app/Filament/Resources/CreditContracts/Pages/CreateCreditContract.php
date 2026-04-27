<?php

namespace App\Filament\Resources\CreditContracts\Pages;

use App\Filament\Resources\CreditContracts\CreditContractResource;
use App\Models\CreditContract;
use Filament\Resources\Pages\CreateRecord;

class CreateCreditContract extends CreateRecord
{
    protected static string $resource = CreditContractResource::class;

    public function getTitle(): string
    {
        return 'Yangi kredit modul';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $credits = (int) ($data['credits_count'] ?? 0);

        $data['contract_number'] ??= CreditContract::nextContractNumber();
        $data['contract_date'] ??= now()->toDateString();
        $data['payment_status'] ??= 'pending';
        $data['paid_amount'] ??= 0;
        $data['price_per_credit'] = CreditContract::PRICE_PER_CREDIT;
        $data['total_amount'] = $credits * CreditContract::PRICE_PER_CREDIT;

        return $data;
    }
}
