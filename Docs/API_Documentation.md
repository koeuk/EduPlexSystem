# EduPlex LMS Mobile API Documentation

## Overview

REST API for the EduPlex Learning Management System mobile application.

- **Base URL**: `/api`
- **Authentication**: Laravel Sanctum (Bearer Token)
- **Content-Type**: `application/json`

---

## Authentication

All protected endpoints require the `Authorization` header:

```
Authorization: Bearer {token}
```

---

## Response Format

### Success Response

```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error message",
    "errors": ["Detailed error 1", "Detailed error 2"]
}
```

### Paginated Response

```json
{
    "success": true,
    "data": [ ... ],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 100,
        "total_pages": 5
    },
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    }
}
```

---

## Endpoints

### Authentication (Public)

#### Register Student

```
POST /api/auth/register
```

**Request Body (multipart/form-data):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| username | string | Yes | Unique username |
| email | string | Yes | Valid email address |
| password | string | Yes | Min 8 characters |
| password_confirmation | string | Yes | Must match password |
| full_name | string | Yes | Student's full name |
| phone | string | No | Phone number |
| date_of_birth | date | No | Format: YYYY-MM-DD |
| gender | string | No | male, female, other |
| profile_picture | file | No | Image (jpeg, png, webp), max 2MB |

**Response (201):**

```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "user": {
            "id": 1,
            "username": "johndoe",
            "email": "john@example.com",
            "full_name": "John Doe",
            "profile_picture": "https://..."
        },
        "student": {
            "id": 1,
            "student_id_number": "STU-2026-000001"
        },
        "token": "1|abc123..."
    }
}
```

---

#### Login

```
POST /api/auth/login
```

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "username": "johndoe",
            "email": "john@example.com",
            "full_name": "John Doe",
            "profile_picture": "https://..."
        },
        "student": {
            "id": 1,
            "student_id_number": "STU-2026-000001",
            "student_status": "active"
        },
        "token": "2|xyz789..."
    }
}
```

---

### Authentication (Protected)

#### Get Profile

```
GET /api/auth/profile
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "username": "johndoe",
        "email": "john@example.com",
        "full_name": "John Doe",
        "phone": "+1234567890",
        "date_of_birth": "1995-05-15",
        "gender": "male",
        "address": "123 Main St",
        "profile_picture": "https://...",
        "student": {
            "id": 1,
            "student_id_number": "STU-2026-000001",
            "enrollment_date": "2026-01-15",
            "student_status": "active"
        },
        "created_at": "2026-01-15T10:00:00Z"
    }
}
```

---

#### Update Profile

```
PUT /api/auth/profile
```

**Request Body (multipart/form-data):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| full_name | string | No | Updated name |
| phone | string | No | Phone number |
| date_of_birth | date | No | Format: YYYY-MM-DD |
| gender | string | No | male, female, other |
| address | string | No | Max 500 characters |
| profile_picture | file | No | Image (jpeg, png, webp), max 2MB |

---

#### Change Password

```
PUT /api/auth/change-password
```

**Request Body:**

```json
{
    "current_password": "oldpassword",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

---

#### Logout

```
POST /api/auth/logout
```

**Response (200):**

```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### Categories

#### List Categories

```
GET /api/categories
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| filter[category_name] | string | Filter by name (partial match) |
| filter[is_active] | boolean | Filter by active status |
| sort | string | Sort by: category_name, created_at |

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "category_name": "Programming",
            "description": "Learn programming languages",
            "icon": "code",
            "courses_count": 15
        }
    ]
}
```

---

#### Get Category

```
GET /api/categories/{id}
```

---

#### Get Category Courses

```
GET /api/categories/{id}/courses
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| filter[level] | string | beginner, intermediate, advanced |
| filter[is_featured] | boolean | Filter featured courses |
| sort | string | Sort by: course_name, price, created_at |
| per_page | integer | Items per page (default: 20) |

---

### Courses

#### List Courses

