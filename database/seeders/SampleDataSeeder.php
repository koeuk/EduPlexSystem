<?php

namespace Database\Seeders;

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
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding sample data for API testing...');

        // Create Categories
        $this->command->info('Creating categories...');
        $categories = $this->createCategories();

        // Create Courses
        $this->command->info('Creating courses...');
        $courses = $this->createCourses($categories);

        // Create Quizzes
        $this->command->info('Creating quizzes...');
        $quizzes = $this->createQuizzes();

        // Create Modules and Lessons
        $this->command->info('Creating modules and lessons...');
        $this->createModulesAndLessons($courses, $quizzes);

        // Create Test Students
        $this->command->info('Creating test students...');
        $students = $this->createStudents();

        // Create Enrollments
        $this->command->info('Creating enrollments...');
        $enrollments = $this->createEnrollments($students, $courses);

        // Create Lesson Progress
        $this->command->info('Creating lesson progress...');
        $this->createLessonProgress($students, $courses);

        // Create Quiz Attempts
        $this->command->info('Creating quiz attempts...');
        $this->createQuizAttempts($students, $quizzes);

        // Create Payments
        $this->command->info('Creating payments...');
        $this->createPayments($students, $courses);

        // Create Certificates
        $this->command->info('Creating certificates...');
        $this->createCertificates($students, $courses);

        // Create Reviews
        $this->command->info('Creating reviews...');
        $this->createReviews($students, $courses);

        // Create Notifications
        $this->command->info('Creating notifications...');
        $this->createNotifications($students);

        $this->command->info('Sample data seeding completed!');
        $this->command->newLine();
        $this->command->info('Test Accounts:');
        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['admin@eduplex.com', 'password', 'Super Admin'],
                ['student1@test.com', 'password', 'Student'],
                ['student2@test.com', 'password', 'Student'],
                ['student3@test.com', 'password', 'Student'],
            ]
        );
    }

    private function createCategories(): array
    {
        $categoriesData = [
            [
                'category_name' => 'Web Development',
                'description' => 'Learn to build modern web applications with HTML, CSS, JavaScript, and popular frameworks.',
                'icon' => 'globe',
                'is_active' => true,
            ],
            [
                'category_name' => 'Mobile Development',
                'description' => 'Create native and cross-platform mobile applications for iOS and Android.',
                'icon' => 'smartphone',
                'is_active' => true,
            ],
            [
                'category_name' => 'Data Science',
                'description' => 'Master data analysis, machine learning, and artificial intelligence.',
                'icon' => 'bar-chart',
                'is_active' => true,
            ],
            [
                'category_name' => 'DevOps',
                'description' => 'Learn CI/CD, containerization, cloud infrastructure, and automation.',
                'icon' => 'server',
                'is_active' => true,
            ],
            [
                'category_name' => 'Cybersecurity',
                'description' => 'Understand security principles, ethical hacking, and system protection.',
                'icon' => 'shield',
                'is_active' => true,
            ],
            [
                'category_name' => 'UI/UX Design',
                'description' => 'Design beautiful and user-friendly interfaces and experiences.',
                'icon' => 'palette',
                'is_active' => true,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $categories[] = Category::create($data);
        }

        return $categories;
    }

    private function createCourses(array $categories): array
    {
        $coursesData = [
            // Web Development Courses
            [
                'category_index' => 0,
                'course_name' => 'Complete Laravel 11 Masterclass',
                'course_code' => 'WEB-LAR-001',
                'description' => 'Master Laravel 11 from scratch. Build real-world applications with the most popular PHP framework. Learn routing, controllers, Eloquent ORM, authentication, and more.',
                'level' => 'beginner',
                'duration_hours' => 40,
                'price' => 99.99,
                'instructor_name' => 'John Smith',
                'status' => 'published',
                'enrollment_limit' => 500,
                'is_featured' => true,
            ],
            [
                'category_index' => 0,
                'course_name' => 'Vue.js 3 Complete Guide',
                'course_code' => 'WEB-VUE-001',
                'description' => 'Learn Vue.js 3 with Composition API, Pinia, Vue Router, and build modern single-page applications.',
                'level' => 'intermediate',
                'duration_hours' => 30,
                'price' => 89.99,
                'instructor_name' => 'Sarah Johnson',
                'status' => 'published',
                'enrollment_limit' => 300,
                'is_featured' => true,
            ],
            [
                'category_index' => 0,
                'course_name' => 'HTML & CSS Fundamentals',
                'course_code' => 'WEB-HTML-001',
                'description' => 'Start your web development journey with HTML5 and CSS3 fundamentals. Perfect for beginners.',
                'level' => 'beginner',
                'duration_hours' => 15,
                'price' => 0.00,
                'instructor_name' => 'Mike Davis',
                'status' => 'published',
                'enrollment_limit' => null,
                'is_featured' => false,
            ],
            // Mobile Development Courses
            [
                'category_index' => 1,
                'course_name' => 'Flutter & Dart Complete Course',
                'course_code' => 'MOB-FLT-001',
                'description' => 'Build beautiful cross-platform mobile apps with Flutter and Dart. Create iOS and Android apps with a single codebase.',
                'level' => 'intermediate',
                'duration_hours' => 50,
                'price' => 129.99,
                'instructor_name' => 'Emily Chen',
                'status' => 'published',
                'enrollment_limit' => 200,
                'is_featured' => true,
            ],
            [
                'category_index' => 1,
                'course_name' => 'React Native for Beginners',
                'course_code' => 'MOB-RN-001',
                'description' => 'Learn React Native and build your first mobile application. Get started with cross-platform development.',
                'level' => 'beginner',
                'duration_hours' => 25,
                'price' => 79.99,
                'instructor_name' => 'Alex Wilson',
                'status' => 'published',
                'enrollment_limit' => 250,
                'is_featured' => false,
            ],
            // Data Science Courses
            [
                'category_index' => 2,
                'course_name' => 'Python for Data Science',
                'course_code' => 'DS-PY-001',
                'description' => 'Learn Python programming for data analysis, visualization, and machine learning applications.',
                'level' => 'beginner',
                'duration_hours' => 45,
                'price' => 119.99,
                'instructor_name' => 'Dr. Robert Brown',
                'status' => 'published',
                'enrollment_limit' => 400,
                'is_featured' => true,
            ],
            [
                'category_index' => 2,
                'course_name' => 'Machine Learning A-Z',
                'course_code' => 'DS-ML-001',
                'description' => 'Comprehensive machine learning course covering all major algorithms from regression to deep learning.',
                'level' => 'advanced',
                'duration_hours' => 60,
                'price' => 149.99,
                'instructor_name' => 'Dr. Lisa Wang',
                'status' => 'published',
                'enrollment_limit' => 150,
                'is_featured' => false,
            ],
            // DevOps Courses
            [
                'category_index' => 3,
                'course_name' => 'Docker & Kubernetes Mastery',
                'course_code' => 'DEV-DK-001',
                'description' => 'Master containerization with Docker and orchestration with Kubernetes. Deploy and scale like a pro.',
                'level' => 'intermediate',
                'duration_hours' => 35,
                'price' => 109.99,
                'instructor_name' => 'James Miller',
                'status' => 'published',
                'enrollment_limit' => 200,
                'is_featured' => false,
            ],
            // Draft Course (not visible to students)
            [
                'category_index' => 4,
                'course_name' => 'Ethical Hacking Bootcamp',
                'course_code' => 'SEC-EH-001',
                'description' => 'Learn penetration testing and ethical hacking from scratch. Become a certified ethical hacker.',
                'level' => 'intermediate',
                'duration_hours' => 55,
                'price' => 159.99,
                'instructor_name' => 'Chris Anderson',
                'status' => 'draft',
                'enrollment_limit' => 100,
                'is_featured' => false,
            ],
            // UI/UX Course
            [
                'category_index' => 5,
                'course_name' => 'Figma UI Design Course',
                'course_code' => 'UX-FIG-001',
                'description' => 'Learn UI design with Figma. Create stunning interfaces and interactive prototypes.',
                'level' => 'beginner',
                'duration_hours' => 20,
                'price' => 0.00,
                'instructor_name' => 'Jessica Taylor',
                'status' => 'published',
                'enrollment_limit' => null,
                'is_featured' => false,
            ],
        ];

        $courses = [];
        foreach ($coursesData as $data) {
            $categoryIndex = $data['category_index'];
            unset($data['category_index']);
            $data['category_id'] = $categories[$categoryIndex]->id;
            $courses[] = Course::create($data);
        }

        return $courses;
    }

    private function createQuizzes(): array
    {
        $quizzesData = [
            [
                'quiz_title' => 'Laravel Basics Quiz',
                'instructions' => 'Test your knowledge of Laravel fundamentals. Answer all questions within the time limit.',
                'time_limit_minutes' => 20,
                'passing_score' => 70.00,
                'max_attempts' => 3,
                'show_correct_answers' => true,
                'randomize_questions' => true,
            ],
            [
                'quiz_title' => 'Vue.js Fundamentals Quiz',
                'instructions' => 'Test your understanding of Vue.js concepts including reactivity, components, and composition API.',
                'time_limit_minutes' => 25,
                'passing_score' => 70.00,
                'max_attempts' => 3,
                'show_correct_answers' => true,
                'randomize_questions' => true,
            ],
            [
                'quiz_title' => 'HTML & CSS Quiz',
                'instructions' => 'Basic quiz covering HTML5 and CSS3 fundamentals.',
                'time_limit_minutes' => 15,
                'passing_score' => 60.00,
                'max_attempts' => 5,
                'show_correct_answers' => true,
                'randomize_questions' => false,
            ],
            [
                'quiz_title' => 'Flutter Widget Quiz',
                'instructions' => 'Test your knowledge of Flutter widgets and Dart programming.',
                'time_limit_minutes' => 30,
                'passing_score' => 75.00,
                'max_attempts' => 2,
                'show_correct_answers' => false,
                'randomize_questions' => true,
            ],
            [
                'quiz_title' => 'Python Basics Quiz',
                'instructions' => 'Assess your Python programming fundamentals.',
                'time_limit_minutes' => 20,
                'passing_score' => 70.00,
                'max_attempts' => 3,
                'show_correct_answers' => true,
                'randomize_questions' => true,
            ],
        ];

        $quizzes = [];
        foreach ($quizzesData as $data) {
            $quiz = Quiz::create($data);
            $quizzes[] = $quiz;

            // Create questions for each quiz
            $this->createQuizQuestions($quiz);
        }

        return $quizzes;
    }

    private function createQuizQuestions(Quiz $quiz): void
    {
        $questionsData = [
            // Sample questions for different topics
            [
                'question_text' => 'What is the correct way to define a route in Laravel?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'Routes in Laravel are defined using the Route facade.',
                'options' => [
                    ['text' => 'Route::get(\'/path\', [Controller::class, \'method\']);', 'correct' => true],
                    ['text' => 'new Route(\'/path\', \'Controller@method\');', 'correct' => false],
                    ['text' => 'define_route(\'/path\', \'method\');', 'correct' => false],
                    ['text' => 'Router::add(\'/path\', function() {});', 'correct' => false],
                ],
            ],
            [
                'question_text' => 'Which artisan command creates a new controller?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'The make:controller command is used to generate controllers.',
                'options' => [
                    ['text' => 'php artisan make:controller', 'correct' => true],
                    ['text' => 'php artisan create:controller', 'correct' => false],
                    ['text' => 'php artisan new:controller', 'correct' => false],
                    ['text' => 'php artisan generate:controller', 'correct' => false],
                ],
            ],
            [
                'question_text' => 'Eloquent is Laravel\'s ORM for database operations.',
                'question_type' => 'true_false',
                'points' => 5,
                'explanation' => 'Eloquent is indeed Laravel\'s built-in ORM.',
                'options' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
            [
                'question_text' => 'What method is used to retrieve all records from a model?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'The all() method retrieves all records from the database table.',
                'options' => [
                    ['text' => 'Model::all()', 'correct' => true],
                    ['text' => 'Model::fetch()', 'correct' => false],
                    ['text' => 'Model::getAll()', 'correct' => false],
                    ['text' => 'Model::retrieve()', 'correct' => false],
                ],
            ],
            [
                'question_text' => 'Which directive is used for conditional rendering in Blade?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => '@if is the Blade directive for conditional rendering.',
                'options' => [
                    ['text' => '@if / @endif', 'correct' => true],
                    ['text' => '#if / #endif', 'correct' => false],
                    ['text' => '{{if}} / {{/if}}', 'correct' => false],
                    ['text' => '<?if?> / <?endif?>', 'correct' => false],
                ],
            ],
            [
                'question_text' => 'Middleware can be used to filter HTTP requests.',
                'question_type' => 'true_false',
                'points' => 5,
                'explanation' => 'Middleware provides a mechanism for filtering HTTP requests.',
                'options' => [
                    ['text' => 'True', 'correct' => true],
                    ['text' => 'False', 'correct' => false],
                ],
            ],
            [
                'question_text' => 'What is the default database connection in Laravel?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'MySQL is the default database connection, but SQLite is often used for development.',
                'options' => [
                    ['text' => 'MySQL', 'correct' => true],
                    ['text' => 'PostgreSQL', 'correct' => false],
                    ['text' => 'MongoDB', 'correct' => false],
                    ['text' => 'Oracle', 'correct' => false],
                ],
            ],
        ];

        foreach ($questionsData as $order => $qData) {
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_text' => $qData['question_text'],
                'question_type' => $qData['question_type'],
                'points' => $qData['points'],
                'question_order' => $order + 1,
                'explanation' => $qData['explanation'],
            ]);

            foreach ($qData['options'] as $optOrder => $opt) {
                QuizOption::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['text'],
                    'is_correct' => $opt['correct'],
                    'option_order' => $optOrder + 1,
                ]);
            }
        }
    }

    private function createModulesAndLessons(array $courses, array $quizzes): void
    {
        $moduleTemplates = [
            'Introduction' => [
                ['title' => 'Welcome to the Course', 'type' => 'video', 'duration' => 5],
                ['title' => 'Course Overview', 'type' => 'video', 'duration' => 10],
                ['title' => 'Setting Up Your Environment', 'type' => 'text', 'duration' => 15],
            ],
            'Core Concepts' => [
                ['title' => 'Understanding the Basics', 'type' => 'video', 'duration' => 20],
                ['title' => 'Key Terminology', 'type' => 'text', 'duration' => 10],
                ['title' => 'Fundamental Principles', 'type' => 'video', 'duration' => 25],
            ],
            'Building Projects' => [
                ['title' => 'Project Setup', 'type' => 'video', 'duration' => 15],
                ['title' => 'Implementing Features', 'type' => 'video', 'duration' => 35],
                ['title' => 'Testing Your Code', 'type' => 'text', 'duration' => 20],
            ],
            'Advanced Topics' => [
                ['title' => 'Performance Optimization', 'type' => 'video', 'duration' => 25],
                ['title' => 'Best Practices', 'type' => 'text', 'duration' => 20],
                ['title' => 'Security Considerations', 'type' => 'video', 'duration' => 30],
            ],
            'Conclusion' => [
                ['title' => 'Course Summary', 'type' => 'video', 'duration' => 10],
                ['title' => 'Final Assessment', 'type' => 'quiz', 'duration' => 20],
                ['title' => 'Next Steps', 'type' => 'text', 'duration' => 5],
            ],
        ];

        $quizIndex = 0;
        foreach ($courses as $course) {
            $moduleOrder = 0;
            foreach ($moduleTemplates as $moduleName => $lessons) {
                $module = CourseModule::create([
                    'course_id' => $course->id,
                    'module_title' => $moduleName,
                    'module_order' => $moduleOrder++,
                    'description' => "Learn about {$moduleName} in this comprehensive module.",
                ]);

                $lessonOrder = 0;
                foreach ($lessons as $lessonData) {
                    $quizId = null;
                    if ($lessonData['type'] === 'quiz' && isset($quizzes[$quizIndex % count($quizzes)])) {
                        $quizId = $quizzes[$quizIndex % count($quizzes)]->id;
                        $quizIndex++;
                    }

                    Lesson::create([
                        'course_id' => $course->id,
                        'module_id' => $module->id,
                        'lesson_title' => $lessonData['title'],
                        'lesson_type' => $lessonData['type'],
                        'lesson_order' => $lessonOrder++,
                        'description' => "Learn about {$lessonData['title']} in this lesson.",
                        'content' => $this->generateLessonContent($lessonData['title']),
                        'video_duration' => $lessonData['type'] === 'video' ? $lessonData['duration'] * 60 : null,
                        'quiz_id' => $quizId,
                        'is_mandatory' => $lessonOrder <= 2,
                        'duration_minutes' => $lessonData['duration'],
                    ]);
                }
            }
        }
    }

    private function generateLessonContent(string $title): string
    {
        return "# {$title}\n\n" .
            "## Overview\n\n" .
            "In this lesson, you will learn about {$title}. This is an important topic that will help you understand the core concepts.\n\n" .
            "## Key Points\n\n" .
            "- Understanding the fundamentals\n" .
            "- Practical applications\n" .
            "- Best practices and tips\n" .
            "- Common mistakes to avoid\n\n" .
            "## Summary\n\n" .
            "By the end of this lesson, you should have a solid understanding of {$title} and be ready to apply these concepts in your projects.";
    }

    private function createStudents(): array
    {
        $studentsData = [
            [
                'username' => 'student1',
                'email' => 'student1@test.com',
                'full_name' => 'Alice Johnson',
                'phone' => '+1234567001',
                'gender' => 'female',
                'date_of_birth' => '1998-03-15',
            ],
            [
                'username' => 'student2',
                'email' => 'student2@test.com',
                'full_name' => 'Bob Smith',
                'phone' => '+1234567002',
                'gender' => 'male',
                'date_of_birth' => '1995-07-22',
            ],
            [
                'username' => 'student3',
                'email' => 'student3@test.com',
                'full_name' => 'Carol Williams',
                'phone' => '+1234567003',
                'gender' => 'female',
                'date_of_birth' => '2000-11-08',
            ],
            [
                'username' => 'student4',
                'email' => 'student4@test.com',
                'full_name' => 'David Brown',
                'phone' => '+1234567004',
                'gender' => 'male',
                'date_of_birth' => '1997-05-30',
            ],
            [
                'username' => 'student5',
                'email' => 'student5@test.com',
                'full_name' => 'Eva Martinez',
                'phone' => '+1234567005',
                'gender' => 'female',
                'date_of_birth' => '1999-09-12',
            ],
        ];

        $students = [];
        $studentNumber = 100;

        foreach ($studentsData as $data) {
            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'user_type' => 'student',
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'status' => 'active',
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_id_number' => 'STU-2024-' . str_pad($studentNumber++, 6, '0', STR_PAD_LEFT),
                'enrollment_date' => now()->subDays(rand(30, 365)),
                'student_status' => 'active',
            ]);

            $students[] = ['user' => $user, 'student' => $student];
        }

        return $students;
    }

    private function createEnrollments(array $students, array $courses): array
    {
        $enrollments = [];
        $publishedCourses = array_filter($courses, fn($c) => $c->status === 'published');
        $publishedCourses = array_values($publishedCourses);

        foreach ($students as $studentData) {
            // Each student enrolls in 2-4 random courses
            $enrollCount = rand(2, min(4, count($publishedCourses)));
            $shuffledCourses = $publishedCourses;
            shuffle($shuffledCourses);

            for ($i = 0; $i < $enrollCount; $i++) {
                $course = $shuffledCourses[$i];
                $enrolledAt = now()->subDays(rand(1, 180));

                // Determine enrollment status
                $statuses = ['active', 'active', 'active', 'completed'];
                $status = $statuses[array_rand($statuses)];
                $progress = $status === 'completed' ? 100 : rand(0, 90);

                $enrollment = Enrollment::create([
                    'student_id' => $studentData['student']->id,
                    'course_id' => $course->id,
                    'enrollment_date' => $enrolledAt,
                    'completion_date' => $status === 'completed' ? $enrolledAt->copy()->addDays(rand(7, 60)) : null,
                    'progress_percentage' => $progress,
                    'status' => $status,
                    'last_accessed' => now()->subDays(rand(0, 30)),
                    'payment_status' => $course->price > 0 ? 'paid' : 'paid',
                    'certificate_issued' => $status === 'completed',
                ]);

                $enrollments[] = [
                    'enrollment' => $enrollment,
                    'student' => $studentData,
                    'course' => $course,
                ];
            }
        }

        return $enrollments;
    }

    private function createLessonProgress(array $students, array $courses): void
    {
        foreach ($students as $studentData) {
            $studentEnrollments = Enrollment::where('student_id', $studentData['student']->id)->get();

            foreach ($studentEnrollments as $enrollment) {
                $lessons = Lesson::where('course_id', $enrollment->course_id)->get();

                foreach ($lessons as $lesson) {
                    // Random chance of having progress
                    if (rand(0, 100) < $enrollment->progress_percentage + 20) {
                        $isCompleted = rand(0, 100) < 70;
                        $status = $isCompleted ? 'completed' : (rand(0, 1) ? 'in_progress' : 'not_started');

                        LessonProgress::create([
                            'student_id' => $studentData['student']->id,
                            'lesson_id' => $lesson->id,
                            'course_id' => $enrollment->course_id,
                            'status' => $status,
                            'progress_percentage' => $isCompleted ? 100 : rand(10, 90),
                            'time_spent_minutes' => rand(1, $lesson->duration_minutes ?? 30),
                            'video_last_position' => $lesson->video_duration ? rand(0, $lesson->video_duration) : null,
                            'first_accessed' => now()->subDays(rand(1, 60)),
                            'last_accessed' => now()->subDays(rand(0, 30)),
                            'completed_at' => $isCompleted ? now()->subDays(rand(1, 30)) : null,
                        ]);
                    }
                }
            }
        }
    }

    private function createQuizAttempts(array $students, array $quizzes): void
    {
        foreach ($students as $studentData) {
            // Each student attempts 1-3 quizzes
            $attemptCount = rand(1, min(3, count($quizzes)));
            $shuffledQuizzes = $quizzes;
            shuffle($shuffledQuizzes);

            for ($i = 0; $i < $attemptCount; $i++) {
                $quiz = $shuffledQuizzes[$i];
                $questions = QuizQuestion::where('quiz_id', $quiz->id)->with('options')->get();

                if ($questions->isEmpty()) {
                    continue;
                }

                $totalPoints = 0;
                $maxPoints = $questions->sum('points');
                $startedAt = now()->subDays(rand(1, 30));

                // Create attempt
                $attempt = QuizAttempt::create([
                    'quiz_id' => $quiz->id,
                    'student_id' => $studentData['student']->id,
                    'attempt_number' => 1,
                    'score_percentage' => 0,
                    'total_points' => 0,
                    'max_points' => $maxPoints,
                    'passed' => false,
                    'started_at' => $startedAt,
                    'submitted_at' => $startedAt->copy()->addMinutes(rand(5, $quiz->time_limit_minutes ?? 30)),
                    'time_taken_minutes' => rand(5, $quiz->time_limit_minutes ?? 30),
                ]);

                // Answer questions
                foreach ($questions as $question) {
                    $options = $question->options;
                    if ($options->isEmpty()) continue;

                    $correctOption = $options->firstWhere('is_correct', true);
                    if (!$correctOption) {
                        $correctOption = $options->first();
                    }

                    // 70% chance of answering correctly
                    $answeredCorrectly = rand(1, 100) <= 70;
                    $incorrectOptions = $options->where('is_correct', false);
                    $selectedOption = $answeredCorrectly
                        ? $correctOption
                        : ($incorrectOptions->isNotEmpty() ? $incorrectOptions->random() : $correctOption);

                    $pointsEarned = $selectedOption->is_correct ? $question->points : 0;
                    $totalPoints += $pointsEarned;

                    QuizAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'selected_option_id' => $selectedOption->id,
                        'is_correct' => $selectedOption->is_correct,
                        'points_earned' => $pointsEarned,
                    ]);
                }

                // Update attempt with score
                $scorePercentage = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 2) : 0;
                $attempt->update([
                    'score_percentage' => $scorePercentage,
                    'total_points' => $totalPoints,
                    'passed' => $scorePercentage >= $quiz->passing_score,
                ]);
            }
        }
    }

    private function createPayments(array $students, array $courses): void
    {
        $paidCourses = array_filter($courses, fn($c) => $c->price > 0 && $c->status === 'published');
        $paidCourses = array_values($paidCourses);

        if (empty($paidCourses)) {
            return;
        }

        foreach ($students as $studentData) {
            // Each student has 1-2 payments
            $paymentCount = rand(1, 2);

            for ($i = 0; $i < $paymentCount; $i++) {
                $course = $paidCourses[array_rand($paidCourses)];

                // Check if payment already exists
                $exists = Payment::where('student_id', $studentData['student']->id)
                    ->where('course_id', $course->id)
                    ->exists();

                if ($exists) continue;

                Payment::create([
                    'student_id' => $studentData['student']->id,
                    'course_id' => $course->id,
                    'amount' => $course->price,
                    'payment_method' => ['credit_card', 'paypal', 'bank_transfer'][rand(0, 2)],
                    'transaction_id' => 'TXN_' . strtoupper(Str::random(12)),
                    'payment_date' => now()->subDays(rand(1, 180)),
                    'status' => 'completed',
                ]);
            }
        }
    }

    private function createCertificates(array $students, array $courses): void
    {
        foreach ($students as $studentData) {
            $completedEnrollments = Enrollment::where('student_id', $studentData['student']->id)
                ->where('status', 'completed')
                ->get();

            foreach ($completedEnrollments as $enrollment) {
                // Check if certificate already exists
                $exists = Certificate::where('student_id', $studentData['student']->id)
                    ->where('course_id', $enrollment->course_id)
                    ->exists();

                if ($exists) continue;

                Certificate::create([
                    'student_id' => $studentData['student']->id,
                    'course_id' => $enrollment->course_id,
                    'issue_date' => $enrollment->completion_date ?? now(),
                    'certificate_code' => 'CERT-' . strtoupper(Str::random(8)),
                    'verification_url' => url('/certificates/verify/' . Str::uuid()),
                ]);
            }
        }
    }

    private function createReviews(array $students, array $courses): void
    {
        $publishedCourses = array_filter($courses, fn($c) => $c->status === 'published');
        $publishedCourses = array_values($publishedCourses);

        $reviewTexts = [
            5 => [
                'Excellent course! Highly recommended.',
                'Best course I have ever taken. Very comprehensive.',
                'Amazing content and great instructor.',
                'Learned so much from this course!',
            ],
            4 => [
                'Very good course with practical examples.',
                'Great content, would recommend to others.',
                'Good course overall, learned a lot.',
            ],
            3 => [
                'Decent course, but could use more examples.',
                'Average content, expected more depth.',
            ],
        ];

        foreach ($students as $studentData) {
            // Each student reviews 1-2 courses
            $reviewCount = rand(1, 2);
            $shuffledCourses = $publishedCourses;
            shuffle($shuffledCourses);

            for ($i = 0; $i < $reviewCount && $i < count($shuffledCourses); $i++) {
                $course = $shuffledCourses[$i];

                // Check if review already exists
                $exists = CourseReview::where('course_id', $course->id)
                    ->where('student_id', $studentData['student']->id)
                    ->exists();

                if ($exists) continue;

                $rating = [5, 5, 5, 4, 4, 3][rand(0, 5)];
                $texts = $reviewTexts[$rating];

                CourseReview::create([
                    'course_id' => $course->id,
                    'student_id' => $studentData['student']->id,
                    'rating' => $rating,
                    'review_text' => $texts[array_rand($texts)],
                    'would_recommend' => $rating >= 4,
                    'created_at' => now()->subDays(rand(1, 90)),
                ]);
            }
        }
    }

    private function createNotifications(array $students): void
    {
        $notificationTemplates = [
            [
                'title' => 'Welcome to EduPlex!',
                'message' => 'Thank you for joining our learning platform. Start exploring courses today!',
                'type' => 'info',
            ],
            [
                'title' => 'New Course Available',
                'message' => 'A new course has been added that matches your interests. Check it out!',
                'type' => 'info',
            ],
            [
                'title' => 'Course Update',
                'message' => 'New lessons have been added to a course you are enrolled in.',
                'type' => 'info',
            ],
            [
                'title' => 'Quiz Reminder',
                'message' => 'You have a pending quiz. Complete it to track your progress.',
                'type' => 'reminder',
            ],
            [
                'title' => 'Certificate Ready',
                'message' => 'Congratulations! Your course completion certificate is ready for download.',
                'type' => 'completion',
            ],
            [
                'title' => 'Enrollment Confirmed',
                'message' => 'Your enrollment has been confirmed. You now have access to the course.',
                'type' => 'enrollment',
            ],
        ];

        foreach ($students as $studentData) {
            // Each student gets 3-5 notifications
            $notifCount = rand(3, 5);

            for ($i = 0; $i < $notifCount; $i++) {
                $template = $notificationTemplates[array_rand($notificationTemplates)];

                Notification::create([
                    'user_id' => $studentData['user']->id,
                    'title' => $template['title'],
                    'message' => $template['message'],
                    'type' => $template['type'],
                    'is_read' => rand(0, 1) === 1,
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
}
