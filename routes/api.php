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
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('users')->name('users.')->group(function(){
    Route::get('/',[UserController::class,'index'])->name('index');
    Route::get('/{id}',[UserController::class,'detail'])->name('detail');
    Route::post('/',[UserController::class,'create'])->name('create');
    Route::put('/{id}',[UserController::class,'update'])->name('update-put');
    Route::patch('/{id}',[UserController::class,'update'])->name('update-patch');
    Route::delete('/{id}',[UserController::class,'delete'])->name('delete');
});