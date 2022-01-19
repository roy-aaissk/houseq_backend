<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\Admin\Auth\LoginController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Api\Admin\UserController;
use Symfony\Component\Console\Question\Question;

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
// 管理画面の機能として実装するのでPrefixにadminをつける
Route::prefix('admin')->group(function() {
    // 認証処理
    Route::prefix('auth')->group(function () {
        Route::post('/login', [LoginController::class, 'login']);
    });

    Route::middleware('auth:api')->group(function () {
        Route::get('/user', function () {
            return Auth::user();
        });

        // Users
        Route::resource('users', UserController::class)->only([
            'index'
        ]);
    });
});

Route::middleware('jwt')->get('index', [QuestionController::class, 'index']);

Route::resource('questions', QuestionController::class)->only([
    'index'
]);

Route::middleware('jwt')->get('/private', function (Request $request) {
    return response()->json([
        "autho_user_id" => $request['auth0_user_id'],
        "message" => "プライベートなエンドポイントへようこそ！これを表示するには有効なIDトークンが必要です。"
    ]);
});








