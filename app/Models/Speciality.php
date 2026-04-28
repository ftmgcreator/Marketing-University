<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'education_type',
    'faculty',
    'code',
    'name',
    'education_form',
    'contract_amount',
    'is_active',
])]
class Speciality extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'contract_amount' => 'integer',
        ];
    }
}
