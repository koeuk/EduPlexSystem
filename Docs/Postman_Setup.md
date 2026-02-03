# EduPlex LMS API - Postman Setup Guide

## Environment Setup

### 1. Create Environment

Create a new environment called `EduPlex Local` with these variables:

| Variable | Initial Value | Description |
|----------|---------------|-------------|
| `base_url` | `http://localhost:8000/api` | API base URL |
| `token` | (empty) | Auto-filled after login |

---

## Collection Structure

Create a collection called `EduPlex LMS API` with these folders:

```
EduPlex LMS API/
├── Auth/
├── Categories/
├── Courses/
├── Enrollments/
├── Lessons/
├── Quizzes/
├── Payments/
├── Certificates/
├── Notifications/
└── Dashboard/
```

---

## Collection-Level Settings

### Authorization Tab
- Type: `Bearer Token`
- Token: `{{token}}`

### Pre-request Script (Collection Level)
```javascript
// Auto-refresh token if needed
// Add any global pre-request logic here
```

---

## Auth Endpoints

### 1. Register Student
```
POST {{base_url}}/auth/register
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "username": "john_doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "full_name": "John Doe",
    "phone": "+1234567890",
    "date_of_birth": "1995-05-15",
    "gender": "male"
}
```

**Tests Script (auto-save token):**
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    pm.environment.set("token", jsonData.data.token);
    console.log("Token saved:", jsonData.data.token);
}
```

---

### 2. Login
```
POST {{base_url}}/auth/login
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Tests Script (auto-save token):**
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("token", jsonData.data.token);
    console.log("Token saved:", jsonData.data.token);
}
```

---

### 3. Get Profile
```
GET {{base_url}}/auth/profile
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 4. Update Profile
```
PUT {{base_url}}/auth/profile
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{token}}
```

**Body (raw JSON):**
```json
{
    "full_name": "John Doe Updated",
    "phone": "+1987654321",
    "date_of_birth": "1995-06-20",
    "gender": "male",
    "address": "123 Main Street, City"
}
```

---

### 5. Change Password
```
PUT {{base_url}}/auth/change-password
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{token}}
```

**Body (raw JSON):**
```json
{
    "current_password": "password123",
    "password": "newpassword456",
    "password_confirmation": "newpassword456"
}
```

---

### 6. Logout
```
POST {{base_url}}/auth/logout
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Tests Script (clear token):**
```javascript
if (pm.response.code === 200) {
    pm.environment.unset("token");
    console.log("Token cleared");
}
```

---

## Categories Endpoints

### 1. List Categories
```
GET {{base_url}}/categories
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Query Parameters (optional):**
```
filter[search]=programming
filter[status]=active
sort=-created_at
per_page=20
page=1
```

---

### 2. Get Category
```
GET {{base_url}}/categories/:id
```

**Path Variables:**
- `id`: Category ID (e.g., `1`)

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 3. Get Category Courses
```
GET {{base_url}}/categories/:id/courses
```

**Path Variables:**
- `id`: Category ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Query Parameters (optional):**
```
filter[level]=beginner
filter[status]=published
sort=-created_at
per_page=10
```

---

## Courses Endpoints

### 1. List Courses
```
GET {{base_url}}/courses
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Query Parameters (optional):**
```
filter[search]=laravel
filter[category_id]=1
filter[level]=beginner
filter[status]=published
filter[is_free]=true
sort=-created_at
per_page=20
page=1
```

---

### 2. Get Course Details
```
GET {{base_url}}/courses/:id
```

**Path Variables:**
- `id`: Course ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 3. Get Course Modules (Enrolled Only)
```
GET {{base_url}}/courses/:id/modules
```

**Path Variables:**
- `id`: Course ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 4. Get Course Reviews
```
GET {{base_url}}/courses/:id/reviews
```

**Path Variables:**
- `id`: Course ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Query Parameters (optional):**
```
sort=-created_at
per_page=10
```

---

### 5. Add Course Review
```
POST {{base_url}}/courses/:id/reviews
```

**Path Variables:**
- `id`: Course ID

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{token}}
```

**Body (raw JSON):**
```json
{
    "rating": 5,
    "review": "Excellent course! Very comprehensive and well-structured."
}
```

---

### 6. Update Review
```
PUT {{base_url}}/courses/reviews/:id
```

**Path Variables:**
- `id`: Review ID

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{token}}
```

**Body (raw JSON):**
```json
{
    "rating": 4,
    "review": "Updated review text here."
}
```

---

### 7. Delete Review
```
DELETE {{base_url}}/courses/reviews/:id
```

**Path Variables:**
- `id`: Review ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

## Enrollments Endpoints

### 1. List My Enrollments
```
GET {{base_url}}/enrollments
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Query Parameters (optional):**
```
filter[status]=active
sort=-enrolled_at
per_page=10
```

---

### 2. Enroll in Course
```
POST {{base_url}}/enrollments
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{token}}
```

**Body (raw JSON):**
```json
{
    "course_id": 1
}
```

---

### 3. Get Enrollment Details
```
GET {{base_url}}/enrollments/:id
```

**Path Variables:**
- `id`: Enrollment ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 4. Drop Enrollment
```
DELETE {{base_url}}/enrollments/:id
```

**Path Variables:**
- `id`: Enrollment ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

## Lessons Endpoints

### 1. Get Lesson
```
GET {{base_url}}/lessons/:id
```

**Path Variables:**
- `id`: Lesson ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 2. Update Lesson Progress
```
POST {{base_url}}/lessons/:id/progress
```

