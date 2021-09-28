<?php

use App\Http\Controllers\TweetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->post('createtweet',[TweetController::class,'createTweet']);
Route::middleware('auth:api')->get('gettweetsforuser/{id}',[TweetController::class,'getTweetsForUser']);
Route::middleware('auth:api')->get('gettweets',[TweetController::class,'getTweets']);
Route::middleware('auth:api')->post('like/{tweetId}',[TweetController::class,'like']);
Route::middleware('auth:api')->get('getlikes/{tweetId}',[TweetController::class,'getLikes']);
Route::middleware('auth:api')->post('retweet/{tweetId}',[TweetController::class,'retweet']);
Route::middleware('auth:api')->get('getretweets/{tweetId}',[TweetController::class,'tweetRetweets']);
Route::middleware('auth:api')->get('getnotifications',[TweetController::class,'getNotifications']);
