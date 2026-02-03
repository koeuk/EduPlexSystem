# EduPlex LMS Documentation

## Overview

EduPlex is a comprehensive Learning Management System (LMS) with:
- **Admin Dashboard**: Built with Laravel 11, Inertia.js, Vue.js 3, Tailwind CSS
- **Mobile API**: RESTful API with Laravel Sanctum authentication
- **Spatie Packages**: Permission, Media Library, Activity Log, Query Builder

---

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | Laravel 11 |
| Frontend | Vue.js 3 + Inertia.js |
| Styling | Tailwind CSS 4 |
| UI Components | shadcn-vue |
| Icons | Lucide Vue Next |
| Charts | Chart.js + vue-chartjs |
| Authentication | Laravel Sanctum |
| Permissions | Spatie Laravel Permission |
| File Uploads | Spatie Media Library |
| Activity Logging | Spatie Activity Log |
| Query Building | Spatie Query Builder |

---

## Installed Packages

### Composer Packages

```bash
composer require inertiajs/inertia-laravel
composer require spatie/laravel-query-builder
composer require spatie/laravel-permission
composer require spatie/laravel-medialibrary
composer require spatie/laravel-activitylog
composer require tightenco/ziggy
```

| Package | Purpose |
|---------|---------|
| `inertiajs/inertia-laravel` | Server-side adapter for Inertia.js |
| `spatie/laravel-query-builder` | Build Eloquent queries from API requests |
| `spatie/laravel-permission` | Role and permission management |
| `spatie/laravel-medialibrary` | File uploads and media management |
| `spatie/laravel-activitylog` | Audit trail and activity logging |
| `tightenco/ziggy` | Use Laravel named routes in JavaScript |

### NPM Packages

```bash
npm install vue@^3.4 @inertiajs/vue3 @vitejs/plugin-vue
npm install tailwindcss @tailwindcss/vite
npm install lucide-vue-next
npm install chart.js vue-chartjs
npm install class-variance-authority clsx tailwind-merge
npm install radix-vue @vueuse/core
```

---

## Database Structure

### Application Tables (18)

| # | Table | Description |
|---|-------|-------------|
| 1 | `users` | Base user table (admin/student) |
| 2 | `admins` | Admin profile data |
| 3 | `students` | Student profile data |
| 4 | `categories` | Course categories |
| 5 | `quizzes` | Standalone quizzes |
| 6 | `courses` | Course information |
| 7 | `course_modules` | Modules within courses |
| 8 | `lessons` | Lessons within modules |
| 9 | `quiz_questions` | Questions for quizzes |
| 10 | `quiz_options` | Options for quiz questions |
| 11 | `enrollments` | Student course enrollments |
| 12 | `lesson_progress` | Student lesson completion tracking |
| 13 | `quiz_attempts` | Student quiz attempt records |
| 14 | `quiz_answers` | Answers submitted in quiz attempts |
| 15 | `course_reviews` | Student course reviews/ratings |
| 16 | `notifications` | User notifications |
| 17 | `certificates` | Course completion certificates |
| 18 | `payments` | Payment transactions |

### Spatie Tables (Auto-created)

| Table | Package | Description |
|-------|---------|-------------|
| `roles` | Permission | User roles |
| `permissions` | Permission | Available permissions |
| `model_has_roles` | Permission | Role assignments |
| `model_has_permissions` | Permission | Direct permission assignments |
| `role_has_permissions` | Permission | Role-permission mappings |
| `media` | Media Library | Uploaded files metadata |
| `activity_log` | Activity Log | Audit trail records |

---

## Project Structure

