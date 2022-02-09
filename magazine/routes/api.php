<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:api')->get('/user', function (Request $request) {
      return $request->user();
  });
Route::post('new-semester','Admin\AdminController@createSemester');
Route::post('new-faculty','Admin\AdminController@createFaculty');
Route::post('search-faculty/{id}','Admin\AdminController@searchFaculty');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('search/{request}','Student\StudentController@search');
Route::get('searchAll','Student\StudentController@searchAll');
Route::get('searches/{request}','Coordinator\CoordinatorController@search');
