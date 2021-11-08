<?php
/**
 * Created by PhpStorm.
 * User: Onaya
 * Date: 2/16/2019
 * Time: 4:46 PM
 */

use Illuminate\Support\Facades\Route;

/**
 * Middleware Authorize for Admin
 */
Route::group([
    'middleware' => ['auth:'.ADMIN_GUARD],             //OPTIONAL MIDDLEWARE IMPLEMENTATION
], function ($router) {

//    Route::post('login', 'AuthController@login');
//    Route::post('logout', 'AuthController@logout');
//    Route::post('refresh', 'AuthController@refresh');
//    Route::post('me', 'AuthController@me');
    Route::get('','AdminController@index');
    Route::get('get/{id}','AdminController@show');
    Route::post('new-semester','AdminController@store');
});
