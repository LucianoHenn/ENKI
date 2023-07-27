<?php

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


Route::get('/', function (Request $request) {
    return response()->json([
        'status' => 1,
        'message' => __('Welcome To :app', ['app' => config('app.name')]),
    ], 200);
});

// Route::get('/test-signature', function (Request $request) {
//     $signature = getSignature('123456', 'secretsecretsecretsecretsecret', 'yads', 'us', 1, now());
//     return response()->json([
//         'status' => 1,
//         'message' => $signature,
//     ], 200);
// });

// Route::get('/hello-world', function (Request $request) {
//     return response()->json([
//         'status' => 1,
//         'message' => 'Hello World',
//     ], 200);
// });

Route::middleware(['throttle:ws', 'ws.validate'])->any(
    '/v1/{clientId}/{mkt}/{provider}/{configId}/{timestamp}',
    [
        \App\Http\Controllers\API\WsV1Controller::class,
        'process'
    ]
);
