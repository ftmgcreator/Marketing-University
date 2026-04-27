<?php

namespace App\Filament\Resources\CreditContracts\Pages;

use App\Filament\Resources\CreditContracts\CreditContractResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

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
            CreateAction::make()
                ->label('Yangi kredit modul')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
