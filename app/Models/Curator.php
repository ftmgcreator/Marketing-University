<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'report_id', 'department_id', 'full_name', 'slug',
    'group_count', 'student_count', 'paid_count', 'debt_count',
    'contract_amount', 'paid_amount', 'debt_amount', 'percent_paid',
])]
class Curator extends Model
{
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}
