<?php
use App\Helpers\StorageHelper;
use App\Models\PublishImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
Route::get('/', function () {
    return redirect(\route('student.login'));
});
Route::get('resources/publish/{idFacultySemester}/{idPublish}/{filename}',
    function ($idFacultySemester, $idPublish, $filename) {
        $publish = PublishImage::with("publish")
            ->whereHas("publish", function (Builder $builder) use ($idPublish, $idFacultySemester) {
                $builder->where("id", $idPublish)->whereHas("article.faculty_semester", function (Builder $builder) use ($idFacultySemester) {
                    $builder->where("id", $idFacultySemester);
                });
            })
            ->where("image_path", $filename)->first();
        if (!$publish) {
            abort(404);
        }
        try {
            $file = StorageHelper::getPublishFilePath($idFacultySemester, $idPublish, $filename);
            return Image::make(storage_path("app/" . $file))->response();
        } catch (Exception $exception) {
            dd($exception);
            abort(404);
        }
        return;
    })->name("resources.publishes");
Route::group([
    'prefix' => 'guest',
    'namespace' => 'Guest'
], function ($router) {
    Route::get('login', 'Auth\AuthController@showLoginForm')->name('guest.login')->middleware('guest:' . GUEST_GUARD);
    Route::post('login', 'Auth\AuthController@login')->name('guest.loginPost')->middleware('guest:' . GUEST_GUARD);
    Route::any('logout', 'Auth\AuthController@loggedOut')->name('guest.logout');
    Route::group([
        'middleware' => ['auth:' . GUEST_GUARD],             
    ], function () {
        Route::get('dashboard', 'GuestController@dashboard')->name('guest.dashboard');
    });
});
