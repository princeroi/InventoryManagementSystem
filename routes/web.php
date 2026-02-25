<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\ReceivingCopyController;

Route::middleware(['auth'])->group(function () {

    // Full issuance — all recipients (partial: all release logs; issued/returned: current data)
    Route::get('/receiving-copy/issuance/{issuance}', [ReceivingCopyController::class, 'issuance'])
        ->name('receiving-copy.issuance');

    // Single recipient slip (issued/returned only)
    Route::get('/receiving-copy/recipient/{recipient}', [ReceivingCopyController::class, 'recipient'])
        ->name('receiving-copy.recipient');

    // Single log batch slip (partial releases — one per release action)
    Route::get('/receiving-copy/log/{log}', [ReceivingCopyController::class, 'log'])
        ->name('receiving-copy.log');

    // Bulk — ?ids=1,2,3 — handles partial, issued, returned
    Route::get('/receiving-copy/bulk', [ReceivingCopyController::class, 'bulk'])
        ->name('receiving-copy.bulk');

});

use App\Http\Controllers\TransmittalCopyController;

Route::middleware(['auth'])->group(function () {

    // Print transmittal form from a Transmittal record
    Route::get('/transmittal-copy/{transmittal}', [TransmittalCopyController::class, 'show'])
        ->name('transmittal-copy.show');

    // Print transmittal form from an issuance (uses its linked transmittal)
    Route::get('/transmittal-copy/issuance/{issuance}', [TransmittalCopyController::class, 'fromIssuance'])
        ->name('transmittal-copy.issuance');

});