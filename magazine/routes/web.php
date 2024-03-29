<?php
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return redirect(\route('student.login'));
});
Route::group([
    'middleware' => ['authorized']
], function ($router) {
    Route::get('publishes/{faculty_id}/semester/{semester_id}', 'GeneralController@listPublished')->name('shared.listPublishes');
    Route::get('publishes/{faculty_id}/semester/{semester_id}/article/{id_publish}', 'GeneralController@published')->name('shared.publish');
});
Route::group([
    'prefix' => 'guest',
    'namespace' => 'Guest'
], function ($router) {
    Route::get('login', 'Auth\AuthController@showLoginForm')->name('guest.login')->middleware('guest:' . GUEST_GUARD);
    Route::post('login', 'Auth\AuthController@login')->name('guest.loginPost')->middleware('guest:' . GUEST_GUARD);
    Route::any('logout', 'Auth\AuthController@loggedOut')->name('guest.logout');
    Route::group([
        'middleware' => ['auth:' . GUEST_GUARD],             
    ], function () {
        Route::get('dashboard', 'GuestController@dashboard')->name('guest.dashboard');
    });
});
