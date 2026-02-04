<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Support\Facades\Route;

// Admin Dashboard routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Users (requires admins.edit permission)
    Route::middleware('permission:admins.edit')->group(function () {
        Route::get('/admins', [AdminUserController::class, 'index'])->name('admins.index');
        Route::get('/admins/create', [AdminUserController::class, 'create'])->name('admins.create');
        Route::post('/admins', [AdminUserController::class, 'store'])->name('admins.store');
        Route::get('/admins/{admin}', [AdminUserController::class, 'show'])->name('admins.show');
        Route::get('/admins/{admin}/edit', [AdminUserController::class, 'edit'])->name('admins.edit');
        Route::put('/admins/{admin}', [AdminUserController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{admin}', [AdminUserController::class, 'destroy'])->name('admins.destroy');
    });

    // Students (requires students.edit permission)
    Route::middleware('permission:students.edit')->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    });

    // Categories (requires categories.edit permission)
    Route::middleware('permission:categories.edit')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Courses (requires courses.edit permission)
    Route::middleware('permission:courses.edit')->group(function () {
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::patch('/courses/{course}/status', [CourseController::class, 'updateStatus'])->name('courses.status');
    });

    // Course Modules (requires modules.edit permission)
    Route::middleware('permission:modules.edit')->group(function () {
        Route::get('/courses/{course}/modules', [ModuleController::class, 'index'])->name('courses.modules.index');
        Route::get('/courses/{course}/modules/create', [ModuleController::class, 'create'])->name('courses.modules.create');
        Route::post('/courses/{course}/modules', [ModuleController::class, 'store'])->name('courses.modules.store');
        Route::get('/courses/{course}/modules/{module}/edit', [ModuleController::class, 'edit'])->name('courses.modules.edit');
        Route::put('/courses/{course}/modules/{module}', [ModuleController::class, 'update'])->name('courses.modules.update');
        Route::post('/courses/{course}/modules/reorder', [ModuleController::class, 'reorder'])->name('courses.modules.reorder');
        Route::delete('/courses/{course}/modules/{module}', [ModuleController::class, 'destroy'])->name('courses.modules.destroy');
    });

    // Module Lessons (requires lessons.edit permission)
    Route::middleware('permission:lessons.edit')->group(function () {
        Route::get('/courses/{course}/modules/{module}/lessons', [LessonController::class, 'index'])->name('courses.modules.lessons.index');
        Route::get('/courses/{course}/modules/{module}/lessons/create', [LessonController::class, 'create'])->name('courses.modules.lessons.create');
        Route::post('/courses/{course}/modules/{module}/lessons', [LessonController::class, 'store'])->name('courses.modules.lessons.store');
        Route::get('/courses/{course}/modules/{module}/lessons/{lesson}', [LessonController::class, 'show'])->name('courses.modules.lessons.show');
        Route::get('/courses/{course}/modules/{module}/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('courses.modules.lessons.edit');
        Route::put('/courses/{course}/modules/{module}/lessons/{lesson}', [LessonController::class, 'update'])->name('courses.modules.lessons.update');
        Route::delete('/courses/{course}/modules/{module}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('courses.modules.lessons.destroy');
    });

    // Quizzes (requires quizzes.edit permission)
    Route::middleware('permission:quizzes.edit')->group(function () {
        Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
        Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
        Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
        Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
        Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
        Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
        Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');
        // Quiz Questions
        Route::get('/quizzes/{quiz}/questions', [QuestionController::class, 'index'])->name('quizzes.questions.index');
        Route::get('/quizzes/{quiz}/questions/create', [QuestionController::class, 'create'])->name('quizzes.questions.create');
        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('quizzes.questions.store');
        Route::get('/quizzes/{quiz}/questions/{question}/edit', [QuestionController::class, 'edit'])->name('quizzes.questions.edit');
        Route::put('/quizzes/{quiz}/questions/{question}', [QuestionController::class, 'update'])->name('quizzes.questions.update');
        Route::post('/quizzes/{quiz}/questions/reorder', [QuestionController::class, 'reorder'])->name('quizzes.questions.reorder');
        Route::delete('/quizzes/{quiz}/questions/{question}', [QuestionController::class, 'destroy'])->name('quizzes.questions.destroy');
        // Quiz Attempts
        Route::get('/quizzes/{quiz}/attempts', [QuizController::class, 'attempts'])->name('quizzes.attempts.index');
        Route::get('/quizzes/{quiz}/attempts/{attempt}', [QuizController::class, 'showAttempt'])->name('quizzes.attempts.show');
    });

    // Enrollments (requires enrollments.edit permission)
    Route::middleware('permission:enrollments.edit')->group(function () {
        Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/enrollments/create', [EnrollmentController::class, 'create'])->name('enrollments.create');
        Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
        Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
        Route::put('/enrollments/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollments.update');
        Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    });

    // Payments (requires payments.edit permission)
    Route::middleware('permission:payments.edit')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::patch('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    });

    // Certificates (requires certificates.issue permission)
    Route::middleware('permission:certificates.issue')->group(function () {
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/create', [CertificateController::class, 'create'])->name('certificates.create');
        Route::post('/certificates', [CertificateController::class, 'store'])->name('certificates.store');
        Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::delete('/certificates/{certificate}', [CertificateController::class, 'destroy'])->name('certificates.destroy');
    });

    // Reviews (requires reviews.view permission)
    Route::middleware('permission:reviews.view')->group(function () {
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    });

    // Notifications
    Route::resource('notifications', NotificationController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::post('/notifications/bulk', [NotificationController::class, 'sendBulk'])->name('notifications.bulk');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Reports (requires reports.view permission)
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/students', [ReportController::class, 'students'])->name('students');
        Route::get('/enrollments', [ReportController::class, 'enrollments'])->name('enrollments');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/course-performance', [ReportController::class, 'coursePerformance'])->name('course-performance');
    });
    Route::prefix('reports/export')->name('reports.export.')->middleware('permission:reports.export')->group(function () {
        Route::get('/students', [ReportController::class, 'exportStudents'])->name('students');
        Route::get('/enrollments', [ReportController::class, 'exportEnrollments'])->name('enrollments');
        Route::get('/revenue', [ReportController::class, 'exportRevenue'])->name('revenue');
    });

    // Activity Log (requires activity-log.view permission)
    Route::middleware('permission:activity-log.view')->group(function () {
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::get('/activity-log/{activity}', [ActivityLogController::class, 'show'])->name('activity-log.show');
    });
});
