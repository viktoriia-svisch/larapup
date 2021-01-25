<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get('','AdminController@index');
Route::get('get/{id}','AdminController@show');
Route::post('new-semester','AdmiinController@store');
