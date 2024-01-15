<?php
use Illuminate\Support\Facades\Route;
Route::get('login', 'Auth\AuthController@showLoginForm')->name('coordinator.login')->middleware('guest:' . COORDINATOR_GUARD);;
Route::post('login', 'Auth\AuthController@login')->name('coordinator.loginPost')->middleware('guest:' . COORDINATOR_GUARD);;
Route::any('logout', 'Auth\AuthController@loggedOut')->name('coordinator.logout');
Route::group([
    'middleware' => ['auth:' . COORDINATOR_GUARD],          
], function ($router) {
    Route::get('dashboard', 'CoordinatorController@dashboard')->name('coordinator.dashboard');
    Route::get('semester', 'SemesterController@listSemester')->name('coordinator.manageSemester');
    Route::get('semester/{semester_id}', 'SemesterController@semesterDetail')->name('coordinator.semester.detail');
    Route::post('new-faculty', 'CoordinatorController@storeFaculty');
    Route::get('coordinator', 'CoordinatorController@Coordinator')->name('coordinator.coordinator');
    Route::get('faculty', 'FacultyController@faculty')->name('coordinator.faculty');
    Route::get('manage/{id}', 'CoordinatorController@updateCoordinator')->name('coordinator.manageAccount');
    Route::post('manage/{id}', 'CoordinatorController@updateCoordinatorPost')->name('coordinator.manageAccount_post');
    Route::get('faculty/{faculty_id}/{semester_id}/dashboard', 'FacultyController@facultyDetailDashboard')
        ->name('coordinator.faculty.dashboard');
    Route::get('faculty/{faculty_id}/{semester_id}/backups', 'FacultyController@facultyBackupsDownload')
        ->name('coordinator.faculty.backupsDownload');
    Route::get('faculty/{faculty_id}/{semester_id}/members', 'FacultyController@facultyDetailMember')
        ->name('coordinator.faculty.students');
    Route::get('faculty/{faculty_id}/{semester_id}/articles', 'FacultyController@facultyDetailListArticle')
        ->name('coordinator.faculty.listArticle');
    Route::get('faculty/{faculty_id}/{semester_id}/articles/{article_file_id}/download', 'ArticleController@articleFileDownload')
        ->name('coordinator.faculty.listArticle.download');
    Route::get('faculty/{faculty_id}/{semester_id}/articles/{article_id}/discussion', 'ArticleController@facultyArticleDiscussion')
        ->name('coordinator.faculty.article');
    Route::post('faculty/{faculty_id}/{semester_id}/article/{article_id}/discussion', 'ArticleController@commentPost')
        ->name('coordinator.faculty.comment_post');
    Route::get('faculty/{faculty_id}/{semester_id}/articles/{article_id}/publish', 'FacultyController@facultyDetailArticlePublish')
        ->name('coordinator.faculty.article.publish');
    Route::post('faculty/{faculty_id}/{semester_id}/articles/{article_id}/publish', 'FacultyController@facultyDetailArticlePublish_Post')
        ->name('coordinator.faculty.article.publishPost');
    Route::get('faculty/{faculty_id}/{semester_id}/settings', 'FacultyController@facultyDetailSettings')
        ->name('coordinator.faculty.settings');
    Route::post('faculty/{faculty_id}/{semester_id}/settings', 'FacultyController@facultyDetailSettingPost')
        ->name('coordinator.faculty.settingPost');
    Route::get('faculty/add-coordinator', 'CoordinatorController@addToFaculty_index')
        ->name('coordinator.faculty.addCoorToFaculty');
    Route::get('faculty/add-coordinator/coordinator/{coordinator}/faculty/{faculty}/semester/{semester}', 'CoordinatorController@addToFaculty')
        ->name('coordinator.faculty.addToFaculty.addCoorToFaculty_post');
    Route::get('faculty/remove-coordinator/coordinator/{coordinator}/faculty/{faculty}/semester/{semester}', 'CoordinatorController@removeToFaculty')
        ->name('coordinator.faculty.addToFaculty.removeCoorFromFaculty_post');
    Route::post('faculty/add-coordinator/fetch', 'CoordinatorController@fetch')
        ->name('coordinator.faculty.addToFaculty.fetch');
    Route::post('faculty/add-coordinator/fetchCoor', 'CoordinatorController@fetchCoor')
        ->name('coordinator.faculty.addToFaculty.fetchCoor');
    Route::group(['middleware' => ['coordinator.master']], function ($route) {
        Route::post('semester/{semester_id}', 'SemesterController@semesterDetail_removeFaculty')
            ->name('coordinator.semester.detail.remove');
        Route::get('semester/{semester_id}/add-faculty', 'SemesterController@semesterDetail_addFaculty')
            ->name('coordinator.semester.detail.add');
        Route::post('semester/{semester_id}/add-faculty', 'SemesterController@semesterDetail_addFaculty_post')
            ->name('coordinator.semester.detail.addPost');
        Route::get('faculty/{faculty_id}/{semester_id}/members/manage-student', 'FacultyController@facultyDetailMember_manage')
            ->name('coordinator.faculty.students.manage');
        Route::get('faculty/{faculty_id}/{semester_id}/members/manage-student/{student_id}/add', 'FacultyController@facultyDetailMember_manage_add')
            ->name('coordinator.faculty.students.manage.add');
        Route::get('faculty/{faculty_id}/{semester_id}/members/manage-student/{student_id}/remove', 'FacultyController@facultyDetailMember_manage_remove')
            ->name('coordinator.faculty.students.manage.remove');
        Route::get('faculty/{faculty_id}/{semester_id}/members/manage-coordinator', 'FacultyController@facultyDetailMemberCoordinator_manage')
            ->name('coordinator.faculty.coordinators.manage');
        Route::get('faculty/{faculty_id}/{semester_id}/members/manage-coordinator/{coordinator_id}/add', 'FacultyController@facultyDetailMemberCoordinator_manage_add')
            ->name('coordinator.faculty.coordinators.manage.add');
        Route::get('faculty/{faculty_id}/{semester_id}/members/manage-coordinator/{coordinator_id}/remove', 'FacultyController@facultyDetailMemberCoordinator_manage_remove')
            ->name('coordinator.faculty.coordinators.manage.remove');
    });
});
