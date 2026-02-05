# EduPlex LMS API Documentation for Postman

---

## Part 1: Auth Endpoints

Create API requests for EduPlex LMS
Base URL: http://localhost:8000/api
Use Bearer Token {{token}} for authentication

### AUTH FOLDER:

1. **POST /auth/register** (No auth)
   ```
   Headers: Content-Type: application/json, Accept: application/json
   Body: {
     "username": "john_doe",
     "email": "john@example.com",
     "password": "password123",
     "password_confirmation": "password123",
     "full_name": "John Doe",
     "phone": "+1234567890",
     "gender": "male",
     "date_of_birth": "1995-01-01"
   }
   ```

2. **POST /auth/login** (No auth)
   ```
   Headers: Content-Type: application/json, Accept: application/json
   Body: {"email":"student1@test.com","password":"password"}
   Test script: Save response.data.token to {{token}}
   ```

3. **GET /auth/profile**

4. **PUT /auth/profile**
   ```
   Body: {"full_name":"Updated Name","phone":"+123456789"}
   ```

5. **PUT /auth/change-password**
   ```
   Body: {
     "current_password": "password",
     "new_password": "newpass123",
     "new_password_confirmation": "newpass123"
   }
   ```

6. **POST /auth/logout**

---

## Part 2: Data Endpoints (Public - For Dropdowns)

Base URL: http://localhost:8000/api
No authentication required

### DATA FOLDER:

1. **GET /data** - Get all dropdown options
2. **GET /data/categories** - Categories for dropdown
3. **GET /data/courses** - Courses for dropdown
4. **GET /data/courses?category_id=1** - Courses filtered by category
5. **GET /data/categories/{category}/courses** - Courses by category
6. **GET /data/user-statuses** - User status options
7. **GET /data/student-statuses** - Student status options
8. **GET /data/lesson-types** - Lesson type options (video, text, quiz)
9. **GET /data/course-pricing-types** - Pricing type options (free, paid)
10. **GET /data/course-levels** - Level options (beginner, intermediate, advanced)
11. **GET /data/course-filters** - All course filter options combined
12. **GET /data/video-config** - Video upload configuration

---

## Part 3: Categories & Courses

Base URL: http://localhost:8000/api
Bearer Token: {{token}}

### CATEGORIES FOLDER:

1. **GET /categories**
2. **GET /categories/1**
3. **GET /categories/1/courses**

### COURSES FOLDER:

1. **GET /courses** - List all published courses
2. **GET /courses?filter[level]=beginner** - Filter by level
3. **GET /courses?filter[category_id]=1** - Filter by category
4. **GET /courses?filter[is_featured]=1** - Filter featured courses
5. **GET /courses?filter[pricing_type]=free** - Filter free courses
6. **GET /courses?filter[pricing_type]=paid** - Filter paid courses
7. **GET /courses?filter[price_range]=50,100** - Filter by price range ($50-$100)
8. **GET /courses?filter[price_range]=100,** - Filter price >= $100
9. **GET /courses?filter[price_range]=,50** - Filter price <= $50
10. **GET /courses?filter[search]=laravel** - Search courses
11. **GET /courses?per_page=10&page=1** - Pagination
12. **GET /courses?sort=price** - Sort by price ascending
13. **GET /courses?sort=-price** - Sort by price descending
14. **GET /courses?sort=-created_at** - Sort by newest

15. **GET /courses/1** - Get course detail
16. **GET /courses/1/modules** - Get course modules with lessons
17. **GET /courses/1/reviews** - Get course reviews

18. **POST /courses/1/reviews** - Create review
    ```
    Body: {
      "rating": 5,
      "review_text": "Great course!",
      "would_recommend": true
    }
    ```

---

## Part 4: Course Reviews

Base URL: http://localhost:8000/api
Bearer Token: {{token}}

### REVIEWS FOLDER:

1. **GET /reviews** - List user's reviews
2. **GET /reviews/1** - Get review detail
3. **GET /reviews/courses/1** - Get reviews by course

4. **POST /reviews/courses/1** - Create review for course
   ```
   Body: {
     "rating": 5,
     "review_text": "Excellent course content!",
     "would_recommend": true
   }
   ```

5. **PUT /reviews/1** - Update review
   ```
   Body: {
     "rating": 4,
     "review_text": "Updated review text",
     "would_recommend": true
   }
   ```

6. **DELETE /reviews/1** - Delete review

---

## Part 5: Enrollments

Base URL: http://localhost:8000/api
Bearer Token: {{token}}

### ENROLLMENTS FOLDER:

1. **GET /enrollments** - List user's enrollments
2. **GET /enrollments?filter[status]=active** - Filter by status
3. **GET /enrollments?filter[payment_status]=paid** - Filter by payment status

4. **POST /enrollments** - Enroll in course
   ```
   Body: {"course_id": 1}
   ```

5. **GET /enrollments/1** - Get enrollment detail with modules/lessons
6. **DELETE /enrollments/1** - Drop enrollment

---

## Part 6: Lessons & Progress

Base URL: http://localhost:8000/api
Bearer Token: {{token}}

### LESSONS FOLDER:

1. **GET /lessons/1** - Get lesson detail
2. **GET /lessons/1/progress** - Get lesson progress