```
GET /api/courses
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| filter[category_id] | integer | Filter by category |
| filter[level] | string | beginner, intermediate, advanced |
| filter[is_featured] | boolean | Filter featured courses |
| filter[search] | string | Search in name, code, description |
| sort | string | Sort by: course_name, price, created_at |
| per_page | integer | Items per page (default: 20) |

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "course_name": "Introduction to Python",
            "course_code": "PY101",
            "description": "Learn Python basics",
            "level": "beginner",
            "duration_hours": 20,
            "price": "49.99",
            "instructor_name": "Jane Smith",
            "is_featured": true,
            "thumbnail": "https://...",
            "category": {
                "id": 1,
                "category_name": "Programming"
            },
            "enrollments_count": 150,
            "average_rating": 4.5
        }
    ],
    "pagination": { ... }
}
```

---

#### Get Course Details

```
GET /api/courses/{id}
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "course_name": "Introduction to Python",
        "course_code": "PY101",
        "description": "Learn Python basics",
        "level": "beginner",
        "duration_hours": 20,
        "price": "49.99",
        "instructor_name": "Jane Smith",
        "enrollment_limit": 100,
        "is_featured": true,
        "thumbnail": "https://...",
        "category": { ... },
        "modules": [
            {
                "id": 1,
                "module_title": "Getting Started",
                "module_order": 1,
                "description": "Introduction to Python",
                "lessons_count": 5
            }
        ],
        "total_lessons": 25,
        "enrollments_count": 150,
        "reviews_count": 45,
        "average_rating": 4.5,
        "is_enrolled": false,
        "enrollment": null
    }
}
```

---

#### Get Course Modules (Enrolled Only)

```
GET /api/courses/{id}/modules
```

Returns modules with lessons and progress for enrolled students.

---

#### Get Course Reviews

```
GET /api/courses/{id}/reviews
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| filter[rating] | integer | Filter by rating (1-5) |
| sort | string | Sort by: created_at, rating |
| per_page | integer | Items per page (default: 20) |

---

#### Add Course Review

```
POST /api/courses/{id}/reviews
```

**Request Body:**

```json
{
    "rating": 5,
    "review_text": "Excellent course!",
    "would_recommend": true
}
```

---

#### Update Review

```
PUT /api/courses/reviews/{id}
```

---

#### Delete Review

```
DELETE /api/courses/reviews/{id}
```

---

### Enrollments

#### List My Enrollments

```
GET /api/enrollments
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| filter[status] | string | active, completed, dropped, expired |
| filter[payment_status] | string | pending, paid, failed, refunded |
| sort | string | Sort by: enrollment_date, progress_percentage |
| per_page | integer | Items per page (default: 20) |

---

#### Enroll in Course

```
POST /api/enrollments
```

**Request Body:**

```json
{
    "course_id": 1
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "Successfully enrolled in course",
    "data": {
        "id": 1,
        "enrollment_date": "2026-02-03T10:00:00Z",
        "status": "active",
        "payment_status": "pending",
        "course": {
            "id": 1,
            "course_name": "Introduction to Python",
            "price": "49.99"
        }
    }
}
```

---

#### Get Enrollment Details

```
GET /api/enrollments/{id}
```

---

#### Drop Enrollment

```
DELETE /api/enrollments/{id}
```

---

### Lessons

#### Get Lesson

```
GET /api/lessons/{id}
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "lesson_title": "Variables and Data Types",
        "lesson_type": "video",
        "lesson_order": 1,
        "description": "Learn about variables",
        "content": "<p>HTML content...</p>",
        "video_url": "https://...",
        "video_duration": 900,
        "thumbnail": "https://...",
        "documents": [
            {
                "id": 1,
                "name": "Cheat Sheet",
                "file_name": "cheatsheet.pdf",
                "mime_type": "application/pdf",
                "size": 102400,
                "url": "https://..."
            }
        ],
        "duration_minutes": 15,
        "is_mandatory": true,
        "module": {
            "id": 1,
            "module_title": "Getting Started"
        },
        "quiz": {
            "id": 1,
            "quiz_title": "Variables Quiz",
            "time_limit_minutes": 10,
            "passing_score": 70,
            "total_questions": 10
        },
        "progress": {
            "id": 1,
            "status": "in_progress",
            "progress_percentage": 50,
            "time_spent_minutes": 8,
            "video_last_position": 450,
            "completed_at": null
        }
    }
}
```

