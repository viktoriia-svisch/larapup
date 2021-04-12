<?php
use Illuminate\Support\Facades\Route;
$this->get('login', 'Auth\AuthController@showLoginForm')->name('student.login');
Route::group([
], function ($router) {
    Route::get('dashboard', 'StudentController@dashboard')->name('student.dashboard');
    Route::get('','StudentController@index');
    Route::get('get/{id}','StudentController@show');
    Route::post('new-student','StudentController@store');
});
