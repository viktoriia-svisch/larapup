<?php
use Illuminate\Support\Facades\Route;
Route::group([
], function ($router) {
    Route::get('cor','CoordinatorController@index');
    Route::get('get/{id}','CoordinatorController@show');
    Route::post('new','CoordinatorController@store');
});
