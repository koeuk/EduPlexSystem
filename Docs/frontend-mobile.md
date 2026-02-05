# EduPlex LMS Mobile API Specification

Create a complete Laravel REST API for a Learning Management System (LMS) Mobile App using Laravel 11, Laravel Sanctum for authentication, and Spatie packages (spatie/laravel-query-builder, spatie/laravel-medialibrary, spatie/laravel-activitylog).

## SPATIE PACKAGES TO USE:
1. spatie/laravel-query-builder: for advanced filtering, sorting, and searching in API endpoints
2. spatie/laravel-medialibrary: for handling file uploads and serving media
3. spatie/laravel-activitylog: for tracking student activities

## DATABASE SCHEMA (same as Admin Dashboard - 18 tables + Spatie tables)

## SPATIE SETUP:
1. Install packages:
   - composer require spatie/laravel-query-builder
   - composer require spatie/laravel-medialibrary
   - composer require spatie/laravel-activitylog

2. Publish configurations and run migrations

---

## API ENDPOINTS (Base URL: /api)

---

### AUTHENTICATION (public routes):

**POST /auth/register:**
- Register new student (username, email, password, full_name, phone, date_of_birth, gender)
- Handle profile_picture upload with Spatie Media Library
- Create user and student records
- Log activity: "Student registered"
- Return token

**POST /auth/login:**
- Login student (email, password)
- Verify user_type is student
- Log activity: "Student logged in"
- Return token

**POST /auth/forgot-password:**
- Send password reset email
- Log activity: "Password reset requested"

**POST /auth/reset-password:**
- Reset password with token
- Log activity: "Password reset"

### AUTHENTICATION (protected routes - require Sanctum token):

**GET /auth/profile:**
- Get authenticated student profile with student relationship
- Include image_url from Spatie Media Library

**PUT /auth/profile:**
- Update profile (full_name, phone, date_of_birth, gender, address)
- Handle profile_picture upload/update with Spatie Media Library
- Log activity: "Profile updated"

**PUT /auth/change-password:**
- Change password (current_password, new_password, new_password_confirmation)
- Log activity: "Password changed"

**POST /auth/logout:**
- Delete current access token
- Log activity: "Student logged out"

---

### DATA ENDPOINTS (public - for dropdowns/filters):

**GET /data:**
- Get all dropdown options in one request
- Returns: categories, courses, userStatuses, studentStatuses, lessonTypes, coursePricingTypes

**GET /data/categories:**
- Categories for dropdown
- Returns: [{value: id, label: category_name}]

**GET /data/courses:**
- Courses for dropdown
- Optional: ?category_id=1 to filter by category
- Returns: [{value: id, label: course_name, category_id}]

**GET /data/categories/{category}/courses:**
- Courses by specific category

**GET /data/user-statuses:**
- User status options (active, inactive, suspended)

**GET /data/student-statuses:**
- Student status options (active, inactive, graduated, suspended)

**GET /data/lesson-types:**
- Lesson type options (video, text, quiz)

**GET /data/course-pricing-types:**
- Pricing type options (free, paid)

**GET /data/course-levels:**
- Level options (beginner, intermediate, advanced)

**GET /data/course-filters:**
- All course filter options combined
- Returns: {categories, levels, pricing_types}

**GET /data/video-config:**
- Video upload configuration (max size, allowed types)

---

### CATEGORIES (protected):

**GET /categories:**
- List all active categories with course count
- Use Spatie QueryBuilder (allowedFilters: category_name, is_active)
- Use Spatie QueryBuilder (allowedSorts: category_name, created_at)

**GET /categories/{id}:**
- Get category details with course count
- Include image_url

**GET /categories/{id}/courses:**
- Get published courses by category
- Use Spatie QueryBuilder (allowedFilters: level, pricing_type, price_range, is_featured)
- Use Spatie QueryBuilder (allowedSorts: course_name, price, created_at)
- Include image_url from Spatie Media Library
- Paginated (20 per page)

---

### COURSES (protected):

**GET /courses:**
- List published courses
- Use Spatie QueryBuilder with filters:
  - `filter[category_id]` - exact match
  - `filter[level]` - exact match (beginner, intermediate, advanced)
  - `filter[is_featured]` - exact match (1 or 0)
  - `filter[pricing_type]` - exact match (free, paid)
  - `filter[price_range]` - custom filter (format: min,max e.g., "50,100")
  - `filter[search]` - partial match on course_name, course_code, description, instructor_name
