<?php
use Illuminate\Support\Facades\Route;
Route::get('login', 'Auth\AuthController@showLoginForm')->name('student.login')->middleware('guest:'.STUDENT_GUARD);
Route::post('login', 'Auth\AuthController@login')->name('student.loginPost')->middleware('guest:'.STUDENT_GUARD);
Route::any('logout', 'Auth\AuthController@loggedOut')->name('student.logout');
Route::group([
    'middleware' => ['auth:'.STUDENT_GUARD],             
], function ($router) {
    Route::get('dashboard', 'StudentController@dashboard')->name('student.dashboard');
    Route::redirect('','student/dashboard');
    Route::get('faculty', 'FacultyController@faculty')->name('student.faculty');
    Route::get('faculty/{id}/{semester}/dashboard', 'FacultyController@facultyDetailDashboard')->name('student.faculty.detail');
    Route::get('faculty/{id}/{semester}/members', 'FacultyController@facultyDetailMember')->name('student.faculty.members');
    Route::get('faculty/{id}/{semester}/article', 'FacultyController@facultyDetailArticle')->name('student.faculty.article');
    Route::post('faculty/{id}/{semester}/article_files', 'FacultyController@articleFilePost')->name('student.faculty.articleFiles_post');
    Route::get('article', 'StudentController@article')->name('student.article');
    Route::get('manage/{id}', 'StudentController@updateStudent')->name('student.manageAccount');
    Route::post('manage/{id}', 'StudentController@updateStudentPost')->name('student.manageAccount_post');
    Route::get('get/{id}','StudentController@show');
    Route::post('new-student','StudentController@store');
});
