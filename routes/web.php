<?php

use App\Http\Controllers\AuthController;
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

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Users (requires admins.* permissions)
    Route::middleware('permission:admins.view')->group(function () {
        Route::get('/admins', [AdminUserController::class, 'index'])->name('admins.index');
    });
    Route::middleware('permission:admins.create')->group(function () {
        Route::get('/admins/create', [AdminUserController::class, 'create'])
            ->middleware('permission:admins.create')
            ->name('admins.create');

        Route::post('/admins', [AdminUserController::class, 'store'])
            ->middleware('permission:admins.create')
            ->name('admins.store');
    });
    Route::middleware('permission:admins.view')->group(function () {
        Route::get('/admins/{admin}', [AdminUserController::class, 'show'])->name('admins.show');
    });
    Route::middleware('permission:admins.edit')->group(function () {
        Route::get('/admins/{admin}/edit', [AdminUserController::class, 'edit'])
            ->name('admins.edit');
        Route::put('/admins/{admin}', [AdminUserController::class, 'update'])
            ->name('admins.update');
    });
    Route::delete('/admins/{admin}', [AdminUserController::class, 'destroy'])
        ->middleware('permission:admins.delete')
        ->name('admins.destroy');

    // Students (requires students.* permissions) — create before {student} so /students/create isn't matched as ID
    Route::middleware('permission:students.view')->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    });
    Route::middleware('permission:students.create')->group(function () {
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    });
    Route::middleware('permission:students.view')->group(function () {
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    });
    Route::middleware('permission:students.edit')->group(function () {
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    });
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])
        ->middleware('permission:students.delete')
        ->name('students.destroy');

    // Categories (requires categories.* permissions)
    Route::middleware('permission:categories.view')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    });
    Route::middleware('permission:categories.create')->group(function () {
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    });
    Route::middleware('permission:categories.edit')->group(function () {
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    });
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('permission:categories.delete')
        ->name('categories.destroy');

    // Courses (requires courses.* permissions)
    Route::middleware('permission:courses.view')->group(function () {
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    });
    Route::middleware('permission:courses.create')->group(function () {
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    });
    Route::middleware('permission:courses.edit')->group(function () {
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    });
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])
        ->middleware('permission:courses.delete')
        ->name('courses.destroy');
    Route::patch('/courses/{course}/status', [CourseController::class, 'updateStatus'])
        ->middleware('permission:courses.publish')
        ->name('courses.status');

    // Course Modules (requires modules.* permissions)
    Route::middleware('permission:modules.view')->group(function () {
        Route::get('/courses/{course}/modules', [ModuleController::class, 'index'])->name('courses.modules.index');
    });
    Route::middleware('permission:modules.create')->group(function () {
        Route::get('/courses/{course}/modules/create', [ModuleController::class, 'create'])->name('courses.modules.create');
        Route::post('/courses/{course}/modules', [ModuleController::class, 'store'])->name('courses.modules.store');
    });
    Route::middleware('permission:modules.edit')->group(function () {
        Route::get('/courses/{course}/modules/{module}/edit', [ModuleController::class, 'edit'])->name('courses.modules.edit');
        Route::put('/courses/{course}/modules/{module}', [ModuleController::class, 'update'])->name('courses.modules.update');
        Route::post('/courses/{course}/modules/reorder', [ModuleController::class, 'reorder'])->name('courses.modules.reorder');
    });
    Route::delete('/courses/{course}/modules/{module}', [ModuleController::class, 'destroy'])
        ->middleware('permission:modules.delete')
        ->name('courses.modules.destroy');

    // Module Lessons (requires lessons.* permissions)
    Route::middleware('permission:lessons.view')->group(function () {
        Route::get('/courses/{course}/modules/{module}/lessons', [LessonController::class, 'index'])->name('courses.modules.lessons.index');
        Route::get('/courses/{course}/modules/{module}/lessons/{lesson}', [LessonController::class, 'show'])->name('courses.modules.lessons.show');
    });
    Route::middleware('permission:lessons.create')->group(function () {
        Route::get('/courses/{course}/modules/{module}/lessons/create', [LessonController::class, 'create'])->name('courses.modules.lessons.create');
        Route::post('/courses/{course}/modules/{module}/lessons', [LessonController::class, 'store'])->name('courses.modules.lessons.store');
    });
    Route::middleware('permission:lessons.edit')->group(function () {
        Route::get('/courses/{course}/modules/{module}/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('courses.modules.lessons.edit');
        Route::put('/courses/{course}/modules/{module}/lessons/{lesson}', [LessonController::class, 'update'])->name('courses.modules.lessons.update');
    });
    Route::delete('/courses/{course}/modules/{module}/lessons/{lesson}', [LessonController::class, 'destroy'])
        ->middleware('permission:lessons.delete')
        ->name('courses.modules.lessons.destroy');

    // Quizzes (requires quizzes.* permissions)
    Route::middleware('permission:quizzes.view')->group(function () {
        Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    });
    Route::middleware('permission:quizzes.create')->group(function () {
        Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
        Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    });
    Route::middleware('permission:quizzes.view')->group(function () {
        Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    });
    Route::middleware('permission:quizzes.edit')->group(function () {
        Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
        Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
    });
    Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])
        ->middleware('permission:quizzes.delete')
        ->name('quizzes.destroy');

    // Quiz Questions (requires quizzes.* permissions)
    Route::middleware('permission:quizzes.view')->group(function () {
        Route::get('/quizzes/{quiz}/questions', [QuestionController::class, 'index'])->name('quizzes.questions.index');
    });
    Route::middleware('permission:quizzes.create')->group(function () {
        Route::get('/quizzes/{quiz}/questions/create', [QuestionController::class, 'create'])->name('quizzes.questions.create');
        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('quizzes.questions.store');
    });
    Route::middleware('permission:quizzes.edit')->group(function () {
        Route::get('/quizzes/{quiz}/questions/{question}/edit', [QuestionController::class, 'edit'])->name('quizzes.questions.edit');
        Route::put('/quizzes/{quiz}/questions/{question}', [QuestionController::class, 'update'])->name('quizzes.questions.update');
        Route::post('/quizzes/{quiz}/questions/reorder', [QuestionController::class, 'reorder'])->name('quizzes.questions.reorder');
    });
    Route::delete('/quizzes/{quiz}/questions/{question}', [QuestionController::class, 'destroy'])
        ->middleware('permission:quizzes.delete')
        ->name('quizzes.questions.destroy');

    // Quiz Attempts (requires quizzes.view permission)
    Route::middleware('permission:quizzes.view')->group(function () {
        Route::get('/quizzes/{quiz}/attempts', [QuizController::class, 'attempts'])->name('quizzes.attempts.index');
        Route::get('/quizzes/{quiz}/attempts/{attempt}', [QuizController::class, 'showAttempt'])->name('quizzes.attempts.show');
    });

    // Enrollments (requires enrollments.* permissions)
    Route::middleware('permission:enrollments.view')->group(function () {
        Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
    });
    Route::middleware('permission:enrollments.create')->group(function () {
        Route::get('/enrollments/create', [EnrollmentController::class, 'create'])->name('enrollments.create');
        Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
    });
    Route::middleware('permission:enrollments.edit')->group(function () {
        Route::put('/enrollments/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollments.update');
    });
    Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])
        ->middleware('permission:enrollments.delete')
        ->name('enrollments.destroy');

    // Payments (requires payments.* permissions)
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    });
    Route::patch('/payments/{payment}', [PaymentController::class, 'update'])
        ->middleware('permission:payments.edit')
        ->name('payments.update');
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])
        ->middleware('permission:payments.refund')
        ->name('payments.refund');

    // Certificates (requires certificates.* permissions)
    Route::middleware('permission:certificates.view')->group(function () {
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    });
    Route::middleware('permission:certificates.issue')->group(function () {
        Route::get('/certificates/create', [CertificateController::class, 'create'])->name('certificates.create');
        Route::post('/certificates', [CertificateController::class, 'store'])->name('certificates.store');
    });
    Route::delete('/certificates/{certificate}', [CertificateController::class, 'destroy'])
        ->middleware('permission:certificates.revoke')
        ->name('certificates.destroy');

    // Reviews (requires reviews.* permissions)
    Route::get('/reviews', [ReviewController::class, 'index'])
        ->middleware('permission:reviews.view')
        ->name('reviews.index');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->middleware('permission:reviews.delete')
        ->name('reviews.destroy');

    // Notifications
    Route::resource('notifications', NotificationController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::post('/notifications/bulk', [NotificationController::class, 'sendBulk'])->name('notifications.bulk');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Reports (requires reports.* permissions)
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
