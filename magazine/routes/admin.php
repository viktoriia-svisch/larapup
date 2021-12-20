<?php
use Illuminate\Support\Facades\Route;
Route::get('login', 'Auth\AuthController@showLoginForm')->name('admin.login')->middleware('guest:'.ADMIN_GUARD);
Route::post('login', 'Auth\AuthController@login')->name('admin.loginPost')->middleware('guest:'.ADMIN_GUARD);
Route::any('logout', 'Auth\AuthController@loggedOut')->name('admin.logout');
Route::group([
    'middleware' => ['auth:'.ADMIN_GUARD],             
], function ($router) {
    Route::redirect('','dashboard');
    Route::get('dashboard', 'AdminController@dashboard')->name('admin.dashboard');
    Route::get('semester', 'AdminController@semester')->name('admin.semester');
    Route::get('semester/create', 'AdminController@createSemester')->name('admin.createSemester');
    Route::get('get/{id}','AdminController@show');
    Route::post('new-semester','AdminController@store');
});
