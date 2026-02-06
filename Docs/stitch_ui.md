# EduPlex LMS - Stitch UI Design Prompts

> Step-by-step prompts for designing every screen of the EduPlex mobile app.
> Each prompt maps directly to API endpoints and real data structures.
> App Style: Modern educational platform, clean UI, blue/green brand colors.

---

## STEP 1: Splash & Onboarding

### Prompt 1.1 — Splash Screen
```
Design a mobile splash screen for "EduPlex LMS" — an online learning platform.
Center the EduPlex logo (graduation cap icon with book) on a white background.
Below the logo, show "EduPlex" in bold blue (#1E40AF) text and a subtle tagline
"Learn. Grow. Succeed." in gray. Add a circular loading spinner at the bottom.
Use blue (#1E40AF) and green (#059669) as brand colors.
```

### Prompt 1.2 — Onboarding Walkthrough (3 slides)
```
Design a 3-slide onboarding walkthrough for a mobile learning app:

Slide 1: "Discover Courses" — illustration of a person browsing courses on a phone.
Show categories like Programming, Design, Business as floating cards.

Slide 2: "Learn at Your Pace" — illustration of a person watching video lessons
with a progress bar. Show a play button and lesson checklist.

Slide 3: "Earn Certificates" — illustration of a person holding a certificate
with a graduation cap. Show a verified badge.

Each slide has: centered illustration, bold title, subtitle text, dot indicators
at bottom, "Skip" link top-right, "Next" button at bottom. Last slide shows
"Get Started" button instead. Blue/green brand colors.
```

---

## STEP 2: Authentication Screens

### Prompt 2.1 — Login Screen
```
Design a mobile login screen for EduPlex LMS.

Top section: EduPlex logo + "Welcome Back" heading + "Sign in to continue learning" subtitle.

Form fields:
- Email input field with mail icon (required, email format)
- Password input field with lock icon and show/hide toggle (required, min 8 chars)

Below fields:
- "Forgot Password?" link aligned right
- Primary "Sign In" button (full width, blue #1E40AF, rounded)
- Divider with "OR"
- "Don't have an account? Sign Up" link at bottom

Clean white background, rounded input fields with light gray borders.
Show error state version: red border on fields, error message below each field
like "Invalid email or password."
```

### Prompt 2.2 — Registration Screen
```
Design a mobile registration screen for EduPlex LMS with a scrollable form.

Top: "Create Account" heading + "Start your learning journey" subtitle.

Form fields (in order):
- Profile picture upload: circular avatar placeholder with camera icon overlay,
  tap to upload (accepts jpeg, png, webp, max 2MB)
- Full Name (required, text input, person icon)
- Username (required, text input, @ icon, must be unique)
- Email (required, email input, mail icon, must be unique)
- Password (required, min 8 chars, lock icon, show/hide toggle)
- Confirm Password (required, must match, lock icon)
- Phone (optional, phone input with country code)
- Date of Birth (optional, date picker, must be before today)
- Gender (optional, dropdown selector: Male, Female, Other)

Bottom:
- "Sign Up" primary button (full width, blue)
- "Already have an account? Sign In" link

Show validation states: red borders for errors, green checkmark for valid fields.
Show inline error messages like "Username already taken" or "Passwords don't match."
```

---

## STEP 3: Main Dashboard (Home)

### Prompt 3.1 — Dashboard Home Screen
```
Design the main dashboard/home screen for a mobile learning app.

Top bar: "Hi, [Student Name]" greeting with profile avatar on left,
notification bell icon on right with red unread badge count.

Section 1 — Stats Cards (horizontal scroll, 4 cards):
- "Enrolled" — number of enrolled courses, blue icon
- "Completed" — completed courses count, green icon
- "In Progress" — active courses count, orange icon
- "Certificates" — certificates earned count, purple icon
Also show: total learning time in hours, average progress percentage.

Section 2 — "Continue Learning" (vertical list, max 5 items):
Each card shows:
- Course thumbnail (small, left side)
- Course name (bold, 1 line)
- Module name > Lesson name (gray subtitle, next lesson to take)
- Progress bar with percentage (e.g., "65% complete")
- "Continue" button/arrow on right
- Last accessed time ("2 hours ago")

Section 3 — "Recent Activity" (vertical list, last 10):
Each item shows:
- Activity icon (play for video, check for completed, pencil for quiz)
- Lesson name + course name
- Status badge (in_progress=yellow, completed=green)
- Progress percentage
- Time spent (e.g., "15 min")
- Timestamp

Bottom navigation bar with 5 tabs: Home, Courses, My Learning, Certificates, Profile.
Active tab highlighted in blue.
```

