<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable([
    'report_id', 'name', 'slug',
    'department_count', 'curator_count', 'group_count',
    'student_count', 'paid_count', 'debt_count',
    'contract_amount', 'paid_amount', 'debt_amount', 'percent_paid',
])]
class Faculty extends Model
{
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function curators(): HasManyThrough
    {
        return $this->hasManyThrough(Curator::class, Department::class);
    }

    public function groups(): HasManyThrough
    {
        return $this->hasManyThrough(Group::class, Department::class);
    }
}
