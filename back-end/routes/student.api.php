<?php
use Illuminate\Support\Facades\Route;
Route::group([
], function ($router) {
    Route::get('','StudentController@index');
    Route::get('get/{id}','StudentController@show');
    Route::post('new-student','StudentController@store');
    Route::get('search/{request}', 'StudentController@search');
});
