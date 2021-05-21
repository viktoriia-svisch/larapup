<?php
use Illuminate\Support\Facades\Route;
Route::redirect('', \route('student.login'));
Route::group([
    'middleware' => ['auth:'.GUEST_GUARD],             
], function ($router) {
});
