<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UniformIssuanceLog;

class UniformIssuance extends Model
{
    protected $fillable = [
        'department_id',
        'site_id',
        'issuance_type_id',
        'status',
        'pending_at',
        'partial_at',
        'issued_at',
        'returned_at',
        'cancelled_at',
    ];

    protected $casts = [
        'pending_at'   => 'date',
        'partial_at'   => 'date',
        'issued_at'    => 'date',
        'returned_at'  => 'date',
        'cancelled_at' => 'date',
    ];

    public ?array $logSnapshot = null;
    public bool $forceLog      = false;

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function issuanceType(): BelongsTo
    {
        return $this->belongsTo(IssuanceType::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(UniformIssuanceRecipient::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(UniformIssuanceLog::class)->orderBy('created_at', 'desc');
    }

    public function deductStock(): void
    {
        $this->loadMissing('recipients.items');

        foreach ($this->recipients as $recipient) {
            foreach ($recipient->items as $item) {
                if (! $item->size) continue;

                ItemVariant::where('item_id', $item->item_id)
                    ->where('size_label', $item->size)
                    ->first()
                    ?->decrement('quantity', $item->quantity);
            }
        }
    }

    public function restoreStock(): void
    {
        $this->loadMissing('recipients.items');

        foreach ($this->recipients as $recipient) {
            foreach ($recipient->items as $item) {
                if (! $item->size) continue;

                ItemVariant::where('item_id', $item->item_id)
                    ->where('size_label', $item->size)
                    ->first()
                    ?->increment('quantity', $item->quantity);
            }
        }
    }

    protected static function booted(): void
    {
        static::saving(function (self $issuance) {
            if ($issuance->isDirty('status')) {
                $column = match ($issuance->status) {
                    'pending'   => 'pending_at',
                    'partial'   => 'partial_at',
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

        static::created(function (self $issuance) {
            $action = match ($issuance->status) {
                'pending'   => 'pending',
                'partial'   => 'partial',
                'issued'    => 'issued',
                'returned'  => 'returned',
                'cancelled' => 'cancelled',
                default     => 'created',
            };

            UniformIssuanceLog::create([
                'uniform_issuance_id' => $issuance->id,
                'action'              => $action,
                'performed_by'        => auth()->user()?->name ?? 'System',
                'note'                => null,
            ]);
        });

        static::updated(function (self $issuance) {
            if ($issuance->wasChanged('status')) {
                $newStatus = $issuance->status;
                $oldStatus = $issuance->getOriginal('status');

                // issued is the only stock-consuming status now
                $wasStockConsumed = $oldStatus === 'issued';
                $isStockConsumed  = $newStatus === 'issued';

                if ($isStockConsumed && ! $wasStockConsumed && $issuance->logSnapshot === null) {
                    $issuance->deductStock();
                }

                if (! $isStockConsumed && $wasStockConsumed && $issuance->logSnapshot === null) {
                    $issuance->restoreStock();
                }
            }

            if (! $issuance->wasChanged('status')) return;

            $note = null;
            if (! empty($issuance->logSnapshot)) {
                $note = json_encode($issuance->logSnapshot);
            }

            UniformIssuanceLog::create([
                'uniform_issuance_id' => $issuance->id,
                'action'              => $issuance->status,
                'performed_by'        => auth()->user()?->name ?? 'System',
                'note'                => $note,
            ]);

            $issuance->logSnapshot = null;
            $issuance->forceLog    = false;
        });
    }
}