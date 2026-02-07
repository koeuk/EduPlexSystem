# EduPlex Mobile API Reference

API documentation for mobile app developers.

---

## Base URL

```
https://your-domain.com/api
```

---

## Authentication

### Token-Based Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {your_token}
```

### Get Token

Obtain a token by logging in or registering.

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Optional message",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Error message"]
  }
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
  }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## Endpoints

---

## 1. Authentication

### Register

```
POST /auth/register
```

**Body (multipart/form-data):**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| username | string | Yes | Unique username |
| email | string | Yes | Valid email |
| password | string | Yes | Min 8 characters |
| password_confirmation | string | Yes | Must match password |
| full_name | string | Yes | Full name |
| phone | string | No | Phone number |
| date_of_birth | date | No | Format: YYYY-MM-DD |
| gender | string | No | male, female, other |
| profile_picture | file | No | JPEG, PNG, WebP (max 2MB) |

**Response:**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "username": "john_doe",
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

### Login

```
POST /auth/login
```

**Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "username": "john_doe",
      "email": "john@example.com",
      "full_name": "John Doe",
      "phone": "1234567890",
      "image_url": "https://...",
      "user_type": "student",
      "status": "active"
    },
    "student": {
      "id": 1,
      "student_id_number": "STU-2026-000001",
      "student_status": "active",
      "enrollment_date": "2026-01-15"
    },
    "token": "1|abc123..."
  }
}
```

---

### Forgot Password

```
POST /auth/forgot-password
```

**Body:**
```json
{
  "email": "john@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset link sent to your email"
}
```

---

### Reset Password

```
POST /auth/reset-password
```

**Body:**
```json
{
  "token": "reset_token_from_email",
  "email": "john@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password has been reset successfully"
}
```

---

### Get Profile

