<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniformIssuanceBilling extends Model
{
    protected $fillable = [
        'uniform_issuance_id',
        'uniform_issuance_recipient_id',
        'employee_name',
        'employee_status',
        'issuance_type',
        'bill_status',
        'endorsed_at',
        'billed_at',
        'endorsed_by',
        'billed_by',
    ];

    protected $casts = [
        'endorsed_at' => 'datetime',
        'billed_at'   => 'datetime',
    ];

    // ── Eligibility rule ──────────────────────────────────────────────────────
    public static function isEligible(string $issuanceType, string $employeeStatus): bool
    {
        return match (true) {
            $issuanceType === 'New Hire'      && $employeeStatus === 'posted'   => true,
            $issuanceType === 'Salary Deduct'                                   => true, // posted OR reliever
            $issuanceType === 'Annual Issuance'        && $employeeStatus === 'posted'   => true,
            $issuanceType === 'Additional'    && $employeeStatus === 'posted'   => true,
            default => false,
        };
    }

    public function uniformIssuance(): BelongsTo
    {
        return $this->belongsTo(UniformIssuance::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(UniformIssuanceRecipient::class, 'uniform_issuance_recipient_id');
    }
}