---

## STEP 4: Course Discovery & Browsing

### Prompt 4.1 — Course Catalog Screen
```
Design a course catalog/browse screen for a mobile learning app.

Top: "Explore Courses" heading with a search bar below it.
Search bar: magnifying glass icon, placeholder "Search courses, instructors..."
(searches across course_name, course_code, description, instructor_name)

Filter bar (horizontal scroll chips below search):
- Category dropdown (loads from API: list of active categories)
- Level chips: All, Beginner, Intermediate, Advanced
- Price chips: All, Free, Paid
- Price range slider (min-max, e.g., $0 - $500)
- Featured toggle: star icon chip
- Sort dropdown: "Name A-Z", "Name Z-A", "Price Low-High",
  "Price High-Low", "Newest", "Oldest"

Active filters show as dismissible chips below the filter bar.

Course grid (2 columns) or list view toggle:
Each course card shows:
- Course thumbnail image (responsive, 400x300)
- "FREE" green badge or price "$49.99" tag (top-right overlay)
- "Featured" star badge (if is_featured=true)
- Course name (bold, max 2 lines)
- Instructor name (gray, small)
- Level badge (Beginner=green, Intermediate=blue, Advanced=purple)
- Star rating with count (e.g., "4.5 (128 reviews)")
- Duration (e.g., "12 hours")
- Enrollment count (e.g., "234 students")

Pagination: "Load More" button at bottom or infinite scroll.
20 items per page.

Empty state: illustration + "No courses found" + "Try adjusting your filters" message.
```

### Prompt 4.2 — Category Browsing Screen
```
Design a category browsing screen for a mobile learning app.

Top: "Categories" heading.

Grid layout (2 columns) of category cards:
Each card shows:
- Category icon (from API)
- Category image background (from API, with gradient overlay)
- Category name (bold, white text over image)
- Course count badge (e.g., "24 courses")

Tapping a category navigates to a filtered course list showing only
courses from that category, with the same filter/sort options as the
main course catalog.

Show category name as page title with back arrow.
Additional filters available: Level, Featured, Price.
Sortable by: name, price, date.
```

### Prompt 4.3 — Course Detail Screen
```
Design a course detail screen for a mobile learning app.

Hero section:
- Full-width course thumbnail/banner image (800x600)
- Back arrow and share button overlay on image
- "FREE" or "$49.99" price badge

Course info section:
- Course name (large, bold)
- Course code (small, gray, e.g., "CS-101")
- Category badge chip (tappable, links to category)
- Level badge (Beginner=green, Intermediate=blue, Advanced=purple)
- Instructor name with instructor icon
- Duration (e.g., "24 hours")
- Rating: filled stars + "4.5" + "(128 reviews)" — tappable to scroll to reviews
- Enrollment count (e.g., "456 students enrolled")

Description section:
- "About This Course" heading
- Course description text (expandable "Read More" if long)

Curriculum/Modules section:
- "Curriculum" heading with total module count
- Accordion list of modules (collapsed by default):
  Each module shows: module name, lesson count
  LOCKED state: lock icon, "Enroll to access" message
  (modules with lessons are only visible to enrolled students)

Reviews section (preview):
- "Reviews" heading with average rating and count
- Top 3 review cards showing:
  - Student avatar + name
  - Star rating
  - Review text (truncated)
  - "Would recommend" badge if true
  - Date
- "See All Reviews" link (navigates to full reviews page)

Sticky bottom bar:
- If NOT enrolled: "Enroll Now" button (blue, full width).
  If paid course: shows price + "Enroll Now — $49.99"
  If free: "Enroll Now — Free"
- If ENROLLED (active): "Continue Learning" button (green)
- If ENROLLED (completed): "Completed" badge + "View Certificate" link
- If enrollment at limit: "Course Full" disabled button

Show enrollment status and payment_status if enrolled.
```

---

## STEP 5: Enrollment Flow

