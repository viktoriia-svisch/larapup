<?php
use Illuminate\Support\Facades\Route;
Route::get('login', 'Auth\AuthController@showLoginForm')->name('admin.login')->middleware('guest:' . ADMIN_GUARD);
Route::post('login', 'Auth\AuthController@login')->name('admin.loginPost')->middleware('guest:' . ADMIN_GUARD);
Route::any('logout', 'Auth\AuthController@loggedOut')->name('admin.logout');
Route::group([
    'middleware' => ['auth:' . ADMIN_GUARD],             
], function ($router) {
    Route::get('dashboard', 'AdminController@dashboard')->name('admin.dashboard');
    Route::get('student', 'StudentController@student')->name('admin.student');
    Route::get('student/create', 'StudentController@createStudent')->name('admin.createStudent');
    Route::post('student/create', 'StudentController@createStudent_post')->name('admin.createStudent_post');
    Route::get('student/profile/{id}', 'StudentController@updateStudent')->name('admin.updateStudent');
    Route::post('student/profile/{id}', 'StudentController@updateStudentPost')->name('admin.updateStudent_post');
    Route::get('semester', 'SemesterController@semester')->name('admin.semester');
    Route::get('semester/create', 'SemesterController@createSemester')->name('admin.createSemester');
    Route::post('semester/create', 'SemesterController@createSemester_post')->name('admin.createSemester_post');
    Route::get('faculty', 'FacultyController@faculty')->name('admin.faculty');
    Route::get('faculty/create', 'FacultyController@createFaculty_semester')->name('admin.createFacultySemester');
    Route::get('faculty/create/{semester}', 'FacultyController@createFaculty')->name('admin.createFaculty');
    Route::post('faculty/create/{semester}', 'FacultyController@createFaculty_post')->name('admin.createFaculty_post');
    Route::post('search-faculty/{semester}/{request}','Admin\FacultyController@searchFaculty');
    Route::get('coordinator/create', 'CoordinatorController@create')->name('admin.createCoordinator');
    Route::post('coordinator/create', 'CoordinatorController@createCoordinator_post')->name('admin.createCoordinator_post');
    Route::get('get/{id}', 'AdminController@show');
    Route::post('new-semester', 'AdminController@store');
    Route::redirect('', 'admin/dashboard');
});
