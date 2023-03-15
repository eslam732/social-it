<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\followRequests;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function editProfile()
    {
       try{ $rules = [
            'name' => 'required',
            'about' => 'required|min:5'
        ];
        $validation = Validator::make(request()->all(), $rules);
        
            if ($validation->fails()) {
                return $validation = $validation->errors();
            }
        $user=User::find(Auth()->id());
        $user->name=request()->name;
        $user->about=request()->about;
        if(request()->hasFile('picture')){
            $path = request()->file('picture')->store('profilePictures');
            $data['picture'] = $path;
            Storage::delete($user->picture);
            $user->picture=$path;
        }
        $user->save();}
        catch(\Exception $e){
        return response()->json(['error '=>$e->getMessage()],500);

        }
        return response()->json('updated',200);
        
    }
    public function follow()
    {

        $id = request()->query('id');

        $followedUser = User::find($id);

        if (!$followedUser) {
            return response()->json('user not found', 202);
        }

        $follow = Follow::where('follower_user_id', Auth::user()->id)->where('followed_user_id', $id)->get();
        if ($follow->count()) {
            return response()->json('allready followed', 200);
        }
        $data = [];
        $data['followed_user_id'] = $id;
        $data['follower_user_id'] = Auth::user()->id;
        if ($followedUser->private) {
            $request = followRequests::where('follower_user_id', Auth::user()->id)->
                where('followed_user_id', $id)->get();
            if ($request->count()) {
                return response()->json('allready requested', 200);
            }

            followRequests::create($data);
            return response()->json('requested', 201);
        }
        $followingUser = User::find(Auth::user()->id);
        $followingUser->followings = ($followingUser->followings) + 1;
        $followingUser->save();
        $followedUser->followers = ($followedUser->followers) + 1;
        $followedUser->save();

        Follow::create($data);
        return response()->json('followed', 200);

    }

    public function acceptFollow()
    {
        $id = request()->query('id');

        $followRequest = followRequests::find($id);
        if (!$followRequest) {
            return response()->json('request not found', 202);
        }

        if ($followRequest->followed_user_id != Auth::user()->id) {
            return response()->json('unallowed', 203);
        }
        $followerId = $followRequest->follower_user_id;

        $followingUser = User::find($followerId);
        if (!$followingUser) {
            return response()->json('user not found', 202);
        }
        $data = [];
        $data['follower_user_id'] = $followRequest->follower_user_id;
        $data['followed_user_id'] = Auth::user()->id;

        $followedUser = User::find(Auth::user()->id);
        $followedUser->followers = ($followedUser->followers) + 1;
        $followedUser->save();
        $followingUser->followings = ($followingUser->followings) + 1;
        $followingUser->save();

        Follow::create($data);
        $followRequest->delete();
        return response()->json('accepted', 200);

    }
    public function getFollowers()
    {
        $id = request()->query('id');
        $user = User::find($id);
        if (!$user) {
            return response()->json('user not found', 202);
        }

        $users = DB::select(DB::raw("
            SELECT id , name ,about FROM users
           WHERE id in (select follower_user_id from `follows` where `followed_user_id` = $id)
        ;"));
        return response()->json($users, 200);
    }

    public function getFollowRequests()
    {
        $id = Auth::user()->id;
        $usersRequests = DB::select(DB::raw("
        SELECT r.id as req_id , u.id as followerId , u.name ,u.about FROM follow_requests r
        left join users u on u.id = follower_user_id
        where followed_user_id= $id ;"));

        return response()->json(['requests' => $usersRequests, 200]);
    }

    public function getFollowings()
    {

        $id = $id = request()->query('id');
        $user = User::find($id);
        if (!$user) {
            return response()->json('user not found', 202);
        }

        $users = DB::select(DB::raw("
            SELECT id , name ,about FROM users
           WHERE id in (select followed_user_id from `follows` where `follower_user_id` = $id)
        ;"));
        return response()->json(['users' => $users, 200]);
    }

    public function userProfile()
    {
        $user = User::find(request()->query('id'));
        if (!$user) {
            return response()->json(['user not found'], 205);
        }
        $user['followed'] = false;

        $followed = Follow::where('followed_user_id', request()->query('id'))
            ->where('follower_user_id', Auth::user()->id)->get();

        if (count($followed)) {
            $user['followed'] = true;
            $tweets = Tweet::where('user_id', $user->id)->limit(2)->orderBy('created_at', 'desc')->get();
            return response()->json(['user ' => $user, 'tweets' => $tweets], 200);
        }

        return response()->json(['user ' => $user], 200);
    }
    public function getPhoto()
    {
        if (!request()->picture) {
            return response()->json('enter the picture name ', 400);

        }

        $path = storage_path('app/' . request()->picture);

        if (!File::exists($path)) {
            return response()->json('image not found ', 404);

        }

        $file = File::get($path);

        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return response()->stream(function () use ($path) {
            $file = fopen($path, 'rb');
            fpassthru($file);
            fclose($file);
        }, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline; filename="'.basename($path).'"'
        ]);
    }

}
