<?php
use Illuminate\Support\Facades\Route;
Route::group([
], function ($router) {
Route::get('','AdminController@index');
Route::get('get/{id}','AdminController@show');
Route::post('new-semester','AdminController@store');
});
