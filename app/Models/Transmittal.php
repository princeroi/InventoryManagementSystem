<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Transmittal extends Model
{
    protected $fillable = [
        'transmittal_number',
        'department_id',
        'transmitted_by',
        'transmitted_by_user_id',
        'transmitted_to',
        'items_summary',
    ];

    protected $casts = [
        'items_summary' => 'array',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function transmittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transmitted_by_user_id');
    }

    public function issuances(): HasMany
    {
        return $this->hasMany(UniformIssuance::class);
    }

    /**
     * Generate next transmittal number: HR-YYYYMMDD-XXXX
     * Sequence resets per calendar day per department.
     */
    public static function generateNumber(int $departmentId): string
    {
        $prefix = 'HR-' . now()->format('Ymd') . '-';

        $last = DB::table('transmittals')
            ->where('department_id', $departmentId)
            ->where('transmittal_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('transmittal_number');

        $nextSeq = 1;
        if ($last) {
            $parts   = explode('-', $last);
            $lastSeq = (int) end($parts);
            $nextSeq = $lastSeq + 1;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Aggregate items from a UniformIssuance into a summary array.
     */
    public static function buildSummaryFromIssuance(UniformIssuance $issuance): array
    {
        $issuance->loadMissing('recipients.items.item');

        $aggregated = [];

        foreach ($issuance->recipients as $recipient) {
            foreach ($recipient->items as $item) {
                $itemName = $item->item?->name ?? "Item #{$item->item_id}";
                $size     = $item->size ?? null;
                $qty      = (int) ($item->released_quantity ?: $item->quantity);

                if ($qty <= 0) continue;

                $key = "{$item->item_id}:{$size}";

                if (! isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'item_name' => $itemName,
                        'size'      => $size,
                        'quantity'  => 0,
                    ];
                }

                $aggregated[$key]['quantity'] += $qty;
            }
        }

        return array_values($aggregated);
    }
}