### Prompt 5.1 — Enrollment Confirmation Modal
```
Design an enrollment confirmation bottom sheet/modal for a mobile learning app.

For FREE courses:
- Course thumbnail + name at top
- "Free Course" green badge
- Bullet points: duration, level, module count, instructor
- "Confirm Enrollment" primary button
- "Cancel" text button

For PAID courses:
- Course thumbnail + name at top
- Price prominently displayed "$49.99"
- Bullet points: duration, level, module count, instructor
- "Proceed to Payment" primary button
- "Cancel" text button

Error states:
- "You are already enrolled in this course" — info banner with link to course
- "This course has reached its enrollment limit" — warning banner
- "This course is no longer available" — error banner
```

### Prompt 5.2 — Payment Screen
```
Design a payment screen for a mobile learning app.

Top: "Payment" heading with back arrow.

Order summary card:
- Course thumbnail + name
- Price breakdown: Course price, any discounts
- Total amount (bold, large)

Payment method selection:
- Credit Card (card icon) — selected state with blue border
- Debit Card (card icon)
- PayPal (PayPal logo)
- Bank Transfer (bank icon)

Each method is a selectable card/radio button.

When Credit/Debit Card selected, show card form fields:
- Card number input with card type detection icon
- Expiry date (MM/YY) and CVV side by side
- Cardholder name

"Pay $49.99" primary button at bottom (full width).

Processing state: loading overlay with "Processing payment..." text.

Success state: green checkmark animation + "Payment Successful!" +
transaction ID (e.g., "TXN-ABCDEF123456") + "Start Learning" button.

Failed state: red X icon + "Payment Failed" + error message +
"Try Again" button + "Change Payment Method" link.
```

---

## STEP 6: My Learning / Enrollments

### Prompt 6.1 — My Enrollments Screen
```
Design a "My Learning" screen showing all enrolled courses.

Top: "My Learning" heading.

Filter tabs (horizontal): All, Active, Completed, Dropped
(maps to filter[status] = active, completed, dropped, expired)

Sort options: "Recent", "Progress", "Name"
(maps to sort: enrollment_date, progress_percentage)

Payment filter: All, Paid, Pending, Failed, Refunded
(maps to filter[payment_status])

Course enrollment cards (vertical list):
Each card shows:
- Course thumbnail (left)
- Course name (bold)
- Category name (chip/tag)
- Enrollment date (e.g., "Enrolled: Jan 15, 2026")
- Progress bar with percentage (e.g., "72%")
- Status badge:
  - Active = blue badge
  - Completed = green badge with checkmark
  - Dropped = gray badge
  - Expired = red badge
- Payment status indicator:
  - Paid = green "Paid" chip
  - Pending = yellow "Payment Pending" chip
  - Failed = red "Payment Failed" chip
- Last accessed (e.g., "Last accessed: 2 hours ago")

Swipe actions on each card:
- Swipe left: "Drop Course" (red) — only for active enrollments
- Cannot drop completed courses

Tapping a card navigates to the Course Learning Screen.

Empty state: illustration + "No courses yet" + "Browse Courses" button.

Pagination: 20 items per page, load more on scroll.
```

### Prompt 6.2 — Enrollment Detail / Course Learning Screen
```
Design a course learning screen (after enrollment) for a mobile learning app.

Top bar: Back arrow + Course name (truncated) + options menu (three dots).

Course progress header:
- Circular progress indicator (large, centered) showing percentage
- "72% Complete" text
- Status badge (Active/Completed)
- Payment status if pending
- Last accessed date

Modules accordion list:
Each module section:
- Module name (bold) + expand/collapse chevron
- Lesson count + completed count (e.g., "4/6 lessons completed")
- Module progress bar

When expanded, lessons list inside module:
Each lesson item shows:
- Lesson order number (circle)
- Lesson title
- Lesson type icon:
  - Video = play icon + duration (e.g., "15 min")
  - Text = document icon + "Reading"
  - Quiz = question mark icon + "Quiz"
- Is mandatory badge (if true)
- Progress status:
  - Not started = gray circle
  - In progress = yellow/blue half circle + percentage
  - Completed = green checkmark circle
- Time spent (e.g., "12 min spent")

Tapping a lesson navigates to the Lesson Viewer.

Bottom section:
- "Drop Course" button (red outline, only if status=active, not completed)

If enrollment status is completed:
- Show "Course Completed!" banner with confetti
- "View Certificate" button if certificate_issued=true
```

---

## STEP 7: Lesson Viewer

