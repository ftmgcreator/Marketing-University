<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'contract_number', 'contract_date',
    'full_name',
    'speciality', 'group_name',
    'jshshir', 'passport',
    'subject_name', 'credits_count', 'price_per_credit', 'total_amount',
    'payment_status', 'paid_amount',
    'notes',
])]
class CreditContract extends Model
{
    public const PRICE_PER_CREDIT = 100000;

    protected function casts(): array
    {
        return [
            'contract_date' => 'date',
            'credits_count' => 'integer',
            'price_per_credit' => 'integer',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
        ];
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
