<?php declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Sas\ShopwareLaravelSdk\Http\Controllers\AppRegistrationController;

Route::name('sas.app.auth.')->group(function (): void {
    Route::get(
        config('sas_app.registration_url'),
        [AppRegistrationController::class, 'register']
    )->name('registration');

    Route::post(
        config('sas_app.confirmation_url'),
        [AppRegistrationController::class, 'confirm']
    )->name('confirmation');
});
