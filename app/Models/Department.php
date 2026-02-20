<?php
namespace App\Models;

use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model 
{
    protected $fillable = ['name', 'slug'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
