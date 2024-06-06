<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('user/store','App\Http\Controllers\Api\UserController@store');
Route::get('user/get/{flag}',[UserController::class,'index']);
Route::get('user/{id}',[UserController::class , 'show']);
Route::delete('user/delete/{id}',[UserController::class,'destroy']);
Route::put('user/update/{id}',[UserController::class,'update']);
Route::patch('user/change-password/{id}',[UserController::class,'changePassword']);