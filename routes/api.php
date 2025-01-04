<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonalInformationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {
    Route::apiResource('personal-information', PersonalInformationController::class);
    // API Resource is Equivalent to Like this code Below
    // Route::get('personal-information', [PersonalInformationController::class, 'index']);
    // Route::post('personal-information', [PersonalInformationController::class, 'store']);
    // Route::get('personal-information/{id}', [PersonalInformationController::class, 'show']);
    // Route::put('personal-information/{id}', [PersonalInformationController::class, 'update']);
    // Route::delete('personal-information/{id}', [PersonalInformationController::class, 'destroy']);
});