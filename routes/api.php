<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiTestController;

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

/*
 * route sanctum utente
 */

Route::group([
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth'
], function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::group([
        'middleware' => ['auth:sanctum']
    ], function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

/*
 * route di test
 */
Route::group([
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'test'
], function () {
    Route::get('/open', [ApiTestController::class,'freeApi']);
    Route::group([
        'middleware' => ['auth:sanctum'],
    ], function () {
        Route::get('/closed', [ApiTestController::class, 'onlyMiddelware']);;
    });
});
