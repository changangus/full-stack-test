<?php

use App\Http\Controllers\TimeTravelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::post('{user}/travel', [TimeTravelController::class, 'travel'])->name('travel');
    Route::post('{user}/return', [TimeTravelController::class, 'return'])->name('return');
    Route::post('{user}/forward', [TimeTravelController::class, 'forward'])->name('forward');
    Route::post('{user}/back', [TimeTravelController::class, 'back'])->name('back');
    Route::get('{user}/location', [TimeTravelController::class, 'locationAt'])->name('locationAt');
});
