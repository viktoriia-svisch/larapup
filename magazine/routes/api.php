<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:api')->get('/user', function (Request $request) {
      return $request->user();
  });
Route::post('new-semester','Admin\SemesterController@createSemester');
Route::post('search-faculty/{semester}/{request}','Admin\FacultyController@searchFaculty');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('search/{request}','Student\StudentController@search');
Route::get('searchAll','Student\StudentController@searchAll');
Route::get('searches/{request}','Coordinator\CoordinatorController@search');
