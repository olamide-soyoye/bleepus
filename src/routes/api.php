<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;
use App\Http\Controllers\DashboardController;
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

// Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
Route::middleware(['auth:sanctum'])->group(function () {
    //logout
    Route::post('auth/logout', [ Api\AuthController::class, 'logout'])->name('api.auth.logout');


    // Route::prefix('user')->middleware('throttle:5,1')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('profile/{userId?}', [Api\UserController::class, 'getUserProfile'])->name('api.user.profile');
        Route::put('profile', [Api\UserController::class, 'updateUserProfile'])->name('api.update.user.profile');
        Route::post('profile/picture', [Api\UserController::class, 'updateProfilePicture']);
        Route::delete('account', [Api\UserController::class, 'deleteUserAccount'])->name('api.delete.user.profile');
    });

    // Route::post('/public-transfer', [WalletController::class, 'transfer2'])
    //     ->middleware(['verify_status', 'merchant_eligibility', 'throttle:api_transfer_rate_limit']);

    Route::prefix('job')->group(function () {
        Route::post('create', [JobListingController::class, 'createJobOffer'])->name('api.job.create');
        Route::post('apply', [JobApplicantController::class, 'applyForJobs'])->name('api.job.apply');
        Route::get('self/retrieve', [JobListingController::class, 'getPostedJobs'])->name('api.job.self.retrieve');
        Route::get('around', [JobListingController::class, 'getJobsWithinProffessionalRange'])->name('api.jobs.around');
        Route::get('progress', [DashboardController::class, 'showWorkProgress'])->name('api.jobs.around');
        Route::get('appliedto', [JobApplicantController::class, 'getAllJobsAppliedFor'])->name('api.jobs.appliedto');
        Route::get('hired', [JobApplicantController::class, 'getAllJobsHiredFor'])->name('api.job.hired');
        Route::get('applicants', [JobApplicantController::class, 'getApplicants'])->name('api.jobs.applicants');
        Route::get('by/id', [JobListingController::class, 'getJobsById'])->name('api.jobs.by.id');
        Route::post('hireorreject/professional', [JobApplicantController::class, 'hireOrRejectProfessionals'])->name('api.hireorreject.job.applicant'); 
    });
    
    Route::get('working/professionals', [DashboardController::class, 'getProfessionalsOnJob'])->name('api.jobs.applicants');
    Route::get('confirm/endOfShift', [DashboardController::class, 'confirmEndOfShift']);
    Route::post('mark/tasks', [DashboardController::class, 'markTask']);

    // Route::prefix('notifications')->middleware('throttle:30,1')->group(function () {
    Route::prefix('notifications')->group(function () {
        Route::get('get', [NotificationController::class, 'getAllNotifications'])->name('api.notifications.get');
        Route::get('show/{notificationId?}', [NotificationController::class, 'showSingleNotification'])->name('api.notifications.show');
        Route::get('read/{notificationId?}', [NotificationController::class, 'readNotification'])->name('api.notifications.read');
    });
    
    Route::get('professionals/around', [Api\UserController::class, 'getProfessionalsWithinRange'])
    ->name('api.professionals.around');


    Route::get('businesses/around', [Api\UserController::class, 'getHealthCareProvidersWithinRange'])
    ->name('api.businesses.around');
});

Route::get('user/types', [ Api\UserController::class, 'userTypes' ])->name('api.user.types');
// Route::get('user/types', [ Api\UserController::class, 'userTypes' ])->middleware('throttle:5,1')->name('api.user.types');
Route::get('/test', [ Api\UserController::class, 'test' ])->name('api.test');
// Route::get('/test', [ Api\UserController::class, 'test' ])->middleware('throttle:5,1')->name('api.test');

// Route::prefix('auth')->middleware('throttle:5,1')->group(function () { 
Route::prefix('auth')->group(function () { 
    Route::post('/register', [ Api\AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login',[ Api\AuthController::class, 'login' ])->name('api.auth.login');
    Route::get('/forgot-password', [Api\ResetPasswordController::class, 'reset'])->name('password.reset');
    Route::post('/forgot-password', [Api\PasswordResetController::class, '__invoke']);
    // Route::post('/reset-password', [Api\PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::get('/swagger', function () {
    return view('swagger.index');
});
