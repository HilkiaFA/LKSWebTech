<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\{Validator};
use App\Models\User;
use App\Models\Posts;
use App\Models\Post_attachments;
use App\Models\Follow;

class FollowController extends Controller
{
    public function follow_user(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }

        if($user->id === auth()->id())
        {
            return response()->json([
            'message' => 'You are not allowed to follow yourself'
            ], 422);
        }

        $sudahfollow = Follow::where('follower_id', auth()->id())
                             ->where('following_id', $user->id)
                             ->first();

        if($sudahfollow)
        {
            return response()->json([
            'message' => 'You are already followed',
            'status' => $sudahfollow->is_accepted ? 'following' : 'requested'
            ], 422);
        }

        $status = $user->is_private ? 'requested' : 'following';

        $follow = new Follow;
        $follow->follower_id = auth()->id();
        $follow->following_id = $user->id;
        $follow->is_accepted = !$user->is_private;
        $follow->save();

        return response()->json([
            'message' => 'Follow success',
            'status' => $status
        ], 200);
    }

    public function unfollow_user(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }


        $sudahfollow = Follow::where('follower_id', auth()->id())
                             ->where('following_id', $user->id)
                             ->first();

        if(!$sudahfollow)
        {
            return response()->json([
            'message' => 'You are not following the user'
            ], 422);
        }

        $sudahfollow->delete();

        return response()->json([
            'message' => 'Unfollow success',
        ], 204);
    }

    public function user_following(Request $request)
    {
        $user = Auth::user();
    
        $following = $user->following;
    
        $datafollowing = $following->map(function ($as){
            $is_accepted = $as->pivot->is_accepted == 0 ? true : false;
            return [
                'id' => $as->id,
                'full_name' => $as->full_name,
                'username' => $as->username,
                'bio' => $as->bio,
                'is_private' => $as->is_private,
                'created_at' => $as->created_at,
                'is_requested' => $is_accepted
            ];
        });
    
        return response()->json([
            'following' => $datafollowing
        ], 200);
    }
    

    public function accept_follow(Request $request, $username)
    {
        $user = User::where('username',$username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }

        $sudahfollow = Follow::where('follower_id', $user->id)
        ->where('following_id', auth()->id())
        ->first();

        if(!$sudahfollow)
        {
            return response()->json([
            'message' => 'The user is not following you'
            ], 422);
        }

        if($sudahfollow->is_accepted)
        {
            return response()->json([
            'message' => 'Follow request is already accepted'
            ], 422);
        }

        $sudahfollow->is_accepted = true;
        $sudahfollow->save();

        return response()->json([
        'message' => 'Follow request accepted'
        ], 200);
    }

    public function user_follower(Request $request, $username)
    {
        $user =  User::where('username', $username)->first();
    
        $follower = $user->follower;
    
        $datafollower = $follower->map(function ($ass){
            $is_accepted = $ass->pivot->is_accepted == 0 ? true : false;
            return [
                'id' => $ass->id,
                'full_name' => $ass->full_name,
                'username' => $ass->username,
                'bio' => $ass->bio,
                'is_private' => $ass->is_private,
                'created_at' => $ass->created_at,
                'is_accepted' => $is_accepted,
            ];
        });
    
        return response()->json([
        'follower' => $datafollower
        ], 200);
    }

    public function get_user(Request $request)
    {
        $user = Auth::user();

        $tidakdifollow = User::whereNotIn('id', function($query) use ($user){
        $query->select('following_id')
                ->from('follow')
                ->where('follower_id', $user->id);
        })
        ->where('id','!=',$user->id)
        ->get();


        return response()->json([
        'users' => $tidakdifollow
        ], 200);
    }


    public function detail_user(Request $request, $username)
    {
        $user = User::where('username', $username)->first();
    
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    
        $follow_status = 'not-following';
        if (Auth::check()) {
            $follow = Follow::where('follower_id', Auth::id())
                ->where('following_id', $user->id)
                ->first();
    
            if ($follow) {
                $follow_status = $follow->is_accepted ? 'following' : 'requested';
            }
        }
    
        $posts = [];
        $posts = $user->posts()->when(
            !$user->is_private || $follow_status === 'following',
            fn($query) => $query->with('post_attachments')->get()->map(fn($post) => [
                'id' => $post->id,
                'caption' => $post->caption,
                'created_at' => $post->created_at,
                'attachments' => $post->post_attachments->map(fn($attachment) => [
                    'id' => $attachment->id,
                    'storage_path' => $attachment->storage_path
                ])
            ])
        );

    
        $posts_count = $user->posts()->count();
        $followers_count = $user->follower()->count();
        $following_count = $user->following()->count();
    
        $response = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'bio' => $user->bio,
            'is_private' => $user->is_private,
            'created_at' => $user->created_at,
            'is_your_account' => Auth::id() === $user->id,
            'following_status' => $follow_status,
            'posts_count' => $posts_count,
            'followers_count' => $followers_count,
            'following_count' => $following_count,
            'posts' => $posts
        ];
    
        return response()->json($response, 200);
    }
    
    
}
