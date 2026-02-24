<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\UniformIssuance;
use App\Models\UniformIssuanceLog;
use App\Models\UniformIssuanceRecipient;
use App\Services\ReceivingCopyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceivingCopyController extends Controller
{
    /**
     * Check if the authenticated user can access the given department.
     * Super admins can access all departments.
     * Regular users must belong to the department via the department_user pivot.
     */
    private function userCanAccessDepartment(int $departmentId): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Super admin bypasses all tenant checks (mirrors canAccessTenant on User model)
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Check pivot table: department_user
        return $user->departments()
            ->where('departments.id', $departmentId)
            ->exists();
    }

    /**
     * Get all department IDs the authenticated user can access.
     * Super admins get all department IDs.
     */
    private function userDepartmentIds(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        if ($user->hasRole('super_admin')) {
            return Department::pluck('id')->toArray();
        }

        return $user->departments()->pluck('departments.id')->toArray();
    }

    /**
     * All recipients for one issuance — 2 slips per A4.
     * For partial: shows all release logs combined.
     * GET /receiving-copy/issuance/{issuance}
     */
    public function issuance(UniformIssuance $issuance): \Illuminate\Http\Response
    {
        abort_unless(Auth::check(), 403);
        abort_unless(
            $this->userCanAccessDepartment($issuance->department_id),
            403, 'Access denied.'
        );
        abort_unless(
            in_array($issuance->status, ['partial', 'issued', 'returned']),
            404, 'Receiving copy only available for partial, issued, or returned issuances.'
        );

        // Partial: print all release logs combined
        if ($issuance->status === 'partial') {
            return response(
                ReceivingCopyService::generateAllLogs($issuance),
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        }

        return response(
            ReceivingCopyService::generate($issuance),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    /**
     * Single recipient slip (issued/returned only).
     * GET /receiving-copy/recipient/{recipient}
     */
    public function recipient(UniformIssuanceRecipient $recipient): \Illuminate\Http\Response
    {
        abort_unless(Auth::check(), 403);

        $issuance = $recipient->uniformIssuance;

        abort_unless(
            $this->userCanAccessDepartment($issuance->department_id),
            403, 'Access denied.'
        );
        abort_unless(
            in_array($issuance->status, ['issued', 'returned']),
            404, 'Receiving copy only available for issued or returned issuances.'
        );

        return response(
            ReceivingCopyService::generate($issuance, $recipient),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    /**
     * Single log entry — one release batch receiving copy (used for partial).
     * GET /receiving-copy/log/{log}
     */
    public function log(UniformIssuanceLog $log): \Illuminate\Http\Response
    {
        abort_unless(Auth::check(), 403);

        $issuance = $log->uniformIssuance;

        abort_unless(
            $this->userCanAccessDepartment($issuance->department_id),
            403, 'Access denied.'
        );
        abort_unless(
            in_array($log->action, ['partial', 'issued']),
            404, 'This log entry has no receiving copy.'
        );
        abort_if(
            empty($log->note) || ! str_starts_with(trim($log->note), '['),
            404, 'No item snapshot found for this log entry.'
        );

        return response(
            ReceivingCopyService::generateFromLog($log),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    /**
     * Bulk — comma-separated issuance IDs.
     * Handles partial (uses log snapshots), issued, and returned.
     * GET /receiving-copy/bulk?ids=1,2,3
     */
    public function bulk(Request $request): \Illuminate\Http\Response
    {
        abort_unless(Auth::check(), 403);

        $departmentIds = $this->userDepartmentIds();

        abort_if(empty($departmentIds), 403, 'You do not belong to any department.');

        $ids = collect(explode(',', $request->query('ids', '')))
            ->map('trim')->filter()->map('intval')->unique()->values()->all();

        abort_if(empty($ids), 400, 'No issuance IDs provided.');

        $issuances = UniformIssuance::whereIn('id', $ids)
            ->whereIn('status', ['partial', 'issued', 'returned'])
            ->whereIn('department_id', $departmentIds)
            ->get();

        abort_if($issuances->isEmpty(), 404, 'No eligible issuances found.');

        return response(
            ReceivingCopyService::generateBulk($issuances),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
}