```
GET /auth/profile
```
*Requires authentication*

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "full_name": "John Doe",
    "phone": "1234567890",
    "date_of_birth": "1995-05-15",
    "gender": "male",
    "address": "123 Main St",
    "image_url": "https://...",
    "user_type": "student",
    "status": "active",
    "student": {
      "id": 1,
      "student_id_number": "STU-2026-000001",
      "enrollment_date": "2026-01-15",
      "student_status": "active"
    }
  }
}
```

---

### Update Profile

```
PUT /auth/profile
```
*Requires authentication*

**Body (multipart/form-data):**
| Field | Type | Required |
|-------|------|----------|
| full_name | string | No |
| phone | string | No |
| date_of_birth | date | No |
| gender | string | No |
| address | string | No |
| profile_picture | file | No |

---

### Change Password

```
PUT /auth/change-password
```
*Requires authentication*

**Body:**
```json
{
  "current_password": "oldpassword",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

---

### Logout

```
POST /auth/logout
```
*Requires authentication*

---

## 2. Data (Public - For Dropdowns)

### Get All Filter Options

```
GET /data
```

**Response:**
```json
{
  "success": true,
  "data": {
    "categories": [...],
    "courses": [...],
    "userStatuses": [...],
    "studentStatuses": [...],
    "lessonTypes": [...],
    "coursePricingTypes": [...]
  }
}
```

### Other Data Endpoints

| Endpoint | Description |
|----------|-------------|
| `GET /data/categories` | Category list for dropdown |
| `GET /data/courses` | Course list (optional: `?category_id=1`) |
| `GET /data/categories/{id}/courses` | Courses by category |
| `GET /data/user-statuses` | User status options |
| `GET /data/student-statuses` | Student status options |
| `GET /data/lesson-types` | Lesson types (video, text, quiz) |
| `GET /data/course-pricing-types` | Pricing types (free, paid) |
| `GET /data/course-levels` | Levels (beginner, intermediate, advanced) |
| `GET /data/course-filters` | All filter options combined |
| `GET /data/video-config` | Video upload config |

---

## 3. Categories

### List Categories

```
GET /categories
```
*Requires authentication*

**Query Parameters:**
| Param | Description |
|-------|-------------|
| `filter[category_name]` | Search by name |
| `sort` | category_name, created_at |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category_name": "Web Development",
      "description": "...",
      "icon": "code",
      "image_url": "https://...",
      "is_active": true,
      "courses_count": 25
    }
  ]
}
```

### Get Category

```
GET /categories/{id}
```

### Get Courses by Category

```
GET /categories/{id}/courses
```

**Query Parameters:**
| Param | Description |
|-------|-------------|
| `filter[level]` | beginner, intermediate, advanced |
| `filter[pricing_type]` | free, paid |
| `filter[price_range]` | min,max (e.g., "50,100") |
| `filter[is_featured]` | 1 or 0 |
| `sort` | course_name, price, created_at |
| `per_page` | Items per page (default: 20) |

---

## 4. Courses

### List Courses

```
GET /courses
```
*Requires authentication*

**Query Parameters:**
| Param | Description |
|-------|-------------|
| `filter[category_id]` | Filter by category |
| `filter[level]` | beginner, intermediate, advanced |
| `filter[pricing_type]` | free, paid |
| `filter[price_range]` | min,max (e.g., "50,100", "100,", ",50") |
| `filter[is_featured]` | 1 or 0 |
| `filter[search]` | Search in name, code, description |
| `sort` | course_name, price, created_at, -created_at |
| `per_page` | Items per page (default: 20) |

**Example:**
```
GET /courses?filter[level]=beginner&filter[pricing_type]=free&sort=-created_at
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "course_name": "Complete Laravel Course",
      "course_code": "WEB-LAR-001",
      "description": "...",
      "image_url": "https://...",
      "level": "beginner",
      "duration_hours": 40,
      "pricing_type": "paid",
      "is_free": false,
      "price": "99.99",
      "instructor_name": "John Smith",
      "is_featured": true,
      "status": "published",
      "category": {
        "id": 1,
        "category_name": "Web Development",
        "image_url": "https://..."
      },
      "enrollments_count": 150,
      "average_rating": 4.5,
      "created_at": "2026-01-01T00:00:00Z"
    }
  ],
  "pagination": { ... }
}
```

### Get Course Details

```
GET /courses/{id}
```

**Response includes:**
- Course info with category
- Modules list (title, order, lessons count)
- Enrollment status (if enrolled)
- Reviews summary

### Get Course Modules (Enrolled Only)

```
GET /courses/{id}/modules
```

**Response:**
```json
{
  "success": true,
  "data": {
    "course": {
      "id": 1,
      "course_name": "Complete Laravel Course",
      "image_url": "https://..."
    },
    "enrollment": {
      "id": 1,
      "status": "active",
      "progress_percentage": 65
    },
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
            "duration_minutes": 15,
            "video_duration": "00:15:30",
            "is_mandatory": true,
            "image_url": "https://...",
            "video_url": "https://...",
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

### Get Course Reviews

```
GET /courses/{id}/reviews
```

**Query Parameters:**
| Param | Description |
|-------|-------------|
| `filter[rating]` | 1-5 |
| `sort` | created_at, rating |

### Submit Course Review

```
POST /courses/{id}/reviews
```

**Body:**
```json
{
  "rating": 5,
  "review_text": "Great course!",
  "would_recommend": true
}
```

---

## 5. Reviews

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/reviews` | GET | List your reviews |
| `/reviews/{id}` | GET | Get review detail |
| `/reviews/courses/{course}` | GET | Get course reviews |
| `/reviews/courses/{course}` | POST | Create review |
| `/reviews/{id}` | PUT | Update your review |
| `/reviews/{id}` | DELETE | Delete your review |

---

## 6. Enrollments

### List Enrollments

```
GET /enrollments
```

**Query Parameters:**
| Param | Description |
|-------|-------------|
| `filter[status]` | active, completed, dropped |
| `filter[payment_status]` | pending, paid |
| `sort` | enrollment_date, progress_percentage |

### Enroll in Course

```
POST /enrollments
```

**Body:**
```json
{
  "course_id": 1
}
```

### Get Enrollment Details

```
GET /enrollments/{id}
```

### Drop Enrollment

```
DELETE /enrollments/{id}
```

---

## 7. Lessons

### Get Lesson

```
GET /lessons/{id}
```
*Must be enrolled in course*

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "lesson_title": "Introduction",
    "lesson_type": "video",
    "description": "...",
    "content": "...",
    "duration_minutes": 15,
    "video_duration": "00:15:30",
    "image_url": "https://...",
    "video_url": "https://...",
    "is_mandatory": true,
    "quiz": null
  }
}
```

### Update Lesson Progress

```
POST /lessons/{id}/progress
```

**Body:**
```json
{
  "status": "in_progress",
  "progress_percentage": 50,
  "time_spent_minutes": 10,
  "video_last_position": 300,
  "scroll_position": 500
}
```

### Get Lesson Progress

```
GET /lessons/{id}/progress
```

---

## 8. Lesson Progress

| Endpoint | Description |
|----------|-------------|
| `GET /progress` | In-progress lessons |
| `GET /progress/completed` | Completed lessons |
| `GET /progress/courses/{courseId}` | Progress by course |
| `GET /progress/lessons/{id}` | Specific lesson progress |
| `PUT /progress/lessons/{id}` | Update progress |

---

## 9. Quizzes

### Get Quiz

```
GET /quizzes/{id}
```
*Must be enrolled*

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "quiz_title": "Module 1 Quiz",
    "instructions": "...",
    "time_limit_minutes": 30,
    "passing_score": 70,
    "max_attempts": 3,
    "questions": [
      {
        "id": 1,
        "question_text": "What is Laravel?",
        "question_type": "multiple_choice",
        "points": 1,
        "image_url": null,
        "options": [
          { "id": 1, "option_text": "A PHP framework", "option_order": 1 },
          { "id": 2, "option_text": "A database", "option_order": 2 }
        ]
      }
    ]
  }
}
```

### Start Quiz Attempt

```
POST /quizzes/{id}/attempts
```

### Submit Quiz

```
PUT /quizzes/attempts/{id}
```

**Body:**
```json
{
  "answers": [
    { "question_id": 1, "selected_option_id": 1 },
    { "question_id": 2, "selected_option_id": 5 }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "score_percentage": 80,
    "total_points": 8,
    "max_points": 10,
    "passed": true,
    "time_taken_minutes": 15,
    "answers": [...]
  }
}
```

### Get Quiz Attempt

```
GET /quizzes/attempts/{id}
```

### Get Attempts History

```
GET /quizzes/{id}/attempts
```

---

## 10. Payments

### List Payments

```
GET /payments
```

**Query Parameters:**
| Param | Description |
|-------|-------------|
| `filter[status]` | pending, completed, failed, refunded |
| `filter[payment_method]` | credit_card, debit_card, paypal, bank_transfer |
| `sort` | payment_date, amount |

### Create Payment

```
POST /payments
```

**Body:**
```json
{
  "course_id": 1,
  "payment_method": "credit_card",
  "amount": 99.99
}
```

### Get Payment

```
GET /payments/{id}
```

---

## 11. Certificates

### List Certificates

```
GET /certificates
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "certificate_code": "CERT-2026-ABC123",
      "issue_date": "2026-02-01",
      "course": {
        "id": 1,
        "course_name": "Complete Laravel Course",
        "image_url": "https://..."
      }
    }
  ]
}
```

### Get Certificate

```
GET /certificates/{id}
```

### Download Certificate (PDF)

```
GET /certificates/{id}/download
```

Returns PDF file stream.

### Verify Certificate (Public)

```
GET /certificates/verify/{code}
```

---

## 12. Notifications

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/notifications` | GET | List notifications |
| `/notifications/{id}/read` | PUT | Mark as read |
| `/notifications/read-all` | PUT | Mark all as read |
| `/notifications/{id}` | DELETE | Delete notification |

