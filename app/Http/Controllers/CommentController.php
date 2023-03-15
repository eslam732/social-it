<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLikes;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{

    public function createComment()
    {

        if (request()->tweetId) {
            $tweetId = request()->tweetId;
            $tweet = Tweet::find($tweetId);
            if (!$tweet) {
                return response()->json("tweet not found", 200);
            }

            $data = [];
            $data['user_id'] = Auth::user()->id;
            $data['tweet_id'] = $tweetId;
            $data['content'] = request()->content;

            $comment = Comment::create($data);

            return response()->json(['comment created' => $comment, 200]);
        }

        if (request()->commentId) {
            $commentId = request()->commentId;
            $comment = Comment::find($commentId);
            if (!$comment) {
                return response()->json("comment not found", 200);
            }

            $data = [];
            $data['user_id'] = Auth::user()->id;
            $data['comment_id'] = $commentId;
            $data['content'] = request()->content;

            $comment = Comment::create($data);

            return response()->json(['comment created' => $comment, 200]);
        } else {
            return response()->json('you have to give a id', 203);
        }
    }

    public function getComments()
    {

        if (request()->tweetId) {
            $tweetId = request()->tweetId;
            $tweet = Tweet::find($tweetId);
            if (!$tweet) {
                return response()->json("tweet not found", 200);
            }
            $comments = DB::select(DB::raw("
                SELECT * FROM users
               join  comments on users.id=comments.user_id
                where (tweet_id=$tweetId)

            ;"));

            return response()->json($comments);

        }

        if (request()->commentId) {
            $commentId = request()->commentId;

            $comment = Comment::find($commentId);
            if (!$comment) {
                return response()->json("comment not found", 200);
            }
            $comments = DB::select(DB::raw("
            SELECT * FROM users
           join  comments on users.id=comments.user_id
            where (comment_id=$commentId)


        ;"));

            return response()->json($comments);

        }
    }

    public function likeComment($commentId)
    {
        $comment = Comment::find($commentId);
        if (!$comment) {
            return response()->json("comment not found", 202);
        }

        $userId = Auth::user()->id;

        $like = CommentLikes::where('comment_id', $commentId)->where('user_id', $userId)->get();

        if (count($like)) {
            $like->each->delete();
            $comment->likes = ($comment->likes) - 1;
            $comment->save();
            return response()->json("Un liked", 202);
        }
        $comment->likes = ($comment->likes) + 1;

        $comment->save();
        $likeData = [];
        $likeData['comment_id'] = $commentId;
        $likeData['user_id'] = $userId;

        $like = CommentLikes::create($likeData);
        return response()->json('liked', 200);
    }

    public function getCommentLikes($commentId)
    {$comment = Comment::find($commentId);
        if (!$comment) {
            return response()->json("comment not found", 200);
        }

        $likes = CommentLikes::where('comment_id', '=', $commentId)->with('user')->get()->pluck('user');

        return response()->json(['likes' => $likes, 200]);

    }

}
