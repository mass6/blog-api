<?php

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

// Public Routes
use Illuminate\Http\Request;

Route::get('/posts', 'PostsController@index');
Route::get('/posts/{post}', 'PostsController@show');


// Protected Routes
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('posts', 'PostsController@store');
    Route::patch('posts/{post}', 'PostsController@update');
    Route::delete('posts/{post}', 'PostsController@destroy');
    Route::post('posts/{post}/comments', 'PostsController@comment');
});
