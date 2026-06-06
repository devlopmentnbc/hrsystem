<?php

namespace App\Models;

use App\Models\Concerns\LogsAudit;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    use LogsAudit;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function canAccess(string $permission): bool
    {
        return $this->isAdmin() || $this->can($permission);
    }

    public function canAccessAny(array $permissions): bool
    {
        return $this->isAdmin() || $this->hasAnyPermission($permissions);
    }
}