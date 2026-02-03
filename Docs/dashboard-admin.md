Create a complete Laravel Learning Management System (LMS) Admin Dashboard using Laravel 11, Inertia.js, Vue.js 3, Tailwind CSS, shadcn-vue components, and Spatie packages (spatie/laravel-permission, spatie/laravel-query-builder, spatie/laravel-medialibrary, spatie/laravel-activitylog).

SPATIE PACKAGES TO USE:
1. spatie/laravel-permission: for roles and permissions management
2. spatie/laravel-query-builder: for advanced filtering, sorting, and searching
3. spatie/laravel-medialibrary: for handling file uploads (profile pictures, course thumbnails, videos)
4. spatie/laravel-activitylog: for audit trail and activity logging

DATABASE SCHEMA (17 tables + Spatie tables):

1. users: id, username (unique), email (unique), password, full_name, phone, user_type (admin/student), date_of_birth, gender, address, status (active/inactive/suspended), timestamps
   - Remove profile_picture field (handled by spatie/laravel-medialibrary)

2. admins: id, user_id (FK users, unique), department, created_at

3. students: id, user_id (FK users, unique), student_id_number (unique), enrollment_date, student_status (active/inactive/graduated/suspended), created_at

4. categories: id, category_name, description, icon, is_active (default true), timestamps

5. courses: id, course_name, course_code (unique), description, category_id (FK categories), level (beginner/intermediate/advanced), duration_hours, price (decimal), instructor_name, admin_id (FK admins), status (draft/published/archived), enrollment_limit, is_featured (default false), timestamps
   - Remove thumbnail field (handled by spatie/laravel-medialibrary)

6. course_modules: id, course_id (FK courses), module_title, module_order, description, created_at

7. lessons: id, course_id (FK courses), module_id (FK course_modules, nullable), lesson_title, lesson_type (video/text/quiz/assignment/document), lesson_order, description, content (text), video_duration (seconds), quiz_id (FK quizzes, nullable), is_mandatory (default false), duration_minutes, timestamps
   - Remove video_url, video_thumbnail fields (handled by spatie/laravel-medialibrary)

8. quizzes: id, quiz_title, instructions, time_limit_minutes, passing_score (decimal, default 70.00), max_attempts, show_correct_answers (boolean), randomize_questions (boolean), created_at

9. quiz_questions: id, quiz_id (FK quizzes), question_text, question_type (multiple_choice/true_false/short_answer/essay), points (default 1), question_order, explanation
   - Remove image_url field (handled by spatie/laravel-medialibrary)

10. quiz_options: id, question_id (FK quiz_questions), option_text, is_correct (boolean), option_order

11. enrollments: id, student_id (FK students), course_id (FK courses), enrollment_date, completion_date, progress_percentage (decimal), status (active/completed/dropped/expired), last_accessed, payment_status (pending/paid/failed/refunded), certificate_issued (boolean)

12. lesson_progress: id, student_id (FK students), lesson_id (FK lessons), course_id (FK courses), status (not_started/in_progress/completed), progress_percentage (decimal), time_spent_minutes, video_last_position (seconds), scroll_position, first_accessed, last_accessed, completed_at

13. quiz_attempts: id, quiz_id (FK quizzes), student_id (FK students), attempt_number, score_percentage (decimal), total_points, max_points, passed (boolean), started_at, submitted_at, time_taken_minutes

14. quiz_answers: id, attempt_id (FK quiz_attempts), question_id (FK quiz_questions), selected_option_id (FK quiz_options, nullable), answer_text, is_correct (boolean), points_earned

15. course_reviews: id, course_id (FK courses), student_id (FK students), rating (1-5), review_text, would_recommend (boolean), timestamps

16. notifications: id, user_id (FK users), title, message, type (info/success/warning/error/enrollment/completion/reminder), related_id, is_read (boolean), created_at

17. certificates: id, student_id (FK students), course_id (FK courses), issue_date, certificate_code (unique), verification_url
    - Remove certificate_url field (handled by spatie/laravel-medialibrary)

18. payments: id, student_id (FK students), course_id (FK courses), amount (decimal), payment_method, transaction_id (unique), payment_date, status (pending/completed/failed/refunded)

SPATIE TABLES (auto-created):
- roles: id, name, guard_name, created_at, updated_at
- permissions: id, name, guard_name, created_at, updated_at
- model_has_roles: role_id, model_type, model_id
- model_has_permissions: permission_id, model_type, model_id
- role_has_permissions: permission_id, role_id
- media: id, model_type, model_id, uuid, collection_name, name, file_name, mime_type, disk, conversions_disk, size, manipulations, custom_properties, generated_conversions, responsive_images, order_column, created_at, updated_at
- activity_log: id, log_name, description, subject_type, subject_id, causer_type, causer_id, properties, batch_uuid, event, created_at, updated_at

REQUIREMENTS:

