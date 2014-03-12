<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::pattern('id', '[0-9]+');

Route::get('series', 'SeriesController@index');

Route::get('phpinfo', function(){
    phpinfo();
});

/* Legacy API Calls */
Route::any('api/GetSeries.php', 'LegacyApiController@getSeries');
Route::any('api/{apikey}/series/{id}/en.xml', 'LegacyApiController@getSeriesBaseRecord');
Route::any('api/{apikey}/series/{id}/all/en.xml', 'LegacyApiController@getSeriesAll');


// Image Route
Route::get('images/{type}/{data_id}/{id}.jpg', function($type, $data_id, $id){
    $image = Images::find($id);
    if($image->data_id == $data_id && $image->type == $type)
    {
        return Response::make(MongoStor::get($image->image_id), "200")->header('Content-Type', 'image/jpg');
    }
    else
    {
        App::abort(404);
    }
});

Route::get( 'user/create',                 'UserController@create');
Route::post('user',                        'UserController@store');
Route::get( 'user/login',                  'UserController@login');
Route::post('user/login',                  'UserController@do_login');
Route::get( 'user/confirm/{code}',         'UserController@confirm');
Route::get( 'user/forgot_password',        'UserController@forgot_password');
Route::post('user/forgot_password',        'UserController@do_forgot_password');
Route::get( 'user/reset_password/{token}', 'UserController@reset_password');
Route::post('user/reset_password',         'UserController@do_reset_password');
Route::get( 'user/logout',                 'UserController@logout');
