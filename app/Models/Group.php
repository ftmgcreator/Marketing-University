<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'report_id', 'department_id', 'curator_id',
    'name', 'slug', 'faculty', 'speciality_code', 'speciality_name',
    'student_count', 'paid_count', 'debt_count',
    'contract_amount', 'paid_amount', 'debt_amount', 'percent_paid',
])]
class Group extends Model
{
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function curator(): BelongsTo
    {
        return $this->belongsTo(Curator::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
