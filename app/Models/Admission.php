<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'full_name', 'jshshir', 'passport', 'phone', 'phone2', 'address', 'region',
    'speciality_id', 'speciality_code', 'faculty', 'education_type', 'education_form', 'course',
    'contract_amount', 'admission_date', 'status', 'notes',
])]
class Admission extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'Kutilmoqda',
        self::STATUS_APPROVED => 'Qabul qilindi',
        self::STATUS_REJECTED => 'Rad etildi',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
            'contract_amount' => 'decimal:2',
        ];
    }

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class);
    }
}
