<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->roles()->exists() || $this->permissions()->exists();
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_user');
    }

    public function getTenants(Panel $panel): Collection
    {
        if ($this->hasRole('super_admin')) {
            return Department::all();
        }
        return $this->departments;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }
        return $this->departments->contains($tenant);
    }

    /**
     * Returns users who can approve office supply requests
     * and belong to the officesupply department.
     *
     * Uses distinct() to prevent Spatie's permission scope from
     * returning duplicate rows when a user has the permission
     * both directly AND via a role.
     */
    public static function officeSupplyApprovers(): \Illuminate\Database\Eloquent\Collection
    {
        return static::permission('release office-supply-request')
            ->distinct()  // ← fixes duplicate rows from Spatie's join
            ->get()
            ->filter(fn ($user) => $user->departments()
                ->where('slug', 'officesupply')
                ->exists()
            );
    }
}