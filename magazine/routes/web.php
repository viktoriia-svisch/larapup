<?php
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return redirect(\route('student.login'));
});
Route::get('guest/login', 'Auth\AuthController@showLoginForm')->name('guest.login')->middleware('guest:'.GUEST_GUARD);
Route::post('guest/login', 'Auth\AuthController@login')->name('guest.loginPost')->middleware('guest:'.GUEST_GUARD);
Route::any('guest/logout', 'Auth\AuthController@loggedOut')->name('guest.logout');
Route::group([
    'middleware' => ['auth:'.GUEST_GUARD],             
    'prefix' => 'guest',
    'namespace' => 'Guest'
], function ($router) {
    Route::get('dashboard', 'GuestController@dashboard')->name('guest.dashboard');
});