### Prompt 7.1 — Video Lesson Screen
```
Design a video lesson viewer screen for a mobile learning app.

Top bar: Back arrow + lesson title + module name (subtitle).

Video player section (top half):
- Full-width video player with standard controls
- Play/pause, seek bar with buffered progress
- Current time / total duration display
- Fullscreen toggle button
- Video resumes from last position (video_last_position from API)
- Quality selector if available

Below video:
- Lesson title (large, bold)
- Lesson type badge: "Video" with play icon
- Duration: "15 minutes"
- Mandatory badge if is_mandatory=true

Description section:
- Lesson description text (expandable)
- Lesson content (rich text/HTML content)

Documents section (if lesson has attachments):
- "Resources" heading
- List of downloadable documents:
  - PDF icon + filename + file size
  - DOCX icon + filename + file size
  - Tap to download/preview

Progress section:
- Current progress bar with percentage
- Time spent: "12 minutes spent"
- Status: "In Progress" / "Completed"
- "Mark as Complete" button (if not yet completed)

Navigation:
- "Previous Lesson" and "Next Lesson" buttons at bottom
- Shows next lesson name as preview text

Auto-tracking: progress updates as video plays,
saves video_last_position, time_spent_minutes, scroll_position.
```

### Prompt 7.2 — Text Lesson Screen
```
Design a text/reading lesson screen for a mobile learning app.

Top bar: Back arrow + lesson title.

Content area (scrollable):
- Lesson title (large heading)
- "Reading" type badge with estimated duration
- Mandatory badge if applicable
- Rich text content area (HTML rendered):
  - Headings, paragraphs, lists, code blocks, images
  - Good typography, readable line spacing

Documents section:
- Attached PDF/DOCX files with download buttons

Progress section:
- Reading progress bar (tracks scroll_position)
- Time spent counter
- "Mark as Complete" button at the bottom

Navigation: Previous/Next lesson buttons.
```

### Prompt 7.3 — Quiz Lesson Screen (Quiz Entry)
```
Design a quiz entry/info screen before starting a quiz.

Top bar: Back arrow + "Quiz" title.

Quiz info card:
- Quiz title (large, bold)
- Instructions text (from quiz.instructions)
- Info grid:
  - Total questions count
  - Total points
  - Time limit (e.g., "30 minutes") or "No time limit"
  - Passing score (e.g., "70%")
  - Max attempts (e.g., "3 attempts") or "Unlimited"
  - Attempts used (e.g., "1 of 3 used")

Rules section:
- "Questions may be randomized" note (if randomize_questions=true)
- "Correct answers shown after submission" note (if show_correct_answers=true)

Previous attempts section (if any):
- List of past attempts:
  - Attempt #1 — Score: 85% — Passed (green) — Jan 15, 2026
  - Attempt #2 — Score: 60% — Failed (red) — Jan 16, 2026
  - Tappable to view attempt details

Action button:
- "Start Quiz" primary button (blue)
- Disabled with "Maximum attempts reached" if can_attempt=false
- "You have X attempts remaining" text
```

---

## STEP 8: Quiz Taking & Results

### Prompt 8.1 — Quiz Taking Screen
```
Design an active quiz-taking screen for a mobile learning app.

Top bar:
- "Quiz" title
- Timer countdown (if time_limit_minutes set): "24:35" in red/orange
  when < 5 minutes
- Question counter: "3 of 15"
- Progress bar showing questions answered

Question card:
- Question number: "Question 3"
- Question text (large, clear)
- Question image (if present, displayed below text, max 800x600)
- Points for this question

Answer options (for multiple_choice / true_false):
- Radio button style selection
- Each option: letter (A, B, C, D) + option text
- Selected state: blue background, white text
- Unselected state: white background, gray border

Answer input (for text answers):
- Text input field with placeholder "Type your answer..."

Navigation:
- "Previous" and "Next" buttons at bottom
- Question dots/bubbles navigator (horizontal scroll):
  - Gray = unanswered
  - Blue = answered
  - Current = blue with border
- "Submit Quiz" button on last question (or accessible anytime)

Submit confirmation modal:
- "Submit Quiz?"
- "You've answered X of Y questions"
- "Unanswered questions: [list numbers]"
- "Submit" and "Review Answers" buttons
```