**Query Parameters for List:**
| Param | Description |
|-------|-------------|
| `filter[type]` | info, success, warning, error, enrollment, completion |
| `filter[is_read]` | 0 or 1 |
| `sort` | created_at |

---

## 13. Dashboard

### Get Stats

```
GET /dashboard/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "enrolled_courses": 5,
    "completed_courses": 2,
    "in_progress_courses": 3,
    "certificates": 2,
    "total_learning_time_minutes": 1250,
    "total_learning_time_hours": 20.8,
    "average_progress": 65.5
  }
}
```

### Get Recent Activity

```
GET /dashboard/recent-activity
```

Returns last 10 lesson activities.

### Get Activity Log

```
GET /dashboard/activity-log
```

Returns last 50 activities.

### Continue Learning

```
GET /dashboard/continue-learning
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "enrollment_id": 1,
      "progress_percentage": 65,
      "last_accessed": "2026-02-04",
      "course": {
        "id": 1,
        "course_name": "Complete Laravel Course",
        "image_url": "https://..."
      },
      "next_lesson": {
        "id": 5,
        "lesson_title": "Database Migrations",
        "lesson_type": "video"
      }
    }
  ]
}
```

---

## 14. Videos

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/videos/config` | GET | Upload configuration |
| `/videos/list` | GET | List uploaded videos |
| `/videos/upload` | POST | Upload video |
| `/videos/metadata` | POST | Get video metadata |
| `/videos/delete` | DELETE | Delete video |
| `/videos/lessons/{id}/upload` | POST | Upload for lesson |
| `/videos/lessons/{id}` | DELETE | Delete from lesson |
| `/videos/lessons/{id}/stream` | GET | Stream video |

---

## Filtering & Sorting

### Filter Syntax

```
GET /endpoint?filter[field]=value
```

### Examples

```
# Single filter
GET /courses?filter[level]=beginner

# Multiple filters
GET /courses?filter[level]=beginner&filter[pricing_type]=free

# Price range
GET /courses?filter[price_range]=50,100    # Between 50 and 100
GET /courses?filter[price_range]=100,      # 100 or more
GET /courses?filter[price_range]=,50       # 50 or less

# Search
GET /courses?filter[search]=laravel
```

### Sort Syntax

```
GET /endpoint?sort=field          # Ascending
GET /endpoint?sort=-field         # Descending (prefix with -)
```

### Examples

```
GET /courses?sort=price           # Price low to high
GET /courses?sort=-price          # Price high to low
GET /courses?sort=-created_at     # Newest first
```

### Pagination

```
GET /endpoint?per_page=10&page=2
```

---

## Error Handling

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "You are not enrolled in this course"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Course not found"
}
```

---

## Rate Limiting

- **Authenticated:** 60 requests/minute
- **Unauthenticated:** 30 requests/minute

When exceeded, returns `429 Too Many Requests`.
