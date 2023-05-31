<?php
use Illuminate\Support\Facades\Route;
Route::get('login', 'Auth\AuthController@showLoginForm')->name('coordinator.login')->middleware('guest:'.COORDINATOR_GUARD);;
Route::post('login', 'Auth\AuthController@login')->name('coordinator.loginPost')->middleware('guest:'.COORDINATOR_GUARD);;
Route::any('logout', 'Auth\AuthController@loggedOut')->name('coordinator.logout');
Route::group([
    'middleware' => ['auth:'.COORDINATOR_GUARD ],          
], function ($router) {
    Route::get('dashboard', 'CoordinatorController@dashboard')->name('coordinator.dashboard');
    Route::get('cor','CoordinatorController@index');
    Route::get('get/{id}','CoordinatorController@show');
    Route::post('new-coordinator','CoordinatorController@store');
    Route::get('semester', 'CoordinatorController@CoordinatorSemester')->name('coordinator.manageSemester');
    Route::get('coordinator-semester-faculty','CoordinatorController@CoordinatorSemesterFaculty') ->name('coordinator.manageFaculty');
    Route::get('coordinator-semester-faculty/{semester}', 'CoordinatorController@chooseSemesterFaculty')->name('coordinator.chooseSemesterFaculty');
    Route::get('coordinatorSemesterFaculty/addStudent/{facultysemester}', 'CoordinatorController@addStudentFaculty')->name('coordinator.addStudentFaculty');
    Route::post('coordinatorSemesteFaculty/addStudent/{facultysemester}/{student}', 'CoordinatorController@addStudentFaculty_post')->name('coordinator.addStudentFaculty_post');
    Route::post('new-faculty','CoordinatorController@storeFaculty');
    Route::get('coordinator', 'CoordinatorController@Coordinator')->name('coordinator.coordinator');
    Route::get('faculty', 'FacultyController@faculty')->name('coordinator.faculty');
    Route::get('manage/{id}', 'CoordinatorController@updateCoordinator')->name('coordinator.manageAccount');
    Route::post('manage/{id}', 'CoordinatorController@updateCoordinatorPost')->name('coordinator.manageAccount_post');
    Route::get('faculty/{faculty_id}/{semester_id}/dashboard','FacultyController@facultyDetailDashboard')
        ->name('coordinator.faculty.dashboard');
    Route::get('faculty/{faculty_id}/{semester_id}/published','FacultyController@facultyDetailListPublished')
        ->name('coordinator.faculty.listPublished');
    Route::get('faculty/{faculty_id}/{semester_id}/published/{published_id}','FacultyController@facultyDetailPublished')
        ->name('coordinator.faculty.published');
    Route::get('faculty/{faculty_id}/{semester_id}/students','FacultyController@facultyDetailStudents')
        ->name('coordinator.faculty.students');
    Route::get('faculty/{faculty_id}/{semester_id}/articles','FacultyController@facultyDetailListArticle')
        ->name('coordinator.faculty.listArticle');
    Route::get('faculty/{faculty_id}/{semester_id}/articles/{article_file_id}/download','ArticleController@articleFileDownload')
        ->name('coordinator.faculty.listArticle.download');
    Route::get('faculty/{faculty_id}/{semester_id}/articles/{article_id}/discussion','ArticleController@facultyArticleDiscussion')
        ->name('coordinator.faculty.article');
    Route::post('faculty/{faculty_id}/{semester_id}/article/{article_id}/discussion', 'ArticleController@commentPost')
        ->name('coordinator.faculty.comment_post');
    Route::get('faculty/{faculty_id}/{semester_id}/articles/{article_id}/publish','FacultyController@facultyDetailArticlePublish')
        ->name('coordinator.faculty.article.publish');
    Route::post('faculty/{faculty_id}/{semester_id}/articles/{article_id}/publish','FacultyController@facultyDetailArticlePublish_Post')
        ->name('coordinator.faculty.article.publishPost');
    Route::get('faculty/{faculty_id}/{semester_id}/settings','FacultyController@facultyDetailSettings')
        ->name('coordinator.faculty.settings');
    Route::post('faculty/{faculty_id}/{semester_id}/settings', 'FacultyController@facultyDetailSettingPost')
        ->name('coordinator.faculty.settingPost');
    Route::get('faculty/add-coordinator', 'CoordinatorController@addToFaculty_index')->name('coordinator.faculty.addCoorToFaculty');
    Route::get('faculty/add-coordinator/coordinator/{coordinator}/faculty/{faculty}/semester/{semester}', 'CoordinatorController@addToFaculty')->name('coordinator.faculty.addToFaculty.addCoorToFaculty_post');
    Route::post('faculty/add-coordinator/fetch', 'CoordinatorController@fetch')->name('coordinator.faculty.addToFaculty.fetch');
    Route::post('faculty/add-coordinator/fetchCoor', 'CoordinatorController@fetchCoor')->name('coordinator.faculty.addToFaculty.fetchCoor');
});
