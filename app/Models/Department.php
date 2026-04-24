<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'report_id', 'faculty_id', 'name', 'slug',
    'student_count', 'paid_count', 'debt_count',
    'contract_amount', 'paid_amount', 'debt_amount', 'percent_paid',
])]
class Department extends Model
{
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function curators(): HasMany
    {
        return $this->hasMany(Curator::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}