- Use Spatie QueryBuilder (allowedSorts: course_name, price, created_at)
- Include: category, enrollment count, average rating, image_url
- Response fields: id, course_name, course_code, description, image_url, level, duration_hours, pricing_type, is_free, price, instructor_name, is_featured, status, category, enrollments_count, average_rating, created_at
- Pagination (20 per page)

**GET /courses/{id}:**
- Get course details with category, modules, lessons
- Include image_url from Spatie Media Library
- Check if current student is enrolled
- Include: pricing_type, is_free, price
- Include course reviews summary

**GET /courses/{id}/modules:**
- Get course modules with lessons (only if enrolled)
- Include lesson progress for current student
- Include lesson image_url, video_url from Spatie Media Library
- Use Spatie QueryBuilder for sorting

**GET /courses/{id}/reviews:**
- Get course reviews with student info
- Use Spatie QueryBuilder (allowedFilters: rating)
- Use Spatie QueryBuilder (allowedSorts: created_at, rating)
- Paginated (20)

**POST /courses/{id}/reviews:**
- Add course review (rating, review_text, would_recommend)
- Verify enrollment
- Log activity: "Course reviewed"

---

### COURSE REVIEWS (protected):

**GET /reviews:**
- List user's own reviews
- Include course info

**GET /reviews/{id}:**
- Get review detail

**GET /reviews/courses/{course}:**
- Get reviews by specific course

**POST /reviews/courses/{course}:**
- Create review for course
- Body: {rating, review_text, would_recommend}
- Verify enrollment
- Log activity: "Course reviewed"

**PUT /reviews/{id}:**
- Update own review
- Log activity: "Review updated"

**DELETE /reviews/{id}:**
- Delete own review
- Log activity: "Review deleted"

---

### ENROLLMENTS (protected):

**GET /enrollments:**
- Get current student's enrolled courses
- Use Spatie QueryBuilder (allowedFilters: status, payment_status)
- Use Spatie QueryBuilder (allowedSorts: enrollment_date, progress_percentage)
- Include course with image_url, pricing_type, is_free
- Paginated (20)

**POST /enrollments:**
- Enroll in a course (course_id)
- Verify course is published, check enrollment limit, check if already enrolled
- Log activity: "Enrolled in course"

**GET /enrollments/{id}:**
- Get enrollment details with course, modules, lessons, progress
- Include media URLs for course and lessons (image_url, video_url)

**DELETE /enrollments/{id}:**
- Drop/cancel enrollment (set status to 'dropped')
- Log activity: "Enrollment dropped"

---

### LESSONS (protected):

**GET /lessons/{id}:**
- Get lesson details (content, quiz)
- Include image_url, video_url, video_duration from Spatie Media Library
- Verify student is enrolled in course

**POST /lessons/{id}/progress:**
- Update lesson progress (status, progress_percentage, time_spent_minutes, video_last_position, scroll_position)
- Update enrollment progress automatically
- Log activity: "Lesson progress updated"

**GET /lessons/{id}/progress:**
- Get lesson progress for current student

---

### LESSON PROGRESS (protected):

**GET /progress:**
- List all lesson progress for current student

**GET /progress/completed:**
- List completed lessons

**GET /progress/courses/{courseId}:**
- Get progress for specific course

**GET /progress/lessons/{lesson}:**
- Get specific lesson progress

**PUT /progress/lessons/{lesson}:**
- Update lesson progress
- Body: {status, progress_percentage, completed_at}

---

### VIDEOS (protected):

**GET /videos/config:**
- Get video upload configuration
- Returns: max_size, allowed_types, storage_path

**GET /videos/list:**
- List uploaded videos

**POST /videos/upload:**
- Upload video file (multipart/form-data)
- Body: video file

**POST /videos/metadata:**
- Get video metadata
- Body: {path: "videos/example.mp4"}

**DELETE /videos/delete:**
- Delete video file
- Body: {path: "videos/example.mp4"}

**POST /videos/lessons/{lesson}/upload:**
- Upload video for specific lesson
- Body: video file (multipart/form-data)

**DELETE /videos/lessons/{lesson}:**
- Delete video from lesson

**GET /videos/lessons/{lesson}/stream:**
- Stream lesson video

---

### QUIZZES (protected):

