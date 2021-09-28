<?php

namespace App\Http\Controllers;

use App\Models\Likes;
use App\Models\Notification as ModelsNotification;
use App\Models\Retweets;
use App\Models\Tweet;
use App\Models\User;
use App\Notifications\AllNotifications;
use App\Notifications\Like;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TweetController extends Controller
{

    public function createTweet()
    {
        $validation = tweetRules();
        if ($validation) {
            return $validation;
        }

        $tweetData = [];
        $tweetData['content'] = request()->content;
        $tweetData['user_id'] = Auth::user()->id;

        $tweet = Tweet::create($tweetData);
        return response()->json(['tweet' => $tweet, 201]);

    }

    public function getTweetsForUser($id)
    {

        $user = User::find($id);
        if (!$user) {
            return response()->json('user not found', 203);
        }
        $tweets = $user->tweets;
        return response()->json(['tweets' => $tweets], 200);

    }

    public function getTweets()
    {
        // $user=Auth::user();
        // $userFollows=unserialize($user->following);
        // $tweets=DB::table('tweets')
        // ->whereIn('user_id', $userFollows)
        // ->get();
        // $tweets = Tweet::all();

        //     $tweets = DB::select(DB::raw("
        //     SELECT user_id , tweet_id FROM likes
        //    right join  tweets on likes.tweet_id=tweets.id
        // ;"));

        $tweets = DB::table('tweets')->get();

        foreach ($tweets as &$tweet) {

            $userLikes = DB::table('likes')
                ->where('tweet_id', '=', $tweet->id)
                ->get(["user_id"]);
            $tweet->likedby = $userLikes;

            //return response()->json(["tweets" => $tweet, 200]);
            // $tweet->likedby = unserialize($tweet->likedby);
        }

        return response()->json(["tweets" => $tweets, 200]);
    }



    public function like($tweetId)
    {
       
        $tweet = Tweet::find($tweetId);
        if (!$tweet) {
            return response()->json("tweet not found", 202);
        }

        $userId = Auth::user()->id;

        $like = Likes::where('tweet_id', $tweetId)->where('user_id', $userId)->get();

        if (count($like)) {
            $like->each->delete();
            $tweet->likes = ($tweet->likes) - 1;
            $tweet->save();
            return response()->json("Un liked", 202);
        }
        $tweet->likes = ($tweet->likes) + 1;
        $tweet->save();
        $likeData = [];
        $likeData['tweet_id'] = $tweetId;
        $likeData['user_id'] = $userId;

        $like = Likes::create($likeData);
        $notificationData['notifiable_id']=$tweet->user_id;
        $notificationData['creator_id']=$userId;
        $notificationData['type']='Like';
        $notificationData['object_id']=$like->id;
        ModelsNotification::create($notificationData);
        return response()->json('liked', 200);

       

    }

    public function getLikes($tweetId)
    {$tweet = Tweet::find($tweetId);
        if (!$tweet) {
            return response()->json("tweet not found", 200);
        }

        $likes = Likes::where('tweet_id', '=', $tweetId)->with('user')->get()->pluck('user');

        return response()->json(['likes' => $likes, 200]);

    }

    public function retweet($tweetId)
    {
        $tweet=Tweet::find($tweetId);
        if(!$tweet){
            return response()->json("tweet not found", 200);
        }
        $userId=Auth::user()->id;

        $retweet=Retweets::where('tweet_id', $tweetId)->where('user_id', $userId)->get();

        if (count($retweet)) {
            $retweet->each->delete();
            $tweet->retweets = ($tweet->retweets) - 1;
            $tweet->save();
            return response()->json("Undo retweeted", 202);
        }

        $tweet->retweets = ($tweet->retweets) + 1;
        $tweet->save();
        $retweetData = [];
        $retweetData['tweet_id'] = $tweetId;
        $retweetData['user_id'] = $userId;

        $retweeted = Retweets::create($retweetData);
        return response()->json('retweeted', 200);

    }

    public function tweetRetweets($tweetId)
    {
        $tweet=Tweet::find($tweetId);
        if(!$tweet){
            return response()->json("tweet not found", 200);
        }

        // $retweets=Retweets::where('tweet_id', $tweetId)->with('user')->get();

        $retweets=DB::select(DB::raw("
        SELECT users.id,name,picture,about,email FROM users 
        join  retweets on users.id=retweets.user_id
         where (tweet_id=$tweetId)
           
        "));
        return response()->json(['retweets'=>$retweets, 200]);
    }


    public function getNotifications()
    {$user=Auth::user();

      
       


           $notification=DB::select(DB::raw("
                    SELECT name,email,type FROM users 
                   join  notifications on users.id=notifications.creator_id
                    where (notifiable_id=$user->id)
                
                ;"));
       return response()->json(['notifications'=>$notification],200);
        
   }
    
}
