<?php
use Illuminate\Http\Request;
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('search/{request}','Student\StudentController@search');
Route::get('searchAll','Student\StudentController@searchAll');
Route::get('searches/{request}','Coordinator\CoordinatorController@search');