**GET /quizzes/{id}:**
- Get quiz details with questions and options
- Include question image URLs from Spatie Media Library
- Verify enrollment
- Check if max attempts reached

**POST /quizzes/{id}/attempts:**
- Start quiz attempt
- Create attempt record
- Return questions with image URLs
- Log activity: "Quiz attempt started"

**PUT /quizzes/attempts/{id}:**
- Submit quiz answers (array of {question_id, selected_option_id, answer_text})
- Calculate score, mark as passed/failed
- Log activity: "Quiz submitted"
- Return results with correct answers (if show_correct_answers is true)

**GET /quizzes/attempts/{id}:**
- Get quiz attempt results with answers and correct options

**GET /quizzes/{id}/attempts:**
- Get student's quiz attempts history for a quiz
- Use Spatie QueryBuilder (allowedSorts: started_at, score_percentage)

---

### PAYMENTS (protected):

**GET /payments:**
- Get student's payment history
- Use Spatie QueryBuilder (allowedFilters: status, payment_method)
- Use Spatie QueryBuilder (allowedSorts: payment_date, amount)
- Include course info with image_url
- Paginated (20)

**POST /payments:**
- Process payment for course (course_id, payment_method, amount)
- Verify amount matches course price
- Create payment record
- Update enrollment payment_status
- Log activity: "Payment processed"

**GET /payments/{id}:**
- Get payment details with course info

---

### CERTIFICATES (protected):

**GET /certificates:**
- Get student's certificates
- Include certificate PDF URL from Spatie Media Library
- Include course info with image_url

**GET /certificates/{id}:**
- Get certificate details
- Include PDF URL

**GET /certificates/{id}/download:**
- Download certificate PDF from Spatie Media Library
- Stream file response

### CERTIFICATE VERIFICATION (public):

**GET /certificates/verify/{code}:**
- Verify certificate by code
- Return student name, course name, issue date
- Include certificate PDF URL

---

### NOTIFICATIONS (protected):

**GET /notifications:**
- Get student notifications
- Use Spatie QueryBuilder (allowedFilters: type, is_read)
- Use Spatie QueryBuilder (allowedSorts: created_at)
- Paginated (20)

**PUT /notifications/{id}/read:**
- Mark notification as read

**PUT /notifications/read-all:**
- Mark all notifications as read

**DELETE /notifications/{id}:**
- Delete notification

---

### DASHBOARD (protected):

**GET /dashboard/stats:**
- Get student statistics (enrolled_courses, completed_courses, in_progress_courses, certificates, total_learning_time_minutes)

**GET /dashboard/recent-activity:**
- Get recent lesson progress (last 10)
- Include lesson, course info with image_urls
- Use Spatie QueryBuilder for sorting

**GET /dashboard/activity-log:**
- Get student's activity log (last 50 activities)
- Use Spatie Activity Log
- Use Spatie QueryBuilder (allowedFilters: description, created_at)

**GET /dashboard/continue-learning:**
- Get courses to continue learning
- Include last accessed lesson, progress

---

## RESPONSE FORMAT:

### Success Response:
```json
{
  "success": true,
  "message": "Optional message",
  "data": { ... }
}
```

### Error Response:
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

