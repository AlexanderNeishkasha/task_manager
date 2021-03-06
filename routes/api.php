<?php

use Illuminate\Http\Request;

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

Route::group(['namespace' => 'Api'], function () {
    Route::post('login', 'LoginController@login');

    Route::group(['prefix' => 'projects', 'as' => 'projects.'], function () {
        Route::get('', 'ProjectController@get');
        Route::post('', 'ProjectController@store');
        Route::put('{id}', 'ProjectController@update');
        Route::delete('{id}', 'ProjectController@delete');
    });

    Route::group(['prefix' => 'tasks', 'as' => 'tasks.'], function () {
        Route::post('', 'TaskController@store');
        Route::put('{id}', 'TaskController@update');
        Route::delete('{id}', 'TaskController@delete');
    });
});

