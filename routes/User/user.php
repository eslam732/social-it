<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::middleware('auth:api')->post('follow/{id}',[UserController::class,'follow']);
Route::middleware('auth:api')->get('getfollower/{id}',[UserController::class,'getFollowers']);
Route::middleware('auth:api')->get('getfollowings/{id}',[UserController::class,'getFollowings']);