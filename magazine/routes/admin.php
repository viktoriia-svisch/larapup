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
    Route::get('semester/{activeSemester}', 'SemesterController@chooseSemester')->name('admin.infoSemester');
    Route::get('faculty', 'FacultyController@faculty')->name('admin.faculty');
    Route::post('faculty/create', 'FacultyController@createFaculty_post')->name('admin.createFaculty_post');
    Route::post('search-faculty/{semester}/{request}','Admin\FacultyController@searchFaculty');
    Route::get('faculty/choose-semester', 'FacultyController@chooseSemester')->name('admin.chooseSemester');
    Route::get('faculty/choose-semester/{semester}', 'FacultyController@chooseSemesterFaculty')->name('admin.chooseSemesterFaculty');
    Route::post('faculty/choose-semester/{semester}/{faculty}', 'FacultyController@addSemesterFaculty_post')->name('admin.addSemesterFaculty');
    Route::get('faculty/add-student/{facultysemester}', 'FacultyController@addStudentFaculty')->name('admin.addStudentFaculty');
    Route::post('faculty/add-student/{facultysemester}/{student}', 'FacultyController@addStudentFaculty_post')->name('admin.addStudentFaculty_post');
    Route::get('faculty/add-coordinator', 'CoordinatorController@addToFaculty_index')
        ->name('admin.addCoorToFaculty');
    Route::get('faculty/add-coordinator/coordinator/{coordinator}/faculty/{faculty}/semester/{semester}', 'CoordinatorController@addToFaculty')
        ->name('admin.addToFaculty.addCoorToFaculty_post');
    Route::get('faculty/remove-coordinator/coordinator/{coordinator}/faculty/{faculty}/semester/{semester}', 'CoordinatorController@removeToFaculty')
        ->name('admin.addToFaculty.removeCoorFromFaculty_post');
    Route::post('faculty/add-coordinator/fetch', 'CoordinatorController@fetch')
        ->name('admin.addToFaculty.fetch');
    Route::post('faculty/add-coordinator/fetchCoor', 'CoordinatorController@fetchCoor')
        ->name('admin.addToFaculty.fetchCoor');
    Route::get('coordinator', 'CoordinatorController@coordinator')->name('admin.coordinator');
    Route::get('coordinator/create', 'CoordinatorController@create')->name('admin.createCoordinator');
    Route::post('coordinator/create', 'CoordinatorController@createCoordinator_post')->name('admin.createCoordinator_post');
    Route::get('coordinator/profile/{id}', 'CoordinatorController@updateCoordinator')->name('admin.updateCoordinator');
    Route::post('coordinator/profile/{id}', 'CoordinatorController@updateCoordinatorPost')->name('admin.updateCoordinator_post');
    Route::get('get/{id}', 'AdminController@show');
    Route::post('new-semester', 'AdminController@store');
    Route::redirect('', 'admin/dashboard');
});