### Paginated Response:
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5
  }
}
```

---

## COURSE RESPONSE FIELDS:

```json
{
  "id": 1,
  "course_name": "Complete Laravel Course",
  "course_code": "WEB-LAR-001",
  "description": "...",
  "image_url": "/storage/courses/image.jpg",
  "level": "beginner",
  "duration_hours": 40,
  "pricing_type": "paid",
  "is_free": false,
  "price": "99.99",
  "instructor_name": "John Smith",
  "enrollment_limit": 100,
  "is_featured": true,
  "status": "published",
  "category": {
    "id": 1,
    "category_name": "Web Development",
    "description": "...",
    "image_url": "/storage/categories/image.jpg"
  },
  "enrollments_count": 150,
  "reviews_count": 45,
  "average_rating": 4.5,
  "is_enrolled": true,
  "enrollment": {
    "id": 1,
    "status": "active",
    "progress_percentage": 65,
    "enrollment_date": "2026-01-15",
    "last_accessed": "2026-02-04"
  },
  "created_at": "2026-01-01T00:00:00Z"
}
```

---

## ENROLLMENT RESPONSE FIELDS:

```json
{
  "id": 1,
  "enrollment_date": "2026-01-15",
  "completion_date": null,
  "progress_percentage": 65,
  "status": "active",
  "payment_status": "paid",
  "certificate_issued": false,
  "last_accessed": "2026-02-04",
  "course": {
    "id": 1,
    "course_name": "Complete Laravel Course",
    "course_code": "WEB-LAR-001",
    "description": "...",
    "image_url": "/storage/courses/image.jpg",
    "level": "beginner",
    "duration_hours": 40,
    "pricing_type": "paid",
    "is_free": false,
    "price": "99.99",
    "instructor_name": "John Smith",
    "category": { ... },
    "modules": [
      {
        "id": 1,
        "module_title": "Getting Started",
        "module_order": 1,
        "description": "...",
        "lessons": [
          {
            "id": 1,
            "lesson_title": "Introduction",
            "lesson_type": "video",
            "lesson_order": 1,
            "description": "...",
            "duration_minutes": 15,
            "video_duration": "00:15:30",
            "is_mandatory": true,
            "image_url": "/storage/lessons/image.jpg",
            "video_url": "/storage/lessons/video.mp4",
            "progress": {
              "status": "completed",
              "progress_percentage": 100,
              "completed_at": "2026-01-20"
            }
          }
        ]
      }
    ]
  }
}
```

---

## FILTER EXAMPLES:

### Course Filters:
```
GET /courses?filter[category_id]=1
GET /courses?filter[level]=beginner
GET /courses?filter[pricing_type]=free
GET /courses?filter[pricing_type]=paid
GET /courses?filter[price_range]=50,100
GET /courses?filter[price_range]=100,
GET /courses?filter[price_range]=,50
GET /courses?filter[is_featured]=1
GET /courses?filter[search]=laravel
```

### Combined Filters:
```
GET /courses?filter[category_id]=1&filter[level]=beginner&filter[pricing_type]=free
GET /courses?filter[price_range]=50,100&filter[is_featured]=1&sort=-created_at
GET /courses?filter[search]=laravel&filter[level]=intermediate&per_page=10
```

### Sorting:
```
GET /courses?sort=price           # Price ascending
GET /courses?sort=-price          # Price descending
GET /courses?sort=course_name     # Name A-Z
GET /courses?sort=-created_at     # Newest first
```

---

## MODELS (with Spatie traits):

**User model:**
- Use HasMediaTrait (for profile_picture)
- Use LogsActivity trait
- Media collections: 'profile_picture' (max 2MB, convert to 500x500)
- Activity log all changes

**Student model:**
- Use LogsActivity trait
- Activity log for status changes

**Course model:**
- Use HasMediaTrait (for thumbnail)
- Use LogsActivity trait
- Media collections: 'thumbnail' (convert to 800x600, 400x300, 200x150)
- Activity log for view, enroll actions

**Lesson model:**
- Use HasMediaTrait (for video, documents, thumbnail)
- Use LogsActivity trait
- Media collections: 'video' (max 500MB), 'documents', 'thumbnail' (convert to 400x300)
- Activity log for view, complete actions

**QuizQuestion model:**
- Use HasMediaTrait (for question_image)
- Media collections: 'question_image' (max 1MB, convert to 800x600)

**Quiz model:**
- Use LogsActivity trait
- Activity log for attempts

**Enrollment model:**
- Use LogsActivity trait
- Activity log for status changes

**Certificate model:**
- Use HasMediaTrait (for certificate PDF)
- Use LogsActivity trait
- Media collections: 'certificate' (PDF)
- Activity log for issue, download

**Payment model:**
- Use LogsActivity trait
- Activity log for all payment actions

**CourseReview model:**
- Use LogsActivity trait
- Activity log for create, update, delete

---

## API CONTROLLERS (app/Http/Controllers/Api/):

**AuthController:**
- register (with media upload)
- login (log activity)
- profile (include media URLs)
- updateProfile (handle media upload)
- changePassword (log activity)
- logout (log activity)

**DataController:**
- index (all dropdown options)
- categories, courses, coursesByCategory
- userStatuses, studentStatuses
- lessonTypes, coursePricingTypes, courseLevels
- courseFilters (combined)

**CategoryController:**
- index (use QueryBuilder)
- show
- courses (use QueryBuilder with pagination)

**CourseController:**
- index (use QueryBuilder with filters including price_range, pricing_type)
- show (include media URLs, check enrollment, include pricing_type, is_free)
- modules (verify enrollment, include media URLs)
- reviews (use QueryBuilder)
- storeReview (log activity)

**CourseReviewController:**
- index, show, byCourse
- store, update, destroy (log activity)

**EnrollmentController:**
- index (use QueryBuilder, include media URLs, pricing_type, is_free)
- store (log activity, verify limits)
- show (include all media URLs)
- destroy (log activity)

**LessonController:**
- show (include all media URLs, verify enrollment)
- updateProgress (log activity, update enrollment progress)
- getProgress

**LessonProgressController:**
- index, completed, byCourse
- show, update

**VideoController:**
- config, list
- upload, metadata, destroy
- uploadForLesson, destroyFromLesson, stream

**QuizController:**
- show (include question image URLs, verify enrollment)
- startAttempt (log activity)
- submitAttempt (calculate score, log activity)
- getAttempt (include image URLs)
- getAttempts (use QueryBuilder)

**PaymentController:**
- index (use QueryBuilder, include media URLs)
- store (log activity, verify amount)
- show (include media URLs)

**CertificateController:**
- index (include PDF URLs, media URLs)
- show (include PDF URL)
- download (stream PDF from media library)
- verify (public, include PDF URL)

**NotificationController:**
- index (use QueryBuilder)
- markAsRead
- markAllAsRead
- destroy

**DashboardController:**
- stats
- recentActivity (include media URLs)
- activityLog (use Spatie Activity Log with QueryBuilder)
- continueLearning

---

## CUSTOM FILTERS:

**PriceRangeFilter (App\Filters\PriceRangeFilter):**
- Filter courses by price range
- Format: "min,max" (e.g., "50,100")
- Supports: min only ("100,"), max only (",50"), or both ("50,100")

**UniversalSearchFilter (App\Filters\UniversalSearchFilter):**
- Search across multiple fields
- Supports relationship fields with dot notation

---

## MEDIA HANDLING:
- All file uploads use Spatie Media Library
- Profile pictures: convert to 500x500, 200x200
- Course thumbnails: convert to 800x600, 400x300, 200x150 (responsive images)
- Lesson thumbnails: convert to 400x300, 200x150
- Lesson videos: store original, max 500MB
- Question images: convert to 800x600, 400x300
- Certificate PDFs: store original
- All images: optimize for web
- Return URLs with conversions: getUrl('thumbnail-name')
- Use image_url field name consistently across all responses

---

## ACTIVITY LOGGING:
- Log all student actions: login, logout, register, enroll, complete lesson, take quiz, make payment, etc.
- Use Spatie Activity Log with custom properties
- Store student_id, course_id, lesson_id in properties
- Example: activity()->log('Enrolled in course')->properties(['course_id' => $courseId, 'student_id' => $studentId])

---

## QUERY BUILDER USAGE:
- Use allowedFilters() for all filterable endpoints
- Use allowedSorts() for all sortable endpoints
- Use allowedIncludes() for relationship loading
- Example: QueryBuilder::for(Course::class)->allowedFilters(['level', 'category_id', 'pricing_type', AllowedFilter::custom('price_range', new PriceRangeFilter())])->allowedSorts('price', 'created_at')->get()

---

## SECURITY:
- Rate limiting: 60 requests per minute for authenticated users
- Input sanitization
- Only show published courses
- Students can only access their own data
- Verify enrollment before accessing course content
- Validate file uploads (mime types, sizes)
- Use Sanctum abilities for token scoping

---

## VALIDATION:
- Validate all inputs
- File upload validation: mime types, max sizes
- Use Form Request classes for complex validation

---

## ERROR HANDLING:
- Try-catch blocks for all operations
- Proper HTTP status codes
- Return media-specific errors (upload failed, file not found, etc.)

---

## FEATURES:
- Student self-registration with profile picture
- Advanced filtering and sorting with Spatie Query Builder
- Price range filtering for courses
- Free/Paid course filtering
- File uploads and management with Spatie Media Library
- Automatic image optimization and responsive images
- Video streaming
- Activity tracking with Spatie Activity Log
- Certificate PDF generation and download
- Course browsing with thumbnails
- Lesson video playback with progress tracking
- Quiz with images
- Payment processing
- Notifications

Use Spatie packages for all file handling, query building, and activity logging.
Follow Laravel API best practices.
Use proper RESTful conventions.
Include pagination for all list endpoints.
Always include media URLs in API responses using image_url field name.