```
app/
├── Filters/
│   └── UniversalSearchFilter.php
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── Auth/
│   │   │   └── AuthController.php
│   │   ├── Admin/
│   │   │   ├── ActivityLogController.php
│   │   │   ├── AdminUserController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── CertificateController.php
│   │   │   ├── CourseController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── EnrollmentController.php
│   │   │   ├── LessonController.php
│   │   │   ├── ModuleController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── QuestionController.php
│   │   │   ├── QuizController.php
│   │   │   ├── ReportController.php
│   │   │   ├── ReviewController.php
│   │   │   └── StudentController.php
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── CategoryController.php
│   │       ├── CertificateController.php
│   │       ├── CourseController.php
│   │       ├── DashboardController.php
│   │       ├── EnrollmentController.php
│   │       ├── LessonController.php
│   │       ├── NotificationController.php
│   │       ├── PaymentController.php
│   │       └── QuizController.php
│   └── Middleware/
│       ├── CheckAdmin.php
│       └── HandleInertiaRequests.php
├── Models/
│   ├── User.php (HasRoles, InteractsWithMedia, LogsActivity)
│   ├── Admin.php (LogsActivity)
│   ├── Student.php (LogsActivity)
│   ├── Category.php (LogsActivity)
│   ├── Course.php (InteractsWithMedia, LogsActivity)
│   ├── CourseModule.php (LogsActivity)
│   ├── Lesson.php (InteractsWithMedia, LogsActivity)
│   ├── Quiz.php (LogsActivity)
│   ├── QuizQuestion.php (InteractsWithMedia, LogsActivity)
│   ├── QuizOption.php
│   ├── Enrollment.php (LogsActivity)
│   ├── LessonProgress.php
│   ├── QuizAttempt.php (LogsActivity)
│   ├── QuizAnswer.php
│   ├── CourseReview.php (LogsActivity)
│   ├── Notification.php
│   ├── Certificate.php (InteractsWithMedia, LogsActivity)
│   └── Payment.php (LogsActivity)

database/
├── migrations/
│   └── (18 app migrations + Spatie migrations)
└── seeders/
    ├── DatabaseSeeder.php
    └── PermissionSeeder.php

routes/
├── web.php (Admin Dashboard routes)
└── api.php (Mobile API routes)

resources/js/
├── app.js
├── Components/
│   ├── ComingSoon.vue
│   ├── MediaUploader.vue
│   ├── PermissionSelector.vue
│   └── ui/ (shadcn-vue components)
├── Layouts/
│   ├── AdminLayout.vue
│   └── GuestLayout.vue
└── Pages/
    ├── Auth/
    │   └── Login.vue
    └── Admin/
        ├── Dashboard.vue
        ├── Admins/ (Index, Create, Edit)
        └── ... (Other pages as Coming Soon)
```

---

## Roles & Permissions

### Roles

| Role | Description |
|------|-------------|
| Super Admin | Full system access |
| Admin | All permissions except admin management |
| Content Manager | Course, module, lesson, quiz management |
| Support | View-only access to students, enrollments, payments |

### Permissions

```
students.view, students.create, students.edit, students.delete
courses.view, courses.create, courses.edit, courses.delete, courses.publish
modules.view, modules.create, modules.edit, modules.delete
lessons.view, lessons.create, lessons.edit, lessons.delete
quizzes.view, quizzes.create, quizzes.edit, quizzes.delete
enrollments.view, enrollments.create, enrollments.edit, enrollments.delete
payments.view, payments.edit, payments.refund
certificates.view, certificates.issue, certificates.revoke
categories.view, categories.create, categories.edit, categories.delete
reviews.view, reviews.delete
reports.view, reports.export
admins.view, admins.create, admins.edit, admins.delete
activity-log.view
```

---

## Media Collections

| Model | Collection | Max Size | Conversions |
|-------|------------|----------|-------------|
| User | profile_picture | 2 MB | thumb (150x150) |
| Course | thumbnail | 2 MB | thumb (400x300), small (200x150) |
| Lesson | video | 500 MB | - |
| Lesson | thumbnail | 2 MB | thumb (400x300) |
| Lesson | documents | 10 MB | - |
| QuizQuestion | question_image | 1 MB | thumb (400x300) |
| Certificate | certificate | 10 MB | - |

---

## Admin Dashboard Routes

All routes require authentication and admin middleware.

### Route Groups

| Group | Prefix | Middleware |
|-------|--------|------------|
| Dashboard | /admin/dashboard | auth, admin |
| Admins | /admin/admins | permission:admins.* |
| Students | /admin/students | permission:students.* |
| Categories | /admin/categories | permission:categories.* |
| Courses | /admin/courses | permission:courses.* |
| Modules | /admin/courses/{course}/modules | permission:modules.* |
| Lessons | /admin/courses/{course}/lessons | permission:lessons.* |
| Quizzes | /admin/quizzes | permission:quizzes.* |
| Enrollments | /admin/enrollments | permission:enrollments.* |
| Payments | /admin/payments | permission:payments.* |
| Certificates | /admin/certificates | permission:certificates.* |
| Reviews | /admin/reviews | permission:reviews.* |
| Reports | /admin/reports | permission:reports.* |
| Activity Log | /admin/activity-log | permission:activity-log.view |

