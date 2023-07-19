<?php

use App\Http\Controllers\ActionsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/activations/{activation}/actions', [ActionsController::class, 'store'])
    ->name('activations.actions');

Route::post('/activations/{activation}/webhook', [WebhookController::class, 'store'])
    ->name('activations.webhook');
