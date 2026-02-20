<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    use HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_user');
    }

    public function getTenants(Panel $panel): Collection
    {
        // Super admin sees ALL departments in the switcher
        if ($this->hasRole('super_admin')) {
            return Department::all();
        }
        return $this->departments;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        // Super admin bypasses tenant check entirely
        if ($this->hasRole('super_admin')) {
            return true;
        }
        return $this->departments->contains($tenant);
    }

}
