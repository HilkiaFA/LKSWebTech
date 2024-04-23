<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('v1/auth/register', [AuthController::class, 'register']);

Route::post('v1/auth/login', [AuthController::class, 'login']);

Route::post('v1/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);

Route::post('v1/posts', [PostController::class, 'create_post'])->middleware(['auth:sanctum']);

Route::delete('v1/posts/{id}', [PostController::class, 'delete_post'])->middleware(['auth:sanctum']);

Route::get('v1/posts', [PostController::class, 'get_allpost'])->middleware(['auth:sanctum']);

Route::post('v1/users/{username}/follow', [FollowController::class, 'follow_user'])->middleware(['auth:sanctum']);

Route::delete('v1/users/{username}/unfollow', [FollowController::class, 'unfollow_user'])->middleware(['auth:sanctum']);

Route::get('v1/following', [FollowController::class, 'user_following'])->middleware(['auth:sanctum']);

Route::put('v1/users/{username}/accept', [FollowController::class, 'accept_follow'])->middleware(['auth:sanctum']);

Route::get('v1/users/{username}/followers', [FollowController::class, 'user_follower'])->middleware(['auth:sanctum']);

Route::get('v1/users', [FollowController::class, 'get_user'])->middleware(['auth:sanctum']);

Route::get('v1/users/{username}', [FollowController::class, 'detail_user'])->middleware(['auth:sanctum']);