### Prompt 8.2 — Quiz Results Screen
```
Design a quiz results screen for a mobile learning app.

Result header:
- Large circular score display: "85%" in center
- Green background + checkmark if passed (score >= passing_score)
- Red background + X icon if failed
- "Passed!" or "Failed" text below score

Stats grid:
- Score: 85/100 points
- Total Points: 100
- Passing Score: 70%
- Time Taken: "12 min 34 sec"
- Attempt Number: #2

If show_correct_answers is true, show answer review section:
Question-by-question review list:
Each question:
- Question number + text
- Question image (if present)
- Your answer: highlighted in green (correct) or red (incorrect)
- Correct answer: shown in green if yours was wrong
- Points earned: "5/5" or "0/5"

If show_correct_answers is false:
- "Detailed answers are not available for this quiz" message

Action buttons:
- "Retry Quiz" button (if attempts remaining, can_attempt=true)
- "Back to Lesson" button
- "View All Attempts" link
```

---

## STEP 9: Certificates

### Prompt 9.1 — Certificates List Screen
```
Design a certificates gallery screen for a mobile learning app.

Top: "My Certificates" heading.

Certificates grid (1 column, card style):
Each certificate card:
- Certificate preview (miniature representation with blue/green brand border)
- Course name (bold)
- Category name (chip)
- Certificate code (e.g., "CERT-ABC123XYZ")
- Issue date (e.g., "Issued: January 20, 2026")
- Level badge (Beginner/Intermediate/Advanced)
- Action buttons row:
  - "View" button (eye icon)
  - "Download PDF" button (download icon)
  - "Share" button (share icon)

Empty state: illustration of empty certificate + "Complete a course to earn
your first certificate!" + "Browse Courses" button.
```

### Prompt 9.2 — Certificate Detail Screen
```
Design a certificate detail/preview screen for a mobile learning app.

Certificate display (centered, A4 landscape aspect ratio):
- Decorative border (blue #1E40AF and green #059669)
- "Certificate of Completion" title
- Student full name (large, elegant font)
- "has successfully completed"
- Course name (bold)
- Category name
- Level (Beginner/Intermediate/Advanced)
- Duration (e.g., "24 hours")
- Issue date (formatted nicely)
- Certificate code at bottom
- EduPlex logo

Certificate details below:
- Student name
- Student ID number
- Course code
- Instructor name
- Course thumbnail

Action buttons (bottom):
- "Download PDF" primary button (generates A4 landscape PDF)
- "Share Certificate" button
- "Verify Certificate" link (opens public verification URL)
```

### Prompt 9.3 — Public Certificate Verification Screen
```
Design a public certificate verification screen (no login required).

Top: EduPlex logo + "Certificate Verification" heading.

Search section:
- Input field: "Enter certificate code" with search icon
- "Verify" button

Result — Valid certificate:
- Green checkmark + "Valid Certificate"
- Certificate code displayed
- Student name
- Course name + course code
- Category
- Level
- Duration
- Instructor name
- Issue date

Result — Invalid certificate:
- Red X icon + "Certificate Not Found"
- "The certificate code you entered is not valid."
- "Try Again" button

This screen is publicly accessible without authentication.
```

---

## STEP 10: Reviews

### Prompt 10.1 — Course Reviews Screen (Full List)
```
Design a full course reviews screen for a mobile learning app.

Top: Back arrow + "Reviews" heading + course name subtitle.

Summary section:
- Large average rating number (e.g., "4.5")
- 5 filled/empty stars
- Total review count (e.g., "128 reviews")
- Rating distribution bars:
  - 5 stars: ████████░░ 65%
  - 4 stars: ████░░░░░░ 20%
  - 3 stars: ██░░░░░░░░ 10%
  - 2 stars: █░░░░░░░░░ 3%
  - 1 star:  ░░░░░░░░░░ 2%

Filter: Rating dropdown (All, 5 stars, 4 stars, ... 1 star)
Sort: "Newest", "Oldest", "Highest", "Lowest"

Reviews list (paginated, 20 per page):
Each review card:
- Student avatar (200x200 thumbnail) + full name
- Star rating (filled stars)
- "Would recommend" green badge (if would_recommend=true)
- Review text (expandable if long, max 1000 chars)
- Date posted (relative, e.g., "2 days ago")

If the current user has a review, show it pinned at top with
"Your Review" label and Edit/Delete actions.

"Write a Review" floating action button (only if enrolled,
active or completed, and no existing review).
```

