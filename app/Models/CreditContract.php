<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'contract_number', 'contract_date',
    'full_name', 'address', 'jshshir', 'passport', 'phone', 'student_code',
    'speciality', 'speciality_id', 'faculty', 'education_type', 'education_form',
    'course', 'group_name',
    'subject_name', 'subjects', 'credits_count', 'price_per_credit', 'total_amount',
    'payment_status', 'paid_amount',
    'notes',
])]
class CreditContract extends Model
{
    use LogsActivity;

    public const PRICE_PER_CREDIT = 100000;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'contract_number', 'contract_date',
                'full_name', 'jshshir', 'phone',
                'speciality', 'faculty', 'education_form', 'course', 'group_name',
                'credits_count', 'total_amount',
                'payment_status', 'paid_amount',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('credit_contract');
    }

    protected function casts(): array
    {
        return [
            'contract_date' => 'date',
            'credits_count' => 'integer',
            'price_per_credit' => 'integer',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'subjects' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $contract): void {
            $specialityId = $contract->getAttribute('speciality_id');
            if (! $specialityId) {
                return;
            }

            $s = Speciality::find($specialityId);
            if (! $s) {
                return;
            }

            $contract->setAttribute('speciality', $s->name);
            $contract->setAttribute('faculty', $s->faculty);
            $contract->setAttribute('education_type', $s->education_type);
            $contract->setAttribute('education_form', $s->education_form);
        });
    }

    public static function nextContractNumber(): string
    {
        $year = now()->year;
        $prefix = "KM-{$year}-";
        $last = static::where('contract_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('contract_number');

        $next = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
