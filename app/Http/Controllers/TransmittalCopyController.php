<?php

namespace App\Http\Controllers;

use App\Models\Transmittal;
use App\Models\UniformIssuance;
use App\Services\TransmittalService;
use Illuminate\Support\Facades\Auth;

class TransmittalCopyController extends Controller
{
    /**
     * Check if the authenticated user can access the given department.
     */
    private function userCanAccessDepartment(int $departmentId): bool
    {
        $user = Auth::user();

        if (! $user) return false;

        if ($user->hasRole('super_admin')) return true;

        return $user->departments()
            ->where('departments.id', $departmentId)
            ->exists();
    }

    /**
     * Print a transmittal form from a Transmittal record.
     * GET /transmittal-copy/{transmittal}
     */
    public function show(Transmittal $transmittal): \Illuminate\Http\Response
    {
        abort_unless(Auth::check(), 403);
        abort_unless(
            $this->userCanAccessDepartment($transmittal->department_id),
            403, 'Access denied.'
        );

        return response(
            TransmittalService::generateFromTransmittal($transmittal),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    /**
     * Print a transmittal form from an issuance (uses its linked transmittal or
     * generates a preview if no transmittal exists yet).
     * GET /transmittal-copy/issuance/{issuance}
     */
    public function fromIssuance(UniformIssuance $issuance): \Illuminate\Http\Response
    {
        abort_unless(Auth::check(), 403);
        abort_unless(
            $this->userCanAccessDepartment($issuance->department_id),
            403, 'Access denied.'
        );
        abort_unless(
            $issuance->is_for_transmit,
            404, 'This issuance is not marked for transmittal.'
        );
        abort_unless(
            in_array($issuance->status, ['issued', 'partial', 'returned']),
            404, 'Transmittal form only available for issued, partial, or returned issuances.'
        );

        return response(
            TransmittalService::generateFromIssuance($issuance),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
}