<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLES = [
        'admin' => 'Administrator',
        'accountant' => 'Accountant',
        'viewer' => 'Viewer',
    ];

    public const ROLE_PERMISSIONS = [
        'admin' => ['*', 'audit.view'],
        'accountant' => [
            'dashboard.view', 'progress.view', 'reports.view',
            'clients.view', 'clients.manage', 'incomes.view', 'incomes.manage',
            'expenses.view', 'expenses.manage', 'vouchers.view', 'vouchers.manage', 'exports.view',
        ],
        'viewer' => [
            'dashboard.view', 'progress.view', 'reports.view',
            'clients.view', 'incomes.view', 'expenses.view', 'vouchers.view',
        ],
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasPermission(string $permission): bool
    {
        return in_array('*', self::ROLE_PERMISSIONS[$this->role] ?? [], true)
            || in_array($permission, self::ROLE_PERMISSIONS[$this->role] ?? [], true);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
