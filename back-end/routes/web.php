<?php
use Illuminate\Support\Facades\Route;
$this->get('guest/login', 'Guest\Auth\AuthController@showLoginForm')->name('guest.login');
Route::group([
    'middleware' => ['auth:'.GUEST_GUARD],             
], function ($router) {
});
