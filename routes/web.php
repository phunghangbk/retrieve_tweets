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
Route::get('gettweets', ['uses' => 'RetrieveTweetsController@getTweets','as' => 'get.gettweets']);
Route::post('savetweets', ['uses' => 'SaveTweets@saveTweets', 'as' => 'post.savetweets']);
Route::get('savesearchinfo', ['uses' => 'SaveSearchInfo@savesearchinfo', 'as' => 'get.savesearchinfo']);
Route::get('/tweets', 'RetrieveTweetsController@tweets');
Route::get('/delete', 'SaveTweets@deleteTweets');