### Prompt 10.2 — Write/Edit Review Screen
```
Design a review submission screen for a mobile learning app.

Top: "Write a Review" or "Edit Review" heading.

Course preview: thumbnail + course name (non-editable context).

Rating input:
- 5 large tappable stars (required)
- "Tap to rate" prompt, shows "Excellent!", "Good", etc. based on selection

Review text:
- Multi-line text area (optional, max 1000 characters)
- Character counter: "234/1000"
- Placeholder: "Share your experience with this course..."

Recommendation:
- "Would you recommend this course?" label
- Toggle switch or Yes/No buttons (required)

"Submit Review" primary button.
"Cancel" text link.

For edit mode: pre-fill existing values, button says "Update Review".
Show "Delete Review" red text link at bottom.

Delete confirmation modal:
- "Delete your review for [Course Name]?"
- "Delete" red button + "Cancel" button
```

### Prompt 10.3 — My Reviews Screen
```
Design a "My Reviews" screen showing all reviews by the current student.

Top: "My Reviews" heading.

Filter: by course, by rating.
Sort: newest, oldest, highest, lowest rating.

Review cards (vertical list):
Each card:
- Course thumbnail + course name (tappable, navigates to course)
- Star rating
- Review text (truncated to 2 lines)
- "Would recommend" badge
- Date posted
- Edit (pencil) and Delete (trash) icon buttons

Tapping a card expands to show full review text.
Empty state: "You haven't written any reviews yet."
```

---

## STEP 11: Payments & Transactions

### Prompt 11.1 — Payment History Screen
```
Design a payment history screen for a mobile learning app.

Top: "Payment History" heading.

Filter tabs: All, Completed, Pending, Failed, Refunded
(maps to filter[status])

Payment method filter: All, Credit Card, Debit Card, PayPal, Bank Transfer
(maps to filter[payment_method])

Sort: "Newest", "Oldest", "Amount High-Low", "Amount Low-High"

Transaction list (paginated, 20 per page):
Each transaction card:
- Course thumbnail (small) + course name
- Transaction ID (e.g., "TXN-ABCDEF123456") in monospace font
- Amount (bold, e.g., "$49.99")
- Payment method icon + name (e.g., credit card icon + "Credit Card")
- Payment date (e.g., "Jan 15, 2026, 2:30 PM")
- Status badge:
  - Completed = green
  - Pending = yellow/orange
  - Failed = red
  - Refunded = purple

Tapping a card navigates to Payment Detail.

Empty state: "No transactions yet."
```

### Prompt 11.2 — Payment Detail Screen
```
Design a payment receipt/detail screen for a mobile learning app.

Top: "Payment Details" heading with back arrow.

Receipt card (white, elevated, receipt style with dashed border):
- "EduPlex" logo at top
- Transaction ID: "TXN-ABCDEF123456"
- Status badge (large): Completed/Pending/Failed/Refunded

Course section:
- Course thumbnail
- Course name
- Course code
- Course price

Payment section:
- Payment method (icon + name)
- Amount paid (large, bold)
- Payment date and time

Divider line (dashed)

Total: Amount (large, bold)

If status is "completed":
- Green checkmark + "Payment successful"
If status is "pending":
- Yellow clock + "Payment is being processed"
If status is "failed":
- Red X + "Payment failed" + "Retry Payment" button
```

---

## STEP 12: Notifications

### Prompt 12.1 — Notifications Screen
```
Design a notifications screen for a mobile learning app.

Top bar: "Notifications" heading + "Mark All Read" text button (right).

Filter chips (horizontal scroll):
- All
- Enrollment (enrollment icon)
- Completion (checkmark icon)
- Reminders (clock icon)
- Info (info icon)
- Success (check icon)
- Warning (warning icon)
- Error (error icon)
(maps to filter[type] = info, success, warning, error, enrollment,
completion, reminder)

Filter toggle: All / Unread only (maps to filter[is_read]=false)

Notification list (paginated, 20 per page):
Each notification item:
- Type icon (color-coded):
  - info = blue info circle
  - success = green checkmark
  - warning = yellow/orange triangle
  - error = red X circle
  - enrollment = blue book icon
  - completion = green trophy icon
  - reminder = orange bell icon
- Title (bold, 1 line)
- Message text (gray, max 2 lines truncated)
- Timestamp (relative: "5 min ago", "2 hours ago", "Yesterday")
- Unread indicator: blue dot on left if is_read=false
- Related content link (if related_id exists, tappable to navigate)

Swipe actions:
- Swipe right: Mark as read (blue)
- Swipe left: Delete (red)

Tap notification: marks as read + navigates to related content if applicable.

Empty state: bell icon illustration + "No notifications yet" +
"You'll be notified about your courses and progress here."
```

