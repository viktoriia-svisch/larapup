<?php
use Illuminate\Support\Facades\Route;
$this->get('login', 'Auth\AuthController@showLoginForm')->name('student.login');
$this->post('login', 'Auth\AuthController@login')->name('student.loginPost');
$this->any('logout', 'Auth\AuthController@loggedOut')->name('student.logout');
Route::group([
    'middleware' => ['auth.student'],             
], function ($router) {
    Route::get('dashboard', 'StudentController@dashboard')->name('student.dashboard');
    Route::get('article', 'StudentController@article')->name('student.article');
    Route::get('','StudentController@index');
    Route::get('get/{id}','StudentController@show');
    Route::post('new-student','StudentController@store');
});