**Path Variables:**
- `id`: Lesson ID

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{token}}
```

**Body (raw JSON):**
```json
{
    "progress_percentage": 75,
    "last_position": 450,
    "is_completed": false
}
```

---

### 3. Get Lesson Progress
```
GET {{base_url}}/lessons/:id/progress
```

**Path Variables:**
- `id`: Lesson ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

## Quizzes Endpoints

### 1. Get Quiz
```
GET {{base_url}}/quizzes/:id
```

**Path Variables:**
- `id`: Quiz ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 2. Start Quiz Attempt
```
POST {{base_url}}/quizzes/:id/attempts
```

**Path Variables:**
- `id`: Quiz ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Tests Script (save attempt ID):**
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    pm.environment.set("attempt_id", jsonData.data.id);
}
```

---

### 3. Get Quiz Attempts History
```
GET {{base_url}}/quizzes/:id/attempts
```

**Path Variables:**
- `id`: Quiz ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 4. Submit Quiz Attempt
```
PUT {{base_url}}/quizzes/attempts/:id
```

**Path Variables:**
- `id`: Attempt ID

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{token}}
```

**Body (raw JSON):**
```json
{
    "answers": [
        {
            "question_id": 1,
            "selected_option_id": 3
        },
        {
            "question_id": 2,
            "selected_option_id": 7
        },
        {
            "question_id": 3,
            "selected_option_id": 10
        }
    ]
}
```

---

### 5. Get Attempt Results
```
GET {{base_url}}/quizzes/attempts/:id
```

**Path Variables:**
- `id`: Attempt ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

## Payments Endpoints

### 1. List My Payments
```
GET {{base_url}}/payments
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Query Parameters (optional):**
```
filter[status]=completed
sort=-created_at
per_page=10
```

---

### 2. Process Payment
```
POST {{base_url}}/payments
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {{token}}
```

**Body (raw JSON):**
```json
{
    "course_id": 1,
    "payment_method": "credit_card",
    "amount": 99.99,
    "currency": "USD",
    "transaction_id": "TXN_123456789"
}
```

---

### 3. Get Payment Details
```
GET {{base_url}}/payments/:id
```

**Path Variables:**
- `id`: Payment ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

## Certificates Endpoints

### 1. List My Certificates
```
GET {{base_url}}/certificates
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 2. Get Certificate
```
GET {{base_url}}/certificates/:id
```

**Path Variables:**
- `id`: Certificate ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 3. Download Certificate PDF
```
GET {{base_url}}/certificates/:id/download
```

**Path Variables:**
- `id`: Certificate ID

**Headers:**
```
Accept: application/pdf
Authorization: Bearer {{token}}
```

---

### 4. Verify Certificate (Public)
```
GET {{base_url}}/certificates/verify/:code
```

**Path Variables:**
- `code`: Certificate verification code

**Headers:**
```
Accept: application/json
```

*Note: This endpoint does not require authentication.*

---

## Notifications Endpoints

### 1. List Notifications
```
GET {{base_url}}/notifications
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Query Parameters (optional):**
```
filter[is_read]=false
sort=-created_at
per_page=20
```

---

### 2. Mark as Read
```
PUT {{base_url}}/notifications/:id/read
```

**Path Variables:**
- `id`: Notification ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 3. Mark All as Read
```
PUT {{base_url}}/notifications/read-all
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 4. Delete Notification
```
DELETE {{base_url}}/notifications/:id
```

**Path Variables:**
- `id`: Notification ID

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

## Dashboard Endpoints

### 1. Get Stats
```
GET {{base_url}}/dashboard/stats
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 2. Get Recent Activity
```
GET {{base_url}}/dashboard/recent-activity
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

### 3. Get Activity Log
```
GET {{base_url}}/dashboard/activity-log
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

**Query Parameters (optional):**
```
per_page=20
page=1
```

---

### 4. Get Continue Learning
```
GET {{base_url}}/dashboard/continue-learning
```

**Headers:**
```
Accept: application/json
Authorization: Bearer {{token}}
```

---

## Quick Setup Steps

### Step 1: Import Collection
1. Open Postman
2. Click **Import** > **Raw Text**
3. Paste the collection JSON (see below)

### Step 2: Create Environment
1. Click **Environments** > **Create Environment**
2. Name: `EduPlex Local`
3. Add variable: `base_url` = `http://localhost:8000/api`
4. Add variable: `token` = (leave empty)
5. Click **Save**

### Step 3: Test Flow
1. Select `EduPlex Local` environment
2. Run **Register** or **Login** request
3. Token is auto-saved to environment
4. Test other endpoints

---

## Testing Tips

### 1. Always Login First
Run the Login request before testing protected endpoints. The token is automatically saved.

### 2. Check Response Codes
- `200` - Success
- `201` - Created
- `204` - No Content (successful delete)
- `400` - Bad Request (validation error)
- `401` - Unauthenticated
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests (rate limited)
- `500` - Server Error

### 3. Use Variables
Replace hardcoded IDs with variables:
- `{{course_id}}`
- `{{enrollment_id}}`
- `{{lesson_id}}`

### 4. Run Collection
Use Collection Runner to test all endpoints in sequence.

---

## Rate Limiting

The API is rate limited to **60 requests per minute** per user/IP.

If you exceed this limit, you'll receive:
```json
{
    "message": "Too Many Attempts.",
    "retry_after": 45
}
```

Wait for the `retry_after` seconds before making more requests.
