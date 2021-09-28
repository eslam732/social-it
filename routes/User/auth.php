<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('signup',[AuthController::class,'signUp']);
Route::post('login',[AuthController::class,'login']);

Route::get('UnAuthorized',[AuthController::class,'UnAuthorized'])->name('UnAuthorized'); 
Route::middleware('auth:api')->post('verify',[AuthController::class,'verify']);
Route::get('verifyfromemail/{id}',[AuthController::class,'verifyFromEmail'])->name('verify');