SPATIE SETUP:
1. Install packages:
   - composer require spatie/laravel-permission
   - composer require spatie/laravel-query-builder
   - composer require spatie/laravel-medialibrary
   - composer require spatie/laravel-activitylog

2. Publish configurations:
   - php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   - php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
   - php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"

3. Run migrations:
   - php artisan migrate

PERMISSIONS SETUP:
Define permissions:
- students.view, students.create, students.edit, students.delete
- courses.view, courses.create, courses.edit, courses.delete, courses.publish
- modules.view, modules.create, modules.edit, modules.delete
- lessons.view, lessons.create, lessons.edit, lessons.delete
- quizzes.view, quizzes.create, quizzes.edit, quizzes.delete
- enrollments.view, enrollments.create, enrollments.edit, enrollments.delete
- payments.view, payments.edit, payments.refund
- certificates.view, certificates.issue, certificates.revoke
- categories.view, categories.create, categories.edit, categories.delete
- reviews.view, reviews.delete
- reports.view, reports.export
- admins.view, admins.create, admins.edit, admins.delete

Create roles:
- Super Admin: all permissions
- Admin: all permissions except admins.*
- Content Manager: courses.*, modules.*, lessons.*, quizzes.*
- Support: students.view, enrollments.view, payments.view

MODELS (with Spatie traits):

User model:
- Use HasRoles trait from spatie/laravel-permission
- Use HasMediaTrait from spatie/laravel-medialibrary (for profile_picture)
- Use LogsActivity trait from spatie/laravel-activitylog
- Relationships: hasOne Admin, hasOne Student, hasMany Notification, roles(), permissions()
- Methods: isAdmin(), isStudent(), hasPermissionTo(), hasRole()
- Media collections: registerMediaCollections() - 'profile_picture' collection
- Activity log: getActivitylogOptions() - log name, only, except fields

Admin model:
- Use LogsActivity trait
- belongsTo User, hasMany Course
- Activity log for department changes

Student model:
- Use LogsActivity trait
- belongsTo User, hasMany Enrollment/LessonProgress/QuizAttempt/CourseReview/Certificate/Payment
- Activity log for status changes

Category model:
- Use LogsActivity trait
- hasMany Course
- Activity log for create, update, delete

Course model:
- Use HasMediaTrait (for thumbnail)
- Use LogsActivity trait
- belongsTo Category/Admin, hasMany CourseModule/Lesson/Enrollment/CourseReview/Certificate/Payment
- Media collections: 'thumbnail' (with responsive images, max 2MB)
- Activity log for status changes, publish/unpublish

CourseModule model:
- Use LogsActivity trait
- belongsTo Course, hasMany Lesson

Lesson model:
- Use HasMediaTrait (for video, documents)
- Use LogsActivity trait
- belongsTo Course/CourseModule/Quiz, hasMany LessonProgress
- Media collections: 'video' (max 500MB), 'documents' (PDF, DOCX), 'thumbnail'

Quiz model:
- Use LogsActivity trait
- hasMany Lesson/QuizQuestion/QuizAttempt

QuizQuestion model:
- Use HasMediaTrait (for question images)
- Use LogsActivity trait
- belongsTo Quiz, hasMany QuizOption/QuizAnswer
- Media collections: 'question_image'

QuizOption model:
- belongsTo QuizQuestion, hasMany QuizAnswer

Enrollment model:
- Use LogsActivity trait
- belongsTo Student/Course
- Activity log for status changes, completion

LessonProgress model:
- belongsTo Student/Lesson/Course

QuizAttempt model:
- Use LogsActivity trait
- belongsTo Quiz/Student, hasMany QuizAnswer
- Activity log for submit

QuizAnswer model:
- belongsTo QuizAttempt/QuizQuestion/QuizOption (as selectedOption)

CourseReview model:
- Use LogsActivity trait
- belongsTo Course/Student
- Activity log for create, delete

Notification model:
- belongsTo User

Certificate model:
- Use HasMediaTrait (for certificate PDF)
- Use LogsActivity trait
- belongsTo Student/Course
- Media collections: 'certificate' (PDF)
- Activity log for issue, revoke

Payment model:
- Use LogsActivity trait
- belongsTo Student/Course
- Activity log for status changes, refunds

MIGRATIONS:
- Create all 18 migrations with proper foreign keys, indexes, unique constraints
- Run Spatie package migrations
- Create PermissionSeeder to seed roles and permissions

CONTROLLERS (Inertia.js) with Spatie Query Builder:

AuthController:
- login, logout
- Use Spatie Permission to check roles

DashboardController:
- stats with activity log recent activities
- Use QueryBuilder for filtering and sorting

AdminUserController:
- CRUD for admins
- Middleware: permission:admins.view|admins.create|admins.edit|admins.delete
- Use QueryBuilder for filtering (allowedFilters: name, email, department)
- Use QueryBuilder for sorting (allowedSorts: name, email, created_at)
- Assign roles and permissions to admins
- Activity log: "Admin created", "Admin updated", "Admin deleted"

