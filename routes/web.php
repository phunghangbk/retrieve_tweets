<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::post('gettweets', ['uses' => 'RetrieveTweetsController@getTweets','as' => 'post.gettweets']);
Route::post('savetweets', ['uses' => 'SaveTweets@saveTweets', 'as' => 'post.savetweets']);
Route::post('savesearchinfo', ['uses' => 'RetrieveTweetsController@saveTweets', 'as' => 'post.savesearchinfo']);
Route::get('/tweets', 'RetrieveTweetsController@tweets');