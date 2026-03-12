<?php

use App\Http\Controllers\Api\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Token Management Routes
Route::middleware('auth:sanctum')->prefix('tokens')->group(function () {
    Route::get('/', [TokenController::class, 'index'])->name('api.tokens.index');
    Route::get('/{tokenId}', [TokenController::class, 'show'])->name('api.tokens.show');
    Route::post('/revoke', [TokenController::class, 'revoke'])->name('api.tokens.revoke');
    Route::post('/rotate', [TokenController::class, 'rotate'])->name('api.tokens.rotate');
    Route::post('/revoke-all', [TokenController::class, 'revokeAll'])->name('api.tokens.revoke-all');
    Route::post('/revoke-all-including-current', [TokenController::class, 'revokeAllIncludingCurrent'])->name('api.tokens.revoke-all-including-current');
});