StudentController:
- CRUD for students
- Middleware: permission:students.view|students.create|students.edit|students.delete
- Use QueryBuilder (allowedFilters: full_name, email, student_status, student_id_number)
- Use QueryBuilder (allowedSorts: full_name, enrollment_date, created_at)
- Activity log: "Student created", "Student updated", "Student status changed"

CategoryController:
- CRUD for categories
- Middleware: permission:categories.view|categories.create|categories.edit|categories.delete
- Use QueryBuilder (allowedFilters: category_name, is_active)
- Use QueryBuilder (allowedSorts: category_name, created_at)
- Activity log

CourseController:
- CRUD for courses
- Middleware: permission:courses.view|courses.create|courses.edit|courses.delete
- Use QueryBuilder (allowedFilters: course_name, status, level, category_id, is_featured)
- Use QueryBuilder (allowedSorts: course_name, price, created_at)
- Handle thumbnail upload with Spatie Media Library
- Change status with permission:courses.publish
- Activity log: "Course created", "Course published", "Course archived"

ModuleController:
- CRUD for modules
- Middleware: permission:modules.*
- Use QueryBuilder
- Reorder modules
- Activity log

LessonController:
- CRUD for lessons
- Middleware: permission:lessons.*
- Use QueryBuilder
- Handle video/document uploads with Spatie Media Library
- Activity log

QuizController:
- CRUD for quizzes
- Middleware: permission:quizzes.*
- Use QueryBuilder
- Activity log

QuestionController:
- CRUD for questions
- Middleware: permission:quizzes.*
- Handle question image uploads with Spatie Media Library
- Activity log

OptionController:
- CRUD for options
- Middleware: permission:quizzes.*

EnrollmentController:
- CRUD for enrollments
- Middleware: permission:enrollments.*
- Use QueryBuilder (allowedFilters: status, student_id, course_id, payment_status)
- Use QueryBuilder (allowedSorts: enrollment_date, progress_percentage)
- Activity log: "Student enrolled", "Enrollment completed", "Enrollment dropped"

PaymentController:
- List all payments
- Middleware: permission:payments.view|payments.edit|payments.refund
- Use QueryBuilder (allowedFilters: status, payment_method, student_id, course_id, payment_date)
- Use QueryBuilder (allowedSorts: payment_date, amount)
- Update status, process refunds
- Activity log: "Payment completed", "Payment refunded"

CertificateController:
- List all certificates
- Middleware: permission:certificates.view|certificates.issue|certificates.revoke
- Use QueryBuilder
- Issue certificate manually (generate PDF with Spatie Media Library)
- Revoke certificate
- Activity log: "Certificate issued", "Certificate revoked"

ReviewController:
- List all reviews
- Middleware: permission:reviews.view|reviews.delete
- Use QueryBuilder (allowedFilters: rating, course_id, student_id)
- Delete inappropriate reviews
- Activity log: "Review deleted"

NotificationController:
- List notifications
- Send to users (single/bulk)
- Mark as read

ReportController:
- Middleware: permission:reports.view|reports.export
- Student reports, enrollment reports, revenue reports, course performance
- Use QueryBuilder for filtering and date ranges
- Export to CSV/Excel

ActivityLogController:
- View activity logs
- Middleware: permission:admins.view
- Use QueryBuilder (allowedFilters: causer_id, subject_type, event, created_at)
- Show "who did what and when"

VUE.JS PAGES (with shadcn-vue):
[Same as previous prompt but add:]
- Admin/ActivityLog/Index.vue: view all activity logs with filters
- Admin/Admins pages: include role and permission assignment
- All forms: use Spatie Media Library for file uploads with preview and progress

COMPONENTS:
- MediaUploader.vue: component for file uploads using Spatie Media Library
- PermissionSelector.vue: checkbox list for assigning permissions
- RoleSelector.vue: dropdown for assigning roles
- ActivityLogTable.vue: table showing activity logs

ROUTES (web.php):
- All routes use permission middleware
- Example: Route::middleware(['auth', 'permission:students.view'])->get('/admin/students', ...)

MIDDLEWARE:
- CheckAdmin: verify user has 'admin' or 'super-admin' role
- CheckPermission: verify user has specific permission using Spatie

FEATURES:
- Role-based access control with Spatie Permission
- Advanced filtering, sorting, searching with Spatie Query Builder
- File uploads (images, videos, PDFs) with Spatie Media Library
- Automatic thumbnail generation for images
- Responsive images for thumbnails
- Activity logging for audit trail with Spatie Activity Log
- Multiple admins with different roles and permissions
- Permission-based UI (hide/show buttons based on permissions)
- Beautiful UI with Tailwind CSS and shadcn-vue

SEEDER:
Create PermissionSeeder:
- Create all permissions
- Create roles (Super Admin, Admin, Content Manager, Support)
- Assign permissions to roles
- Create default super admin user with all permissions

Use Spatie packages for all file handling, permissions, activity logging, and query building.
Follow Laravel best practices.
Use Inertia.js for seamless SPA experience.