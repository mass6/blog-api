<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

use App\User;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');


// Test route for fecthing a test token
Route::get('request-token/{id?}', function($id = null) {
    $PasswordGrantClient = DB::table('oauth_clients')->where('name', 'Laravel Password Grant Client')->get()->first();

    if (! is_null($id)) {
        $user = User::find($id);
    }
    else {
        $user = User::first();
    }

    $http = new GuzzleHttp\Client;
    $response = $http->post('http://blog-api.app/oauth/token', [
        'form_params' => [
            'grant_type' => 'password',
            'client_id' => $PasswordGrantClient->id,
            'client_secret' => $PasswordGrantClient->secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => '',
        ],
    ]);

    return json_decode((string) $response->getBody(), true);
});