<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\BackupsController;
use App\Http\Controllers\CouplesController;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\FamilyActionsController;
use App\Http\Controllers\UserMarriagesController;
use App\Http\Controllers\Auth\ChangePasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/', [UsersController::class, 'search']);

    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/status', [UsersController::class, 'updateStatus'])
    ->name('users.update_status')
    ->middleware('auth');

    Route::controller(HomeController::class)->group(function () {
        Route::get('home', 'index')->name('home');
        Route::get('profile', 'index')->name('profile');
    });

    Route::controller(FamilyActionsController::class)->group(function () {
        Route::post('family-actions/{user}/set-family', 'setFamily')->name('family-actions.set-family');
        Route::get('family-actions/search-family', 'searchFamily')->name('family-actions.search-family');
        Route::get('family-actions/search-people', 'searchPeople')->name('family-actions.search-people');
        Route::get('family-actions/search-couples', 'searchCouples')->name('family-actions.search-couples');
        Route::post('family-actions/{user}/set-parent-family', 'setParentFamily')->name('family-actions.set-parent-family');
        Route::get('family-actions/{user}/{family}/remove-parent', 'removeParentFamily')->name('family-actions.remove-parent-family');
        Route::post('family-actions/{user}/add-child-family', 'addChildFamily')->name('family-actions.add-child-family');
        Route::get('family-actions/{user}/{family}/remove-child-family', 'removeChildFamily')->name('family-actions.remove-child-family');
        Route::get('family-actions/get-sub-families', 'getSubFamilies')->name('family-actions.get-sub-families');
        Route::post('family-actions/{user}/set-father', 'setFather')->name('family-actions.set-father');
        Route::post('family-actions/{user}/set-mother', 'setMother')->name('family-actions.set-mother');
        Route::post('family-actions/{user}/add-child', 'addChild')->name('family-actions.add-child');
        Route::post('family-actions/{user}/add-wife', 'addWife')->name('family-actions.add-wife');
        Route::post('family-actions/{user}/add-husband', 'addHusband')->name('family-actions.add-husband');
        Route::post('family-actions/{user}/set-parent', 'setParent')->name('family-actions.set-parent');
    });

    Route::controller(UsersController::class)->group(function () {
        Route::get('profile-search', 'search')->name('users.search');
        Route::get('users/{user}', 'show')->name('users.show');
        Route::get('users/{user}/edit', 'edit')->name('users.edit');
        Route::patch('users/{user}', 'update')->name('users.update');
        Route::get('users/{user}/chart', 'chart')->name('users.chart');
        Route::get('users/{user}/tree', 'tree')->name('users.tree');
        Route::get('users/{user}/death', 'death')->name('users.death');
        Route::patch('users/{user}/photo-upload', 'photoUpload')->name('users.photo-upload');
        Route::delete('users/{user}', 'destroy')->name('users.destroy');
    });

    Route::controller(FamilyController::class)->group(function () {
        Route::get('families', 'index')->name('families.index');
        Route::get('families/create', 'create')->name('families.create');
        Route::get('families/search', 'search')->name('families.search');
        Route::post('families', 'store')->name('families.store');
        Route::get('families/{family}', 'show')->name('families.show');
        Route::get('families/{family}/edit', 'edit')->name('families.edit');
        Route::put('families/{family}', 'update')->name('families.update');
        Route::delete('families/{family}', 'destroy')->name('families.destroy');
    });

    Route::get('users/{user}/marriages', [UserMarriagesController::class, 'index'])->name('users.marriages');

    Route::get('birthdays', [BirthdayController::class, 'index'])->name('birthdays.index');
    /**
     * Couple/Marriages Routes
     */
    Route::controller(CouplesController::class)->group(function () {
        Route::get('couples/{couple}', 'show')->name('couples.show');
        Route::get('couples/{couple}/edit', 'edit')->name('couples.edit');
        Route::patch('couples/{couple}', 'update')->name('couples.update');
    });

    Route::controller(ChangePasswordController::class)->group(function () {
        Route::get('password/change', 'show')->name('password_change');
        Route::post('password/change', 'update')->name('password_update');
    });
});

Route::get('/reload-captcha', function () {
    return response()->json(['captcha' => captcha_img('flat')]);
});

/**
 * Admin only routes
 */
Route::group(['middleware' => 'admin'], function () {
    /**
     * Backup Restore Database Routes
     */
    Route::controller(BackupsController::class)->group(function () {
        Route::post('backups/upload', 'upload')->name('backups.upload');
        Route::post('backups/{fileName}/restore', 'restore')->name('backups.restore');
        Route::get('backups/{fileName}/dl', 'download')->name('backups.download');
    });
    Route::resource('backups', BackupsController::class);
});
