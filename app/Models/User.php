<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER = 'super';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MARKETING = 'marketing';
    public const ROLE_ZAMDEKAN = 'zamdekan';

    public const PANEL_ROLES = [
        self::ROLE_SUPER,
        self::ROLE_ADMIN,
        self::ROLE_MARKETING,
        self::ROLE_ZAMDEKAN,
    ];

    public const ROLE_LABELS = [
        self::ROLE_SUPER => 'Super admin',
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_MARKETING => 'Marketing xodimi',
        self::ROLE_ZAMDEKAN => 'Zamdekan',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, self::PANEL_ROLES, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
