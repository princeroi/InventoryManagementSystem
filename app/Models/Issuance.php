<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\IssuanceLog;

class Issuance extends Model
{
    protected $fillable = [
        'site_id',
        'issued_to',
        'status',
        'pending_at',
        'released_at',
        'issued_at',
        'returned_at',
        'cancelled_at',
        'note',
    ];

    protected $casts = [
        'pending_at'   => 'date',
        'released_at'  => 'date',
        'issued_at'    => 'date',
        'returned_at'  => 'date',
        'cancelled_at' => 'date',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(IssuanceItem::class);
    }

    public function getItemIdsAttribute(): string
    {
        return $this->items->map(fn ($i) =>
            $i->size
                ? "{$i->item->name} ({$i->size}) - {$i->quantity}"
                : "{$i->item->name} - {$i->quantity}"
        )->implode('<br>');
    }

    public function getStatusDateAttribute(): ?\Illuminate\Support\Carbon
    {
        return match ($this->status) {
            'pending'   => $this->pending_at,
            'released'  => $this->released_at,
            'issued'    => $this->issued_at,
            'returned'  => $this->returned_at,
            'cancelled' => $this->cancelled_at,
            default     => null,
        };
    }


    protected static function booted(): void
    {
        static::saving(function (self $issuance) {
            if ($issuance->isDirty('status')) {
                $column = match ($issuance->status) {
                    'pending'   => 'pending_at',
                    'released'  => 'released_at',
                    'issued'    => 'issued_at',
                    'returned'  => 'returned_at',
                    'cancelled' => 'cancelled_at',
                    default     => null,
                };

                if ($column && empty($issuance->{$column})) {
                    $issuance->{$column} = now();
                }
            }
        });

        // Log on create
        static::created(function (self $issuance) {

            $action = match ($issuance->status) {
                    'pending'   => 'pending',
                    'released'  => 'released',
                    'issued'    => 'issued',
                    'returned'  => 'returned',
                    'cancelled' => 'cancelled',
                    default     => 'created',
                };

            
            IssuanceLog::create([
                'issuance_id'  => $issuance->id,
                'action'       => $action,
                'performed_by' => auth()->user()?->name ?? 'System',
                'note'         => null,
            ]);
        });

        static::updated(function (self $issuance) {
            if (! $issuance->isDirty('status')) return;

            IssuanceLog::create([
                'issuance_id'  => $issuance->id,
                'action'       => $issuance->status,
                'performed_by' => auth()->user()?->name ?? 'System',
                'note'         => null,
            ]);
        });
    }

    public function logs(): HasMany
    {
        return $this->hasMany(IssuanceLog::class)->orderBy('created_at', 'desc');
    }
}