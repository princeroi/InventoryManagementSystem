<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UniformIssuanceLog;
use App\Models\UniformIssuanceBilling;

class UniformIssuance extends Model
{
    protected $fillable = [
        'department_id',
        'site_id',
        'issuance_type_id',
        'status',
        'note',
        'pending_at',
        'partial_at',
        'issued_at',
        'returned_at',
        'cancelled_at',
        'is_for_transmit',
        'transmitted_to',
        'transmittal_id',
    ];

    protected $casts = [
        'pending_at'      => 'date',
        'partial_at'      => 'date',
        'issued_at'       => 'date',
        'returned_at'     => 'date',
        'cancelled_at'    => 'date',
        'is_for_transmit' => 'boolean',
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

    public function transmittal(): BelongsTo
    {
        return $this->belongsTo(Transmittal::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(UniformIssuanceBilling::class);
    }

    /**
     * Append return entries to the note field.
     * Note is stored as a JSON array of return events for tracking.
     * Each entry: {employee, item, size, qty, by, at}
     */
    public function appendReturnNote(array $entries, string $performer): void
    {
        $existing = [];

        if ($this->note) {
            $decoded = json_decode($this->note, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Already JSON array format
                $existing = $decoded;
            } else {
                // Legacy plain text note — preserve it as a text entry
                $existing = [['text' => $this->note, 'at' => null]];
            }
        }

        foreach ($entries as $entry) {
            $existing[] = [
                'employee' => $entry['employee'],
                'item'     => $entry['item'],
                'size'     => $entry['size'],
                'qty'      => $entry['qty'],
                'by'       => $performer,
                'at'       => now()->timezone('Asia/Manila')->format('Y-m-d H:i'),
            ];
        }

        $this->updateQuietly(['note' => json_encode($existing)]);
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
            if (! $issuance->wasChanged('status')) return;

            $newStatus = $issuance->status;
            $oldStatus = $issuance->getOriginal('status');

            // ── Stock deduction: pending/partial → issued ─────────────────────
            // Only auto-deduct when transitioning INTO issued and we don't have
            // a manual snapshot (meaning the action already handled stock).
            $wasStockConsumed = in_array($oldStatus, ['issued']);
            $isStockConsumed  = in_array($newStatus, ['issued']);

            if ($isStockConsumed && ! $wasStockConsumed && $issuance->logSnapshot === null) {
                $issuance->deductStock();
            }

            // NOTE: We no longer auto-restore stock when status changes to 'cancelled'
            // or any other non-issued state. Stock restoration for returns is handled
            // explicitly in the return action (UniformIssuancesTable) with fine-grained
            // per-item quantities. For cancellations, stock was never deducted (pending
            // issuances haven't had stock deducted yet).

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