3. **POST /lessons/1/progress** - Update lesson progress
   ```
   Body: {
     "status": "in_progress",
     "progress_percentage": 75,
     "video_last_position": 450,
     "time_spent_minutes": 30,
     "scroll_position": 500
   }
   ```

### PROGRESS FOLDER:

1. **GET /progress** - List all progress
2. **GET /progress/completed** - List completed lessons
3. **GET /progress/courses/1** - Get progress by course
4. **GET /progress/lessons/1** - Get specific lesson progress

5. **PUT /progress/lessons/1** - Update lesson progress
   ```
   Body: {
     "status": "completed",
     "progress_percentage": 100,
     "completed_at": "2026-02-05T12:00:00Z"
   }
   ```

---

## Part 7: Videos

Base URL: http://localhost:8000/api
Bearer Token: {{token}}

### VIDEOS FOLDER:

1. **GET /videos/config** - Get video upload configuration
2. **GET /videos/list** - List uploaded videos

3. **POST /videos/upload** - Upload video (multipart/form-data)
   ```
   Body (form-data):
     video: [file]
   ```

4. **POST /videos/metadata** - Get video metadata
   ```
   Body: {"path": "videos/example.mp4"}
   ```

5. **DELETE /videos/delete** - Delete video
   ```
   Body: {"path": "videos/example.mp4"}
   ```

6. **POST /videos/lessons/1/upload** - Upload video for lesson (multipart/form-data)
   ```
   Body (form-data):
     video: [file]
   ```

7. **DELETE /videos/lessons/1** - Delete video from lesson
8. **GET /videos/lessons/1/stream** - Stream lesson video

---

## Part 8: Quizzes

Base URL: http://localhost:8000/api
Bearer Token: {{token}}

### QUIZZES FOLDER:

1. **GET /quizzes/1** - Get quiz with questions
2. **POST /quizzes/1/attempts** - Start quiz attempt
3. **GET /quizzes/1/attempts** - Get user's attempts for quiz

4. **PUT /quizzes/attempts/1** - Submit quiz answers
   ```
   Body: {
     "answers": [
       {"question_id": 1, "selected_option_id": 1},
       {"question_id": 2, "selected_option_id": 5}
     ]
   }
   ```

5. **GET /quizzes/attempts/1** - Get attempt result

---

## Part 9: Payments

Base URL: http://localhost:8000/api
Bearer Token: {{token}}

### PAYMENTS FOLDER:

1. **GET /payments** - List user's payments
2. **GET /payments?filter[status]=completed** - Filter by status

3. **POST /payments** - Create payment
   ```
   Body: {
     "course_id": 1,
     "payment_method": "credit_card",
     "amount": 99.99
   }
   Note: payment_method options: credit_card, debit_card, paypal, bank_transfer
   ```

4. **GET /payments/1** - Get payment detail

---

## Part 10: Certificates

Base URL: http://localhost:8000/api
Bearer Token: {{token}} (except verify)

### CERTIFICATES FOLDER:

1. **GET /certificates** - List user's certificates
   Response includes: id, certificate_code, issue_date, verification_url, download_url, course info

2. **GET /certificates/1** - Get certificate detail
   Response includes: certificate details, student info, course info with category/level/duration

3. **GET /certificates/1/download** - Download certificate PDF
   Returns: PDF file (A4 landscape) with certificate design
   Filename: certificate-{code}.pdf

4. **GET /certificates/verify/CERT-ABC123** (No auth) - Verify certificate
   Response includes: is_valid, certificate_code, issue_date, student_name, course details

---

## Part 11: Notifications

Base URL: http://localhost:8000/api
Bearer Token: {{token}}

### NOTIFICATIONS FOLDER:

1. **GET /notifications** - List notifications
2. **GET /notifications?filter[is_read]=false** - Unread notifications
3. **PUT /notifications/1/read** - Mark as read
4. **PUT /notifications/read-all** - Mark all as read
5. **DELETE /notifications/1** - Delete notification

---

## Part 12: Dashboard

Base URL: http://localhost:8000/api
Bearer Token: {{token}}

### DASHBOARD FOLDER:

1. **GET /dashboard/stats** - Get dashboard statistics
2. **GET /dashboard/recent-activity** - Get recent activity
3. **GET /dashboard/activity-log** - Get activity log
4. **GET /dashboard/continue-learning** - Get courses to continue

---

## Response Format

All API responses follow this format:

### Success Response:
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional message"
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

### Error Response:
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

---

## Course Response Fields

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
  "is_featured": true,
  "status": "published",
  "category": {
    "id": 1,
    "category_name": "Web Development",
    "image_url": "/storage/categories/image.jpg"
  },
  "enrollments_count": 150,
  "average_rating": 4.5,
  "created_at": "2026-01-01T00:00:00Z"
}
```

---

## Filter Examples

### Combined Filters:
```
GET /courses?filter[category_id]=1&filter[level]=beginner&filter[pricing_type]=free
GET /courses?filter[price_range]=50,100&filter[is_featured]=1&sort=-created_at
GET /courses?filter[search]=laravel&filter[level]=intermediate&per_page=10
```

### Sorting:
```
GET /courses?sort=price          # Price ascending
GET /courses?sort=-price         # Price descending
GET /courses?sort=course_name    # Name A-Z
GET /courses?sort=-created_at    # Newest first
```
