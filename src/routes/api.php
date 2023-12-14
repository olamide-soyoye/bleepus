<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;
use App\Http\Controllers\JobApplicantController;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'throttle:20,1'])->group(function () {
    //logout
    Route::post('auth/logout', [ Api\AuthController::class, 'logout'])->name('api.auth.logout');


    Route::prefix('user')->middleware('throttle:5,1')->group(function () {
        Route::get('profile/{userId?}', [Api\UserController::class, 'getUserProfile'])->name('api.user.profile');
        Route::put('profile', [Api\UserController::class, 'updateUserProfile'])->name('api.update.user.profile');
        Route::post('profile/picture', [Api\UserController::class, 'updateProfilePicture']);
        Route::delete('account', [Api\UserController::class, 'deleteUserAccount'])->name('api.delete.user.profile');
    });

    Route::prefix('job')->middleware('throttle:10,1')->group(function () {
        Route::post('create', [JobListingController::class, 'createJobOffer'])->name('api.job.create');
        Route::post('apply', [JobApplicantController::class, 'applyForJobs'])->name('api.job.apply');
        Route::get('self/retrieve', [JobListingController::class, 'getPostedJobs'])->name('api.job.self.retrieve');
        Route::get('around', [JobListingController::class, 'getJobsWithinProffessionalRange'])->name('api.jobs.around');
        Route::get('appliedto', [JobApplicantController::class, 'getAllJobsAppliedFor'])->name('api.jobs.appliedto');
        Route::get('applicants', [JobApplicantController::class, 'getApplicants'])->name('api.jobs.applicants');
        Route::post('hireorreject/professional', [JobApplicantController::class, 'hireOrRejectProfessionals'])->name('api.hireorreject.job.applicant'); 
    });

    Route::prefix('notifications')->middleware('throttle:20,1')->group(function () {
        Route::get('get', [NotificationController::class, 'getAllNotifications'])->name('api.notifications.get');
        Route::get('show/{notificationId?}', [NotificationController::class, 'showSingleNotification'])->name('api.notifications.show');
        
    });
    
    Route::get('professionals/around', [Api\UserController::class, 'getProfessionalsWithinRange'])
    ->middleware('throttle:10,1')->name('api.professionals.around');


    Route::get('businesses/around', [Api\UserController::class, 'getHealthCareProvidersWithinRange'])
    ->middleware('throttle:10,1')->name('api.businesses.around');
});

Route::get('user/types', [ Api\UserController::class, 'userTypes' ])->middleware('throttle:5,1')->name('api.user.types');
Route::get('/test', [ Api\UserController::class, 'test' ])->middleware('throttle:5,1')->name('api.test');

Route::prefix('auth')->middleware('throttle:5,1')->group(function () { 
    Route::post('/register', [ Api\AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login',[ Api\AuthController::class, 'login' ])->name('api.auth.login');
    Route::get('/forgot-password', [Api\ResetPasswordController::class, 'reset'])->name('password.reset');
    Route::post('/forgot-password', [Api\PasswordResetController::class, '__invoke']);
    // Route::post('/reset-password', [Api\PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::get('/swagger', function () {
    return view('swagger.index');
});