---

#### Update Lesson Progress

```
POST /api/lessons/{id}/progress
```

**Request Body:**

```json
{
    "status": "in_progress",
    "progress_percentage": 75,
    "time_spent_minutes": 12,
    "video_last_position": 675,
    "scroll_position": 500
}
```

---

#### Get Lesson Progress

```
GET /api/lessons/{id}/progress
```

---

### Quizzes

#### Get Quiz

```
GET /api/quizzes/{id}
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "quiz_title": "Python Basics Quiz",
        "instructions": "Answer all questions",
        "time_limit_minutes": 30,
        "passing_score": 70,
        "max_attempts": 3,
        "show_correct_answers": true,
        "total_points": 100,
        "total_questions": 10,
        "attempts_used": 1,
        "can_attempt": true,
        "questions": [
            {
                "id": 1,
                "question_text": "What is Python?",
                "question_type": "multiple_choice",
                "points": 10,
                "image_url": null,
                "options": [
                    {
                        "id": 1,
                        "option_text": "A programming language"
                    },
                    {
                        "id": 2,
                        "option_text": "A snake"
                    }
                ]
            }
        ]
    }
}
```

---

#### Start Quiz Attempt

```
POST /api/quizzes/{id}/attempts
```

**Response (201):**

```json
{
    "success": true,
    "message": "Quiz attempt started",
    "data": {
        "attempt_id": 1,
        "attempt_number": 1,
        "started_at": "2026-02-03T10:00:00Z",
        "time_limit_minutes": 30,
        "questions": [ ... ]
    }
}
```

---

#### Submit Quiz Attempt

```
PUT /api/quizzes/attempts/{id}
```

**Request Body:**

```json
{
    "answers": [
        {
            "question_id": 1,
            "selected_option_id": 1,
            "answer_text": null
        },
        {
            "question_id": 2,
            "selected_option_id": null,
            "answer_text": "Python is a programming language"
        }
    ]
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Congratulations! You passed the quiz.",
    "data": {
        "attempt_id": 1,
        "score_percentage": 85,
        "total_points": 85,
        "max_points": 100,
        "passed": true,
        "passing_score": 70,
        "time_taken_minutes": 15,
        "answers": [
            {
                "question_id": 1,
                "question_text": "What is Python?",
                "selected_option_id": 1,
                "correct_option_id": 1,
                "is_correct": true,
                "points_earned": 10,
                "explanation": "Python is a high-level programming language"
            }
        ]
    }
}
```

---

#### Get Quiz Attempt

```
GET /api/quizzes/attempts/{id}
```

---

#### Get Quiz Attempts History

```
GET /api/quizzes/{id}/attempts
```

---

### Payments

#### List My Payments

```
GET /api/payments
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| filter[status] | string | pending, completed, failed, refunded |
| filter[payment_method] | string | credit_card, debit_card, paypal, bank_transfer |
| sort | string | Sort by: payment_date, amount |
| per_page | integer | Items per page (default: 20) |

---

#### Process Payment

```
POST /api/payments
```

**Request Body:**

```json
{
    "course_id": 1,
    "payment_method": "credit_card",
    "amount": 49.99
}
```

---

#### Get Payment Details

```
GET /api/payments/{id}
```

---

### Certificates

#### List My Certificates

```
GET /api/certificates
```

---

#### Get Certificate

```
GET /api/certificates/{id}
```

---

#### Download Certificate

```
GET /api/certificates/{id}/download
```

Returns PDF file stream.

---

#### Verify Certificate (Public)

```
GET /api/certificates/verify/{code}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Certificate verified",
    "data": {
        "certificate_code": "CERT-2026-ABC123",
        "issue_date": "2026-01-30",
        "student_name": "John Doe",
        "course_name": "Introduction to Python",
        "course_code": "PY101",
        "instructor_name": "Jane Smith",
        "certificate_url": "https://..."
    }
}
```

---

### Notifications

#### List Notifications

```
GET /api/notifications
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| filter[type] | string | info, success, warning, error, enrollment, completion, reminder |
| filter[is_read] | boolean | Filter by read status |
| sort | string | Sort by: created_at |
| per_page | integer | Items per page (default: 20) |

