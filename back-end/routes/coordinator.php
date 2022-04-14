<?php
use Illuminate\Support\Facades\Route;
$this->get('login', 'Auth\AuthController@showLoginForm')->name('coordinator.login');
$this->post('login', 'Auth\AuthController@login')->name('coordinator.loginPost');
$this->any('logout', 'Auth\AuthController@loggedOut')->name('coordinator.logout');
Route::group([
    'middleware' => ['auth:'.COORDINATOR_GUARD],             
], function ($router) {
    Route::get('cor','CoordinatorController@index');
    Route::get('get/{id}','CoordinatorController@show');
    Route::post('new-coordinator','CoordinatorController@store');
    Route::post('search','CoordinatorController@search');
});
