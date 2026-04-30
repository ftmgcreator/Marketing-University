<?php

namespace App\Filament\Resources\Admissions\Pages;

use App\Filament\Resources\Admissions\AdmissionResource;
use App\Models\Admission;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmission extends CreateRecord
{
    protected static string $resource = AdmissionResource::class;

    public function getTitle(): string
    {
        return 'Yangi talaba qo\'shish';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] ??= Admission::STATUS_PENDING;
        $data['admission_date'] ??= now()->toDateString();

        return $data;
    }
}