---

#### Mark as Read

```
PUT /api/notifications/{id}/read
```

---

#### Mark All as Read

```
PUT /api/notifications/read-all
```

---

#### Delete Notification

```
DELETE /api/notifications/{id}
```

---

### Dashboard

#### Get Stats

```
GET /api/dashboard/stats
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "enrolled_courses": 5,
        "completed_courses": 2,
        "in_progress_courses": 3,
        "certificates": 2,
        "total_learning_time_minutes": 1500,
        "total_learning_time_hours": 25,
        "average_progress": 65.5
    }
}
```

---

#### Get Recent Activity

```
GET /api/dashboard/recent-activity
```

Returns last 10 lesson progress entries.

---

#### Get Activity Log

```
GET /api/dashboard/activity-log
```

Returns last 50 activities (login, enroll, complete lesson, etc.)

---

#### Continue Learning

```
GET /api/dashboard/continue-learning
```

Returns up to 5 in-progress courses with next lesson.

---

## Error Codes

| HTTP Code | Description |
|-----------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized (invalid/missing token) |
| 403 | Forbidden (no permission) |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## Rate Limiting

- **Authenticated users**: 60 requests per minute
- **Unauthenticated users**: 30 requests per minute

---

## File Upload Limits

| Media Type | Max Size | Formats |
|------------|----------|---------|
| Profile Picture | 2 MB | jpeg, png, webp |
| Course Thumbnail | 2 MB | jpeg, png, webp |
| Lesson Video | 500 MB | mp4, webm, ogg |
| Documents | 10 MB | pdf, docx |
| Question Image | 1 MB | jpeg, png, webp, gif |

---

## Query Builder

### Filtering

```
GET /api/courses?filter[level]=beginner&filter[is_featured]=true
```

### Sorting

```
GET /api/courses?sort=price           # Ascending
GET /api/courses?sort=-price          # Descending
GET /api/courses?sort=course_name,-created_at  # Multiple
```

### Pagination

```
GET /api/courses?per_page=10&page=2
```

---

## Media URLs

All media URLs are served via Spatie Media Library with automatic conversions:

- **Profile Pictures**: Original, 500x500, 200x200
- **Course Thumbnails**: Original, 800x600, 400x300, 200x150
- **Lesson Thumbnails**: Original, 400x300, 200x150
- **Question Images**: Original, 800x600, 400x300

---

## Activity Logging

The API automatically logs the following activities:

- Student registration
- Login/Logout
- Profile updates
- Course enrollment
- Lesson progress
- Quiz attempts
- Payments
- Certificate downloads
- Review actions

---

## SDK Examples

### cURL

```bash
# Login
curl -X POST https://api.eduplex.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Get courses
curl -X GET https://api.eduplex.com/api/courses \
  -H "Authorization: Bearer {token}"
```

### JavaScript (Fetch)

```javascript
// Login
const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        email: 'john@example.com',
        password: 'password123'
    })
});
const data = await response.json();
const token = data.data.token;

// Get courses
const courses = await fetch('/api/courses', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});
```

### Dart (Flutter)

```dart
// Login
final response = await http.post(
    Uri.parse('$baseUrl/api/auth/login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
        'email': 'john@example.com',
        'password': 'password123'
    }),
);
final data = jsonDecode(response.body);
final token = data['data']['token'];

// Get courses
final courses = await http.get(
    Uri.parse('$baseUrl/api/courses'),
    headers: {'Authorization': 'Bearer $token'},
);
```
