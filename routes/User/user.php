<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::middleware('auth:api')->post('follow',[UserController::class,'follow']);
Route::middleware('auth:api')->post('acceptfollow',[UserController::class,'acceptFollow']);
Route::middleware('auth:api')->get('getfollowers',[UserController::class,'getFollowers']);
Route::middleware('auth:api')->get('getfollowings',[UserController::class,'getFollowings']);
Route::middleware('auth:api')->get('userprofile',[UserController::class,'userProfile']);
Route::middleware('auth:api')->post('editprofile',[UserController::class,'editProfile']);
Route::post('userphoto',[UserController::class,'uploadPhoto']);
Route::get('getphoto',[UserController::class,'getPhoto']);
