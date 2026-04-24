<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'report_id', 'group_id', 'full_name', 'group_name',
    'faculty', 'speciality', 'course', 'education_form', 'contract_type',
    'previous_year_amount', 'contract_amount', 'paid_amount', 'debt_amount',
    'percent_paid', 'is_debtor',
])]
class Student extends Model
{
    protected function casts(): array
    {
        return [
            'is_debtor' => 'boolean',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
