<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{



    
    public function follow($id)
    {$followedUser = User::find($id);
        if (!$followedUser) {
            return response()->json('user not found', 202);
        }



         $data=[];
         $data['followed_user_id']=$id;
         $data['follower_user_id']=Auth::user()->id;

         Follow::create($data);
         return response()->json('followed', 200);

        // $followedUserArray = unserialize($followedUser->followers);

        // array_push($followedUserArray, $currentUser->id);

        // $serializedfollowedUserArray = serialize($followedUserArray);
        // DB::table('users')
        //     ->where('id', $id)
        //     ->update(['followers' => $serializedfollowedUserArray]);

        // $followingUserArray = unserialize($currentUser->following);
        
        // array_push($followingUserArray, $id);
        // $serializedfollowingUserArray = serialize($followingUserArray);
        
        // DB::table('users')
        //       ->where('id', $currentUser->id)
        //       ->update(['following' => $serializedfollowingUserArray]);

    }

    public function getFollowers($id)
    {
        $user=User::find($id);
        if(!$user){
            return response()->json('user not found', 202);
        }
        // $users = unserialize($user->followers);
        // if (!$users) {
        //     return response()->json('this user has no followers', 202);
        // }
        // $users=DB::table('follows')
        // ->where('followed_user_id',$id);
        // $user = DB::table('users')
        //     ->whereIn('id', $users)
        //     ->get(['id', 'name', 'picture','about']);
     $users = DB::select(DB::raw("
            SELECT id , name ,about FROM users
           WHERE id in (select follower_user_id from `follows` where `followed_user_id` = $id)
        ;"));
        return response()->json($users, 200);
    }

    public function getFollowings($id)
    {
        $user=User::find($id);
        if(!$user){
            return response()->json('user not found', 202);
        }
        // $userFolloingsArray=unserialize($user->following);
        // $users = DB::table('users')
        //     ->whereIn('id', $userFolloingsArray)
        //     ->get(['id', 'name', 'picture']);

        $users = DB::select(DB::raw("
            SELECT id , name ,about FROM users 
           WHERE id in (select followed_user_id from `follows` where `follower_user_id` = $id)
        ;"));
            return response()->json(['users'=>$users,200]);
    }
    
}
