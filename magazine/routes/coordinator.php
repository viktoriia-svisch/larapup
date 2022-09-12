<?php
use Illuminate\Support\Facades\Route;
Route::get('login', 'Auth\AuthController@showLoginForm')->name('coordinator.login')->middleware('guest:'.COORDINATOR_GUARD);;
Route::post('login', 'Auth\AuthController@login')->name('coordinator.loginPost')->middleware('guest:'.COORDINATOR_GUARD);;
Route::any('logout', 'Auth\AuthController@loggedOut')->name('coordinator.logout');
Route::group([
    'middleware' => ['auth:'.COORDINATOR_GUARD ],          
], function ($router) {
    Route::get('dashboard', 'CoordinatorController@dashboard')->name('coordinator.dashboard');
    Route::get('cor','CoordinatorController@index');
    Route::get('get/{id}','CoordinatorController@show');
    Route::post('new-coordinator','CoordinatorController@store');
    Route::post('new-faculty','CoordinatorController@storeFaculty');
    Route::get('search','CoordinatorController@searchFaculty')->name('coordinator.coordinator');
});
