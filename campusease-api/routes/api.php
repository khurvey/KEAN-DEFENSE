
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FacultySubjectController;

Route::prefix('v1')->group(function () {

    // TEST API CONNECTION
    Route::get('/campusease', function () {
        return response()->json([
            'message' => 'CampusEase API is working!'
        ]);
    });

    // REGISTRATION AND LOGIN
    Route::post('/auth/register',
        [AuthController::class, 'register']);

    Route::post('/auth/login',
        [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    // FACULTY LIST AND SUBJECTS
    Route::get('/faculty',
        [ConsultationController::class, 'faculty']);

    Route::get('/faculty/{id}/subjects',
        [ConsultationController::class, 'subjects']);

    // AUTHENTICATED USERS
    Route::middleware('auth:sanctum')->group(function () {

        // ACCOUNT
        Route::get('/me',
            [AuthController::class, 'me']);

        Route::post('/auth/logout',
            [AuthController::class, 'logout']);

        Route::patch('/auth/profile',
            [AuthController::class, 'updateProfile']);

        // CONSULTATION APPOINTMENTS
        Route::get('/consultations',
            [ConsultationController::class, 'index']);

        Route::post('/consultations',
            [ConsultationController::class, 'store']);

        Route::patch('/consultations/{id}/status',
            [ConsultationController::class, 'updateStatus']);

        // FACULTY SUBJECT MANAGEMENT
        Route::get('/faculty/my-subjects',
            [FacultySubjectController::class, 'index']);

        Route::post('/faculty/my-subjects',
            [FacultySubjectController::class, 'store']);

        Route::patch('/faculty/my-subjects/{id}',
            [FacultySubjectController::class, 'update']);

        Route::delete('/faculty/my-subjects/{id}',
            [FacultySubjectController::class, 'destroy']);

        // ADMIN FACULTY APPROVAL
        Route::get('/admin/faculty/pending',
            [AdminController::class, 'pendingFaculty']);

        Route::patch('/admin/faculty/{id}/review',
            [AdminController::class, 'reviewFaculty']);

    });
});
