<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceivingCopyController;
use App\Http\Controllers\TransmittalCopyController;
use App\Models\UniformIssuance;
use App\Models\UniformIssuanceLog;
use App\Models\UniformIssuanceRecipient;
use App\Models\Transmittal;
use App\Services\ReceivingCopyService;
use App\Services\TransmittalService;

Route::get('/', function () {
    return view('welcome');
});

// ─────────────────────────────────────────────────────────────────────────────
// RECEIVING COPY ROUTES
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth'])->group(function () {

    // Full issuance — all recipients (partial: all release logs + change receipts; issued/returned: current data)
    Route::get('/receiving-copy/issuance/{issuance}', function (UniformIssuance $issuance) {
        return response(ReceivingCopyService::generateAllLogs($issuance))
            ->header('Content-Type', 'text/html');
    })->name('receiving-copy.issuance');

    // Single recipient slip
    Route::get('/receiving-copy/recipient/{recipient}', function (UniformIssuanceRecipient $recipient) {
        $issuance = $recipient->uniformIssuance;
        return response(ReceivingCopyService::generate($issuance, $recipient))
            ->header('Content-Type', 'text/html');
    })->name('receiving-copy.recipient');

    // Single log batch slip (partial/issued release — one per release action)
    Route::get('/receiving-copy/log/{log}', function (UniformIssuanceLog $log) {
        return response(ReceivingCopyService::generateFromLog($log))
            ->header('Content-Type', 'text/html');
    })->name('receiving-copy.log');

    // Item-change receipt — changed item only
    Route::get('/receiving-copy/log/{log}/changed-only', function (UniformIssuanceLog $log) {
        abort_unless($log->action === 'item_changed', 404);
        return response(ReceivingCopyService::generateFromItemChangedLog($log, onlyChangedItems: true))
            ->header('Content-Type', 'text/html');
    })->name('receiving-copy.log.changed-only');

    // Item-change receipt — full updated RC for affected employee
    Route::get('/receiving-copy/log/{log}/full-updated', function (UniformIssuanceLog $log) {
        abort_unless($log->action === 'item_changed', 404);
        return response(ReceivingCopyService::generateFromItemChangedLog($log, onlyChangedItems: false))
            ->header('Content-Type', 'text/html');
    })->name('receiving-copy.log.full-updated');

    // Bulk — ?ids=1,2,3
    Route::get('/receiving-copy/bulk', function () {
        $ids      = array_filter(explode(',', request('ids', '')));
        $issuances = UniformIssuance::whereIn('id', $ids)->get();
        return response(ReceivingCopyService::generateBulk($issuances))
            ->header('Content-Type', 'text/html');
    })->name('receiving-copy.bulk');

});

// ─────────────────────────────────────────────────────────────────────────────
// TRANSMITTAL COPY ROUTES
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth'])->group(function () {

    // Print transmittal form from a Transmittal record
    Route::get('/transmittal-copy/{transmittal}', function (Transmittal $transmittal) {
        return response(TransmittalService::generateFromTransmittal($transmittal))
            ->header('Content-Type', 'text/html');
    })->name('transmittal-copy.show');

    // Print transmittal form from an issuance (uses its linked transmittal)
    Route::get('/transmittal-copy/issuance/{issuance}', function (UniformIssuance $issuance) {
        return response(TransmittalService::generateFromIssuance($issuance))
            ->header('Content-Type', 'text/html');
    })->name('transmittal-copy.issuance');

    // Print ALL — original transmittal + one page per item_changed log
    Route::get('/transmittal-copy/issuance/{issuance}/all', function (UniformIssuance $issuance) {
        return response(TransmittalService::generateAllFromIssuance($issuance))
            ->header('Content-Type', 'text/html');
    })->name('transmittal-copy.issuance.all');

    // Amendment transmittal — changed items only
    Route::get('/transmittal-copy/log/{log}/changed-only', function (UniformIssuanceLog $log) {
        abort_unless($log->action === 'item_changed', 404);
        return response(TransmittalService::generateAmendmentFromLog($log, changedItemsOnly: true))
            ->header('Content-Type', 'text/html');
    })->name('transmittal-copy.log.changed-only');

    // Amendment transmittal — full updated transmittal
    Route::get('/transmittal-copy/log/{log}/full-updated', function (UniformIssuanceLog $log) {
        abort_unless($log->action === 'item_changed', 404);
        return response(TransmittalService::generateAmendmentFromLog($log, changedItemsOnly: false))
            ->header('Content-Type', 'text/html');
    })->name('transmittal-copy.log.full-updated');

});