<?php
use Illuminate\Support\Facades\Route;
Route::get('login', 'Auth\AuthController@showLoginForm')->name('student.login');
Route::post('login', 'Auth\AuthController@login')->name('student.loginPost');
Route::any('logout', 'Auth\AuthController@loggedOut')->name('student.logout');
Route::group([
    'middleware' => ['auth:'.STUDENT_GUARD],             
], function ($router) {
    Route::get('dashboard', 'StudentController@dashboard')->name('student.dashboard');
    Route::get('article', 'StudentController@article')->name('student.article');
    Route::get('','StudentController@index');
    Route::get('get/{id}','StudentController@show');
    Route::post('new-student','StudentController@store');
});
