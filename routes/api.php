<?php

use App\Http\Controllers\SendMailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::prefix('mail')->group(function () {
    Route::post('/send', [SendMailController::class, 'send']);
});
