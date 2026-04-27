<?php

namespace App\Filament\Resources\Specialities\Pages;

use App\Filament\Resources\Specialities\SpecialityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageSpecialities extends ManageRecords
{
    protected static string $resource = SpecialityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Yangi mutaxassislik')
                ->modalWidth(Width::Large),
        ];
    }
}