---

## Mobile API

### Base URL
`/api`

### Authentication
Laravel Sanctum (Bearer Token)

### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /auth/register | Register student |
| POST | /auth/login | Login student |
| GET | /certificates/verify/{code} | Verify certificate |

### Protected Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /auth/profile | Get profile |
| PUT | /auth/profile | Update profile |
| PUT | /auth/change-password | Change password |
| POST | /auth/logout | Logout |
| GET | /categories | List categories |
| GET | /categories/{id} | Get category |
| GET | /categories/{id}/courses | Get category courses |
| GET | /courses | List courses |
| GET | /courses/{id} | Get course details |
| GET | /courses/{id}/modules | Get course modules (enrolled only) |
| GET | /courses/{id}/reviews | Get course reviews |
| POST | /courses/{id}/reviews | Add review |
| PUT | /courses/reviews/{id} | Update review |
| DELETE | /courses/reviews/{id} | Delete review |
| GET | /enrollments | List enrollments |
| POST | /enrollments | Enroll in course |
| GET | /enrollments/{id} | Get enrollment details |
| DELETE | /enrollments/{id} | Drop enrollment |
| GET | /lessons/{id} | Get lesson |
| POST | /lessons/{id}/progress | Update progress |
| GET | /lessons/{id}/progress | Get progress |
| GET | /quizzes/{id} | Get quiz |
| POST | /quizzes/{id}/attempts | Start attempt |
| GET | /quizzes/{id}/attempts | Get attempts history |
| PUT | /quizzes/attempts/{id} | Submit attempt |
| GET | /quizzes/attempts/{id} | Get attempt results |
| GET | /payments | List payments |
| POST | /payments | Process payment |
| GET | /payments/{id} | Get payment details |
| GET | /certificates | List certificates |
| GET | /certificates/{id} | Get certificate |
| GET | /certificates/{id}/download | Download PDF |
| GET | /notifications | List notifications |
| PUT | /notifications/{id}/read | Mark as read |
| PUT | /notifications/read-all | Mark all as read |
| DELETE | /notifications/{id} | Delete notification |
| GET | /dashboard/stats | Get stats |
| GET | /dashboard/recent-activity | Get recent activity |
| GET | /dashboard/activity-log | Get activity log |
| GET | /dashboard/continue-learning | Get in-progress courses |

See [API_Documentation.md](./API_Documentation.md) for complete API documentation.

---

## Running the Application

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL/PostgreSQL

### Setup Commands

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# DB_CONNECTION=mysql
# DB_DATABASE=eduplex
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations and seed
php artisan migrate:fresh --seed

# Create storage link
php artisan storage:link
```

### Development Server

```bash
# Terminal 1 - Laravel backend
php artisan serve

# Terminal 2 - Vite frontend
npm run dev
```

### Default Admin Account

| Field | Value |
|-------|-------|
| Email | admin@eduplex.com |
| Password | password |
| Role | Super Admin |

---

## Query Builder Usage

### Filtering

```
?filter[status]=active
?filter[search]=john
?filter[level]=beginner
```

### Sorting

```
?sort=created_at        # Ascending
?sort=-created_at       # Descending
?sort=name,-created_at  # Multiple
```

### Pagination

```
?per_page=20&page=2
```

---

## Activity Logging

All significant actions are logged automatically:

- User registration, login, logout
- Profile updates
- Course enrollment, completion, drop
- Lesson progress
- Quiz attempts
- Payments
- Certificate downloads
- Review actions
- Admin CRUD operations

---

## Vue Page Status

| Page | Status |
|------|--------|
| Login | Functional |
| Dashboard | Functional (with charts) |
| Admins (Index/Create/Edit) | Functional |
| All other pages | Coming Soon placeholder |

---

## File Locations

| Component | Location |
|-----------|----------|
| Migrations | `database/migrations/` |
| Models | `app/Models/` |
| Admin Controllers | `app/Http/Controllers/Admin/` |
| API Controllers | `app/Http/Controllers/Api/` |
| Middleware | `app/Http/Middleware/` |
| Web Routes | `routes/web.php` |
| API Routes | `routes/api.php` |
| Vue Pages | `resources/js/Pages/` |
| Vue Components | `resources/js/Components/` |
| Seeders | `database/seeders/` |
| Spatie Config | `config/permission.php` |
