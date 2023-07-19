<?php
use Illuminate\Support\Facades\Route;
Route::get('login', 'Auth\AuthController@showLoginForm')->name('student.login')->middleware('guest:' . STUDENT_GUARD);
Route::post('login', 'Auth\AuthController@login')->name('student.loginPost')->middleware('guest:' . STUDENT_GUARD);
Route::any('logout', 'Auth\AuthController@loggedOut')->name('student.logout');
Route::group([
    'middleware' => ['auth:' . STUDENT_GUARD],             
], function ($router) {
    Route::get('dashboard', 'StudentController@dashboard')->name('student.dashboard');
    Route::redirect('', 'student/dashboard');
    Route::get('faculty', 'FacultyController@faculty')->name('student.faculty');
    Route::get('faculty/{faculty_id}/{semester_id}/dashboard', 'FacultyController@facultyDetailDashboard')->name('student.faculty.dashboard');
    Route::get('faculty/{faculty_id}/{semester_id}/members', 'FacultyController@facultyDetailMember')->name('student.faculty.members');
    Route::get('faculty/{faculty_id}/{semester_id}/article', 'FacultyController@facultyDetailArticle')->name('student.faculty.article');
    Route::post('faculty/{faculty_id}/{semester_id}/article_files', 'ArticleController@articleFilePost')->name('student.faculty.articleFiles_post');
    Route::post('faculty/{faculty_id}/{semester_id}/article_files/delete', 'ArticleController@articleFileDelete')->name('student.faculty.articleFiles_delete');
    Route::get('faculty/{faculty_id}/{semester_id}/article_files/download/{article_file_id}', 'ArticleController@articleFileDownload')->name('student.faculty.articleFiles_download');
    Route::post('faculty/{faculty_id}/{semester_id}/comment', 'CommentController@commentPost')->name('student.faculty.comment_post');
    Route::get("faculty/{faculty_id}/{semester_id}/comment/{comment_id}/download/{type}", "CommentController@downloadAttachmentComment")
        ->name("student.faculty.comment_attachmentDownload");
    Route::get('article', 'StudentController@article')->name('student.article');
    Route::get('manage/{id}', 'StudentController@updateStudent')->name('student.manageAccount');
    Route::post('manage/{id}', 'StudentController@updateStudentPost')->name('student.manageAccount_post');
    Route::get('get/{id}', 'StudentController@show');
    Route::post('new-student', 'StudentController@store');
});
