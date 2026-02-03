<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;
    protected Student $student;
    protected string $token;
    protected Category $category;
    protected Course $course;
    protected CourseModule $module;
    protected Lesson $lesson;
    protected Quiz $quiz;
    protected QuizQuestion $question;
    protected QuizOption $correctOption;
    protected QuizOption $incorrectOption;
    protected Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();
    }

    protected function setUpTestData(): void
    {
        // Create student user
        $this->studentUser = User::create([
            'username' => 'teststudent',
            'email' => 'test@student.com',
            'password' => Hash::make('password123'),
            'full_name' => 'Test Student',
            'phone' => '+1234567890',
            'user_type' => 'student',
            'gender' => 'male',
            'date_of_birth' => '1995-01-01',
            'status' => 'active',
        ]);

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'student_id_number' => 'STU-TEST-001',
            'enrollment_date' => now(),
            'student_status' => 'active',
        ]);

        // Create category
        $this->category = Category::create([
            'category_name' => 'Test Category',
            'description' => 'A test category for API testing',
            'icon' => 'test-icon',
            'is_active' => true,
        ]);

        // Create course
        $this->course = Course::create([
            'category_id' => $this->category->id,
            'course_name' => 'Test Course',
            'course_code' => 'TEST-001',
            'description' => 'A test course for API testing',
            'level' => 'beginner',
            'duration_hours' => 10,
            'price' => 49.99,
            'instructor_name' => 'Test Instructor',
            'status' => 'published',
            'enrollment_limit' => 100,
            'is_featured' => true,
        ]);

        // Create quiz
        $this->quiz = Quiz::create([
            'quiz_title' => 'Test Quiz',
            'instructions' => 'Test quiz instructions',
            'time_limit_minutes' => 30,
            'passing_score' => 70.00,
            'max_attempts' => 3,
            'show_correct_answers' => true,
            'randomize_questions' => false,
        ]);

        // Create quiz question
        $this->question = QuizQuestion::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'What is 2 + 2?',
            'question_type' => 'multiple_choice',
            'points' => 10,
            'question_order' => 1,
            'explanation' => 'Basic arithmetic',
        ]);

        // Create quiz options
        $this->correctOption = QuizOption::create([
            'question_id' => $this->question->id,
            'option_text' => '4',
            'is_correct' => true,
            'option_order' => 1,
        ]);

        $this->incorrectOption = QuizOption::create([
            'question_id' => $this->question->id,
            'option_text' => '5',
            'is_correct' => false,
            'option_order' => 2,
        ]);

        // Create module
        $this->module = CourseModule::create([
            'course_id' => $this->course->id,
            'module_title' => 'Test Module',
            'module_order' => 1,
            'description' => 'A test module',
        ]);

        // Create lesson
        $this->lesson = Lesson::create([
            'course_id' => $this->course->id,
            'module_id' => $this->module->id,
            'lesson_title' => 'Test Lesson',
            'lesson_type' => 'video',
            'lesson_order' => 1,
            'description' => 'A test lesson',
            'content' => 'Test lesson content',
            'video_duration' => 600,
            'quiz_id' => $this->quiz->id,
            'is_mandatory' => true,
            'duration_minutes' => 10,
        ]);

        // Create enrollment
        $this->enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'enrollment_date' => now(),
            'progress_percentage' => 0,
            'status' => 'active',
            'payment_status' => 'paid',
        ]);
    }

    protected function authenticateStudent(): void
    {
        Sanctum::actingAs($this->studentUser, ['*']);
    }

    // ==================== AUTH TESTS ====================

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'newstudent',
            'email' => 'new@student.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'full_name' => 'New Student',
            'phone' => '+1234567899',
            'gender' => 'female',
            'date_of_birth' => '2000-05-15',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'student', 'token'],
            ]);
    }

    public function test_user_can_login(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@student.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'student', 'token'],
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@student.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/auth/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'username', 'email', 'full_name', 'student'],
            ]);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $this->authenticateStudent();

        $response = $this->putJson('/api/auth/profile', [
            'full_name' => 'Updated Name',
            'phone' => '+9876543210',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $this->studentUser->id,
            'full_name' => 'Updated Name',
        ]);
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $this->authenticateStudent();

        $response = $this->putJson('/api/auth/change-password', [
            'current_password' => 'password123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $this->authenticateStudent();

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/auth/profile');

        $response->assertStatus(401);
    }

    // ==================== CATEGORY TESTS ====================

    public function test_can_list_categories(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'category_name', 'description'],
                ],
            ]);
    }

    public function test_can_show_single_category(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson("/api/categories/{$this->category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.category_name', 'Test Category');
    }

    public function test_can_get_courses_by_category(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson("/api/categories/{$this->category->id}/courses");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);
    }

    // ==================== COURSE TESTS ====================

    public function test_can_list_courses(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);
    }

    public function test_can_show_single_course(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson("/api/courses/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.course_name', 'Test Course');
    }

    public function test_can_get_course_modules(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson("/api/courses/{$this->course->id}/modules");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'course',
                    'enrollment',
                    'modules',
                ],
            ]);
    }

    public function test_can_get_course_reviews(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson("/api/courses/{$this->course->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_can_submit_course_review(): void
    {
        $this->authenticateStudent();

        $response = $this->postJson("/api/courses/{$this->course->id}/reviews", [
            'rating' => 5,
            'review_text' => 'Great course!',
            'would_recommend' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('course_reviews', [
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'rating' => 5,
        ]);
    }

    public function test_can_update_own_review(): void
    {
        $this->authenticateStudent();

        $review = CourseReview::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'rating' => 4,
            'review_text' => 'Good course',
            'would_recommend' => true,
        ]);

        $response = $this->putJson("/api/courses/reviews/{$review->id}", [
            'rating' => 5,
            'review_text' => 'Updated: Great course!',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('course_reviews', [
            'id' => $review->id,
            'rating' => 5,
        ]);
    }

    public function test_can_delete_own_review(): void
    {
        $this->authenticateStudent();

        $review = CourseReview::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'rating' => 4,
            'review_text' => 'Good course',
            'would_recommend' => true,
        ]);

        $response = $this->deleteJson("/api/courses/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('course_reviews', ['id' => $review->id]);
    }

    // ==================== ENROLLMENT TESTS ====================

    public function test_can_list_enrollments(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_can_create_enrollment(): void
    {
        $this->authenticateStudent();

        // Create another course to enroll in
        $newCourse = Course::create([
            'category_id' => $this->category->id,
            'course_name' => 'Another Course',
            'course_code' => 'TEST-002',
            'description' => 'Another test course',
            'level' => 'beginner',
            'duration_hours' => 5,
            'price' => 0,
            'instructor_name' => 'Another Instructor',
            'status' => 'published',
        ]);

        $response = $this->postJson('/api/enrollments', [
            'course_id' => $newCourse->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $this->student->id,
            'course_id' => $newCourse->id,
        ]);
    }

    public function test_can_show_single_enrollment(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson("/api/enrollments/{$this->enrollment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'course', 'status'],
            ]);
    }

    public function test_can_cancel_enrollment(): void
    {
        $this->authenticateStudent();

        $response = $this->deleteJson("/api/enrollments/{$this->enrollment->id}");

        $response->assertStatus(200);
    }

    // ==================== LESSON TESTS ====================

    public function test_can_view_lesson(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson("/api/lessons/{$this->lesson->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'lesson_title', 'content'],
            ]);
    }

    public function test_can_update_lesson_progress(): void
    {
        $this->authenticateStudent();

        $response = $this->postJson("/api/lessons/{$this->lesson->id}/progress", [
            'status' => 'in_progress',
            'progress_percentage' => 50,
            'video_last_position' => 300,
        ]);

        $response->assertStatus(200);
    }

    public function test_can_get_lesson_progress(): void
    {
        $this->authenticateStudent();

        // First create some progress
        LessonProgress::create([
            'student_id' => $this->student->id,
            'lesson_id' => $this->lesson->id,
            'course_id' => $this->course->id,
            'status' => 'in_progress',
            'progress_percentage' => 50,
        ]);

        $response = $this->getJson("/api/lessons/{$this->lesson->id}/progress");

        $response->assertStatus(200);
    }

    // ==================== QUIZ TESTS ====================

    public function test_can_view_quiz(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson("/api/quizzes/{$this->quiz->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'quiz_title', 'questions'],
            ]);
    }

    public function test_can_start_quiz_attempt(): void
    {
        $this->authenticateStudent();

        $response = $this->postJson("/api/quizzes/{$this->quiz->id}/attempts");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['attempt_id', 'attempt_number'],
            ]);
    }

    public function test_can_get_quiz_attempts(): void
    {
        $this->authenticateStudent();

        // Create an attempt
        QuizAttempt::create([
            'quiz_id' => $this->quiz->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'score_percentage' => 0,
            'total_points' => 0,
            'max_points' => 10,
            'passed' => false,
            'started_at' => now(),
        ]);

        $response = $this->getJson("/api/quizzes/{$this->quiz->id}/attempts");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_can_submit_quiz_attempt(): void
    {
        $this->authenticateStudent();

        $attempt = QuizAttempt::create([
            'quiz_id' => $this->quiz->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'score_percentage' => 0,
            'total_points' => 0,
            'max_points' => 10,
            'passed' => false,
            'started_at' => now(),
        ]);

        $response = $this->putJson("/api/quizzes/attempts/{$attempt->id}", [
            'answers' => [
                [
                    'question_id' => $this->question->id,
                    'selected_option_id' => $this->correctOption->id,
                ],
            ],
        ]);

        $response->assertStatus(200);
    }

    public function test_can_get_attempt_results(): void
    {
        $this->authenticateStudent();

        $attempt = QuizAttempt::create([
            'quiz_id' => $this->quiz->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'score_percentage' => 100,
            'total_points' => 10,
            'max_points' => 10,
            'passed' => true,
            'started_at' => now()->subMinutes(10),
            'submitted_at' => now(),
        ]);

        $response = $this->getJson("/api/quizzes/attempts/{$attempt->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'score_percentage', 'passed'],
            ]);
    }

    // ==================== PAYMENT TESTS ====================

    public function test_can_list_payments(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_can_process_payment(): void
    {
        $this->authenticateStudent();

        // Create a new course to pay for
        $paidCourse = Course::create([
            'category_id' => $this->category->id,
            'course_name' => 'Paid Course',
            'course_code' => 'PAID-001',
            'description' => 'A paid course',
            'level' => 'intermediate',
            'duration_hours' => 20,
            'price' => 99.99,
            'instructor_name' => 'Paid Instructor',
            'status' => 'published',
        ]);

        // Create enrollment with pending payment status
        Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $paidCourse->id,
            'enrollment_date' => now(),
            'progress_percentage' => 0,
            'status' => 'active',
            'payment_status' => 'pending',
        ]);

        $response = $this->postJson('/api/payments', [
            'course_id' => $paidCourse->id,
            'payment_method' => 'credit_card',
            'amount' => 99.99,
        ]);

        $response->assertStatus(201);
    }

    public function test_can_show_payment(): void
    {
        $this->authenticateStudent();

        $payment = Payment::create([
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 49.99,
            'payment_method' => 'credit_card',
            'transaction_id' => 'TXN_TEST123',
            'payment_date' => now(),
            'status' => 'completed',
        ]);

        $response = $this->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'amount', 'status'],
            ]);
    }

    // ==================== CERTIFICATE TESTS ====================

    public function test_can_list_certificates(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/certificates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_can_show_certificate(): void
    {
        $this->authenticateStudent();

        $certificate = Certificate::create([
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'issue_date' => now(),
            'certificate_code' => 'CERT-TEST123',
            'verification_url' => 'https://example.com/verify/test',
        ]);

        $response = $this->getJson("/api/certificates/{$certificate->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'certificate_code', 'issue_date'],
            ]);
    }

    public function test_can_verify_certificate_publicly(): void
    {
        $certificate = Certificate::create([
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'issue_date' => now(),
            'certificate_code' => 'CERT-VERIFY123',
            'verification_url' => 'https://example.com/verify/test',
        ]);

        // This endpoint is public (no auth required)
        $response = $this->getJson("/api/certificates/verify/{$certificate->certificate_code}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_certificate_verification_fails_for_invalid_code(): void
    {
        $response = $this->getJson('/api/certificates/verify/INVALID-CODE');

        $response->assertStatus(404);
    }

    // ==================== NOTIFICATION TESTS ====================

    public function test_can_list_notifications(): void
    {
        $this->authenticateStudent();

        Notification::create([
            'user_id' => $this->studentUser->id,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'type' => 'info',
            'is_read' => false,
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'unread_count',
            ]);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $this->authenticateStudent();

        $notification = Notification::create([
            'user_id' => $this->studentUser->id,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'type' => 'info',
            'is_read' => false,
        ]);

        $response = $this->putJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        $this->authenticateStudent();

        Notification::create([
            'user_id' => $this->studentUser->id,
            'title' => 'Test 1',
            'message' => 'Message 1',
            'type' => 'info',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $this->studentUser->id,
            'title' => 'Test 2',
            'message' => 'Message 2',
            'type' => 'info',
            'is_read' => false,
        ]);

        $response = $this->putJson('/api/notifications/read-all');

        $response->assertStatus(200);
    }

    public function test_can_delete_notification(): void
    {
        $this->authenticateStudent();

        $notification = Notification::create([
            'user_id' => $this->studentUser->id,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'type' => 'info',
            'is_read' => false,
        ]);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    // ==================== DASHBOARD TESTS ====================

    public function test_can_get_dashboard_stats(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_can_get_recent_activity(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/dashboard/recent-activity');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_can_get_activity_log(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/dashboard/activity-log');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_can_get_continue_learning(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/dashboard/continue-learning');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    // ==================== FILTER & PAGINATION TESTS ====================

    public function test_courses_support_filtering(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/courses?filter[level]=beginner');

        $response->assertStatus(200);
    }

    public function test_courses_support_search(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/courses?filter[search]=Test');

        $response->assertStatus(200);
    }

    public function test_courses_support_pagination(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/courses?per_page=5&page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'pagination' => ['current_page', 'per_page', 'total'],
            ]);
    }

    public function test_enrollments_support_filtering(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/enrollments?filter[status]=active');

        $response->assertStatus(200);
    }

    // ==================== ERROR HANDLING TESTS ====================

    public function test_returns_404_for_nonexistent_course(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/courses/99999');

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_category(): void
    {
        $this->authenticateStudent();

        $response = $this->getJson('/api/categories/99999');

        $response->assertStatus(404);
    }

    public function test_returns_validation_error_for_invalid_registration(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_enroll_in_same_course_twice(): void
    {
        $this->authenticateStudent();

        // Try to enroll in a course we're already enrolled in
        $response = $this->postJson('/api/enrollments', [
            'course_id' => $this->course->id,
        ]);

        $response->assertStatus(422);
    }
}
