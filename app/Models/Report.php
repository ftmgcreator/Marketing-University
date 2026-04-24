<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['report_date', 'file_name', 'is_active'])]
class Report extends Model
{
    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function faculties(): HasMany
    {
        return $this->hasMany(Faculty::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function curators(): HasMany
    {
        return $this->hasMany(Curator::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
