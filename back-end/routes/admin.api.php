<?php
use Illuminate\Support\Facades\Route;
Route::group([
    'middleware' => ['auth:'.ADMIN_GUARD],             
], function ($router) {
Route::get('','AdminController@index');
Route::get('get/{id}','AdminController@show');
Route::post('new-semester','AdminController@store');
});
