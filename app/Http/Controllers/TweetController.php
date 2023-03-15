<?php

namespace App\Http\Controllers;

use App\Events\LikeEvent;
use App\Models\Likes;
use App\Models\Retweets;
use App\Models\Tweet;
use App\Models\User;
use App\Notifications\Like;
use Illuminate\Http\Request;
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
        return response()->json(['tweet' => $tweet], 201);

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

        // $tweets = DB::table('tweets')->get();

        // foreach ($tweets as &$tweet) {

        //     $userLikes = DB::table('likes')
        //         ->where('tweet_id', '=', $tweet->id)
        //         ->get(["user_id"]);
        //     $tweet->likedby = $userLikes;

        //return response()->json(["tweets" => $tweet, 200]);
        // $tweet->likedby = unserialize($tweet->likedby);
        // }
        $tweets = Tweet::with('user')->with('likes')->get();

        return response()->json(["tweets" => $tweets, 200]);
    }

    public function like($tweetId)
    {

        try { 
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
        $notifiableUser = Tweet::where('id', $tweetId)->with('user')->get()->pluck('user')[0];
        if (!$notifiableUser) {
            return response()->json(['cant like due to server error or user is not available'], 400);
        }

        $likeData['tweet_id'] = $tweetId;
        $likeData['user_id'] = $userId;
        $like = Likes::create($likeData);
        $tweet->likes = ($tweet->likes) + 1;
        $tweet->save();
       

            $notifiableUser->notify(new Like(Auth()->user(), $tweet, $like));
            broadcast(new LikeEvent($like, Auth::user(), $tweet))->toOthers();

        }
        catch(\Exception $e){
            return response()->json(['liked but some error has ocured in sending notification or brodcasting'=>$e->getMessage()], 200);

        }
        

        return response()->json('liked', 200);

    }

    public function getLikes($tweetId)
    {
        $tweet = Tweet::find($tweetId);
        if (!$tweet) {
            return response()->json("tweet not found", 200);
        }

        //   $likes = Likes::where('tweet_id', '=', $tweetId)->with('user')->get();
        $likenew = $tweet->likes()->with('user')->get();

        return response()->json(['likes' => $likenew, 200]);

    }

    public function retweet($tweetId)
    {
        $tweet = Tweet::find($tweetId);
        if (!$tweet) {
            return response()->json("tweet not found", 200);
        }
        $userId = Auth::user()->id;

        $retweet = Retweets::where('tweet_id', $tweetId)->where('user_id', $userId)->get();

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

        Retweets::create($retweetData);
        return response()->json('retweeted', 200);

    }

    public function tweetRetweets($tweetId)
    {
        $tweet = Tweet::find($tweetId);
        if (!$tweet) {
            return response()->json("tweet not found", 200);
        }

        // $retweets=Retweets::where('tweet_id', $tweetId)->with('user')->get();

        $retweets = DB::select(DB::raw("
        SELECT users.id,name,picture,about,email FROM users
        join  retweets on users.id=retweets.user_id
         where (tweet_id=$tweetId)

        "));
        return response()->json(['retweets' => $retweets, 200]);
    }

}