---

## STEP 13: Student Profile

### Prompt 13.1 — Profile Screen
```
Design a student profile screen for a mobile learning app.

Profile header (blue/green gradient background):
- Large circular profile picture (500x500 from media library)
- Camera icon overlay to change photo
- Full name (large, white, bold)
- Student ID number (small, white, e.g., "STU-2026-ABC123")
- Account status badge (Active=green, Inactive=gray, Suspended=red)

Info section (white cards):
Card 1 — Personal Information:
- Full Name
- Username
- Email
- Phone (or "Not provided")
- Date of Birth (formatted, or "Not provided")
- Gender (or "Not provided")
- Address (or "Not provided")
- "Edit Profile" button

Card 2 — Learning Stats:
- Courses enrolled
- Courses completed
- Certificates earned
- Total learning time
- Average progress

Card 3 — Account:
- "Change Password" row with chevron
- "Notification Settings" row with chevron
- "Activity Log" row with chevron

Bottom:
- "Logout" button (red outline)
- App version text (small, gray)
```

### Prompt 13.2 — Edit Profile Screen
```
Design an edit profile screen for a mobile learning app.

Top: "Edit Profile" heading with back arrow and "Save" button (top right).

Profile picture section:
- Current profile picture (large circle)
- "Change Photo" button below (accepts jpeg, png, webp, max 2MB)
- "Remove Photo" text link

Editable fields:
- Full Name (text input, required, max 255 chars)
- Phone (phone input, optional, max 20 chars)
- Date of Birth (date picker, optional, must be before today)
- Gender (dropdown: Male, Female, Other — optional)
- Address (multi-line text area, optional, max 500 chars)

Note: Username and Email are NOT editable (shown as read-only gray fields).

"Save Changes" primary button at bottom.
Show success toast: "Profile updated successfully."
Show validation errors inline (red text below fields).
```

### Prompt 13.3 — Change Password Screen
```
Design a change password screen for a mobile learning app.

Top: "Change Password" heading with back arrow.

Form fields:
- Current Password (password input, lock icon, show/hide toggle, required)
- New Password (password input, lock icon, show/hide toggle, required, min 8 chars)
  - Password strength indicator bar below (weak=red, medium=yellow, strong=green)
- Confirm New Password (password input, lock icon, required, must match)

"Update Password" primary button.

Error states:
- "Current password is incorrect" — red message below current password field
- "Passwords don't match" — red message below confirm field
- "Password must be at least 8 characters" — red message

Success: toast message "Password changed successfully" + navigate back.
```

---

## STEP 14: Activity Log

### Prompt 14.1 — Activity Log Screen
```
Design an activity log/history screen for a mobile learning app.

Top: "Activity Log" heading with back arrow.

Search/filter: search bar to filter by description text.
Sort: Newest first (default), Oldest first.

Timeline-style activity list (last 50 activities):
Each activity item:
- Timestamp (left column or top): "Jan 15, 2026 2:30 PM"
- Activity icon (based on subject_type):
  - Enrollment = book icon
  - Lesson = play/document icon
  - Quiz = question icon
  - Certificate = award icon
  - Payment = credit card icon
  - Review = star icon
  - Auth = lock icon
- Description text (e.g., "Completed lesson: Introduction to Python")
- Subject type badge (small: "Enrollment", "Lesson", etc.)
- Additional properties (collapsible):
  - Changed fields and values (from properties JSON)

Visual timeline: vertical line connecting activity items with dots.

Empty state: "No activity recorded yet."
```

---

## STEP 15: Video Streaming

### Prompt 15.1 — Video Player (Full Screen)
```
Design a full-screen video player for lesson videos in a mobile learning app.

Landscape orientation:
- Full-screen video with standard controls
- Play/Pause center button (large)
- Seek bar at bottom with buffered and played progress
- Current time / Total duration
- Volume slider
- Playback speed selector (0.5x, 1x, 1.25x, 1.5x, 2x)
- Fullscreen/exit fullscreen toggle
- 10-second skip forward/backward buttons
- Picture-in-Picture toggle (if supported)

Controls auto-hide after 3 seconds of inactivity.
Tap screen to show/hide controls.

Progress tracking overlay (top-right, semi-transparent):
- "Auto-saving progress..." indicator when position updates
- Resumes from video_last_position on reload

Portrait orientation:
- Video player at top (16:9 aspect ratio)
- Lesson info, content, and resources below (scrollable)

Supported formats: MP4, WebM, OGG.
Max file size indicator for uploads: 500MB.
```

