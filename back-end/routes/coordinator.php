<?php
use Illuminate\Support\Facades\Route;
Route::group([
], function ($router) {
    Route::get('cor','CoordinatorController@index');
    Route::get('get/{id}','CoordinatorController@show');
    Route::post('new-coordinator','CoordinatorController@store');
    Route::post('search','CoordinatorController@search');
});
