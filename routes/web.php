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