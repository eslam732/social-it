<?php

use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('getchatmessages',[ChatController::class,'getChatMessages']);
Route::post('sendmessage',[ChatController::class,'sendMessage']);
Route::post('creategroup',[ChatController::class,'createGroup']);
Route::get('getuserchat/{userId}',[ChatController::class,'getUserChat']);