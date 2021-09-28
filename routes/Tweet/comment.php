<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\TweetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->post('createcomment',[CommentController::class,'createComment']);
Route::middleware('auth:api')->get('getcomments',[CommentController::class,'getComments']);
Route::middleware('auth:api')->post('likecomment/{id}',[CommentController::class,'likeComment']);
Route::middleware('auth:api')->get('getlikes/{id}',[CommentController::class,'getCommentLikes']);