---

## STEP 16: Error & Empty States

### Prompt 16.1 — Error & Loading States
```
Design a set of error and loading state screens for a mobile learning app:

1. Loading skeleton screens:
   - Course card skeleton: gray animated shimmer rectangles
   - List item skeleton: avatar circle + text lines shimmer
   - Full page loading: centered spinner + "Loading..." text

2. Network error screen:
   - Wi-Fi off illustration
   - "No Internet Connection"
   - "Check your connection and try again"
   - "Retry" button

3. Server error screen (500):
   - Broken server illustration
   - "Something went wrong"
   - "We're working on fixing this"
   - "Retry" button

4. Not found screen (404):
   - Empty box / magnifying glass illustration
   - "Page Not Found"
   - "Go Back" button

5. Unauthorized screen (401):
   - Lock illustration
   - "Session Expired"
   - "Please sign in again"
   - "Sign In" button

6. Forbidden screen (403):
   - Shield/block illustration
   - "Access Denied"
   - "You don't have permission to view this content"
   - "Go to Home" button

7. Rate limit screen (429):
   - Clock illustration
   - "Too Many Requests"
   - "Please wait a moment before trying again"
   - Auto-retry countdown timer

8. Validation error toast:
   - Red toast at top with error message
   - Dismissible after 3 seconds
   - Inline field errors: red border + red text below field
```

---

## STEP 17: Bottom Navigation & App Shell

### Prompt 17.1 — Bottom Navigation Bar
```
Design a bottom navigation bar for a mobile learning app with 5 tabs:

1. Home (house icon) — Dashboard with stats, continue learning, recent activity
2. Courses (book/grid icon) — Course catalog, browse, search, filter
3. My Learning (graduation cap icon) — Enrolled courses, progress tracking
4. Certificates (award/ribbon icon) — Earned certificates gallery
5. Profile (person icon) — Student profile, settings, logout

Active tab: blue (#1E40AF) icon + label (bold)
Inactive tabs: gray icon + label
Badge on Courses: new courses count (optional)
Notification badge: red dot on Profile or separate bell in top bar

The nav bar is fixed at bottom, visible on all main screens.
Hidden during lesson viewing and quiz taking for full immersion.
```

### Prompt 17.2 — Pull to Refresh & Infinite Scroll
```
Design pull-to-refresh and infinite scroll patterns for the app:

Pull to Refresh:
- User pulls down on any list screen
- EduPlex logo spins as refresh indicator
- "Refreshing..." text
- List updates with fresh data

Infinite Scroll:
- As user scrolls near bottom of paginated list
- Loading spinner appears at bottom
- "Loading more..." text
- New items append below
- When all items loaded: "You've reached the end" message

Applied to: Course catalog, Enrollments, Reviews, Notifications,
Payments, Certificates, Activity log (all paginated endpoints, 20 items/page).
```

---

## STEP 18: Search

### Prompt 18.1 — Global Search Screen
```
Design a search screen for a mobile learning app.

Search bar (auto-focused on entry):
- Magnifying glass icon
- "Search courses, categories..." placeholder
- Clear (X) button when text entered
- "Cancel" text button to close

Search results (as user types, debounced):

Section — Courses (primary results):
- Course cards matching search query
  (searches: course_name, course_code, description, instructor_name)
- Show: thumbnail, name, instructor, rating, price, level
- "See all X results" link if more than 5

Section — Categories:
- Matching category cards
- Show: icon, name, course count

Recent searches:
- List of recent search terms (before user types)
- Clock icon + search term
- "Clear All" button

No results state:
- "No results for '[query]'"
- "Try different keywords or browse categories"
- Suggested categories chips
```

---

## Summary — Screen Count

| Flow | Screens |
|------|---------|
| Splash & Onboarding | 2 |
| Authentication | 2 |
| Dashboard | 1 |
| Course Discovery | 3 |
| Enrollment | 2 |
| My Learning | 2 |
| Lesson Viewer | 3 |
| Quiz | 2 |
| Certificates | 3 |
| Reviews | 3 |
| Payments | 2 |
| Notifications | 1 |
| Profile | 3 |
| Activity Log | 1 |
| Video Player | 1 |
| Error States | 1 (set) |
| Navigation & Patterns | 2 |
| Search | 1 |
| **Total** | **~35 screens** |
