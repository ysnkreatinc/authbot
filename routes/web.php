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

Auth::routes();

Route::get('login/facebook', 'Auth\LoginController@redirectToProvider')->name('login.facebook');
Route::get('login/facebook/callback', 'Auth\LoginController@handleProviderCallback');
Route::get('/pages', 'MainbotController@showListPages')->name('listpages');
Route::get('/done', 'MainbotController@pageLinked')->name('done');
Route::get('/link/{page_id}/{page_name}/{page_token}', 'MainbotController@link')->name('link');

Route::get("/trivia", "MainbotController@receive")->middleware("verify");
//where Facebook sends messages to. No need to attach the middleware to this because the verification is via GET
Route::post("/trivia", "MainbotController@receive");


//Route::get('/getToken/{pageId}', 'MainbotController@getToken'); //112544056983204

Route::get('/logout', function(){
    Auth::logout();
});


Route::get('/infos', 'MainbotController@getInformation');
Route::get('/storesession', 'MainbotController@storesession');