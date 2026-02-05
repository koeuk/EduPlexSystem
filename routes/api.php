<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseReviewController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\LessonProgressController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| EduPlex LMS Mobile API
| Base URL: /api
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Certificate verification (public)
Route::get('/certificates/verify/{code}', [CertificateController::class, 'verify']);

// Data for select dropdowns
Route::prefix('data')->group(function () {
    Route::get('/', [DataController::class, 'index']);
    Route::get('/categories', [DataController::class, 'categories']);
    Route::get('/courses', [DataController::class, 'courses']);
    Route::get('/categories/{category}/courses', [DataController::class, 'coursesByCategory']);
    Route::get('/user-statuses', [DataController::class, 'userStatuses']);
    Route::get('/student-statuses', [DataController::class, 'studentStatuses']);
    Route::get('/lesson-types', [DataController::class, 'lessonTypes']);
    Route::get('/course-pricing-types', [DataController::class, 'coursePricingTypes']);
    Route::get('/course-levels', [DataController::class, 'courseLevels']);
    Route::get('/course-filters', [DataController::class, 'courseFilters']);
    Route::get('/video-config', [VideoController::class, 'config']);
});

// Protected routes (require Sanctum token)
Route::middleware(['auth:sanctum'])->group(function () {

    // Authentication
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Videos
    Route::prefix('videos')->group(function () {
        Route::get('/config', [VideoController::class, 'config']);
        Route::get('/list', [VideoController::class, 'list']);
        Route::post('/upload', [VideoController::class, 'upload']);
        Route::post('/metadata', [VideoController::class, 'metadata']);
        Route::delete('/delete', [VideoController::class, 'destroy']);
        Route::post('/lessons/{lesson}/upload', [VideoController::class, 'uploadForLesson']);
        Route::delete('/lessons/{lesson}', [VideoController::class, 'destroyFromLesson']);
        Route::get('/lessons/{lesson}/stream', [VideoController::class, 'stream']);
    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{category}', [CategoryController::class, 'show']);
        Route::get('/{category}/courses', [CategoryController::class, 'courses']);
    });

    // Courses
    Route::prefix('courses')->group(function () {
        Route::get('/', [CourseController::class, 'index']);
        Route::get('/{course}', [CourseController::class, 'show']);
        Route::get('/{course}/modules', [CourseController::class, 'modules']);
        Route::get('/{course}/reviews', [CourseController::class, 'reviews']);
        Route::post('/{course}/reviews', [CourseController::class, 'storeReview']);
    });

    // Course Reviews
    Route::prefix('reviews')->group(function () {
        Route::get('/', [CourseReviewController::class, 'index']);
        Route::get('/{review}', [CourseReviewController::class, 'show']);
        Route::post('/courses/{course}', [CourseReviewController::class, 'store']);
        Route::get('/courses/{course}', [CourseReviewController::class, 'byCourse']);
        Route::put('/{review}', [CourseReviewController::class, 'update']);
        Route::delete('/{review}', [CourseReviewController::class, 'destroy']);
    });

    // Lesson Progress
    Route::prefix('progress')->group(function () {
        Route::get('/', [LessonProgressController::class, 'index']);
        Route::get('/completed', [LessonProgressController::class, 'completed']);
        Route::get('/courses/{courseId}', [LessonProgressController::class, 'byCourse']);
        Route::get('/lessons/{lesson}', [LessonProgressController::class, 'show']);
        Route::put('/lessons/{lesson}', [LessonProgressController::class, 'update']);
    });

    // Enrollments
    Route::prefix('enrollments')->group(function () {
        Route::get('/', [EnrollmentController::class, 'index']);
        Route::post('/', [EnrollmentController::class, 'store']);
        Route::get('/{enrollment}', [EnrollmentController::class, 'show']);
        Route::delete('/{enrollment}', [EnrollmentController::class, 'destroy']);
    });

    // Lessons
    Route::prefix('lessons')->group(function () {
        Route::get('/{lesson}', [LessonController::class, 'show']);
        Route::post('/{lesson}/progress', [LessonController::class, 'updateProgress']);
        Route::get('/{lesson}/progress', [LessonController::class, 'getProgress']);
    });

    // Quizzes
    Route::prefix('quizzes')->group(function () {
        Route::get('/{quiz}', [QuizController::class, 'show']);
        Route::post('/{quiz}/attempts', [QuizController::class, 'startAttempt']);
        Route::get('/{quiz}/attempts', [QuizController::class, 'getAttempts']);
    });

    // Quiz Attempts
    Route::prefix('quizzes/attempts')->group(function () {
        Route::put('/{attempt}', [QuizController::class, 'submitAttempt']);
        Route::get('/{attempt}', [QuizController::class, 'getAttempt']);
    });

    // Payments
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('/{payment}', [PaymentController::class, 'show']);
    });

    // Certificates
    Route::prefix('certificates')->group(function () {
        Route::get('/', [CertificateController::class, 'index']);
        Route::get('/{certificate}', [CertificateController::class, 'show']);
        Route::get('/{certificate}/download', [CertificateController::class, 'download']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{notification}', [NotificationController::class, 'destroy']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/recent-activity', [DashboardController::class, 'recentActivity']);
        Route::get('/activity-log', [DashboardController::class, 'activityLog']);
        Route::get('/continue-learning', [DashboardController::class, 'continueeLearning']);
    });
});
