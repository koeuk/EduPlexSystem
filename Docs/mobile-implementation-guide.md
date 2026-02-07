# EduPlex Mobile App - Implementation Guide

Step-by-step guide for building the mobile app.

---

## Overview

### Tech Stack (Recommended)
- **React Native** or **Flutter**
- **State Management:** Redux/Zustand (React Native) or Provider/Riverpod (Flutter)
- **HTTP Client:** Axios (React Native) or Dio (Flutter)
- **Storage:** AsyncStorage/SecureStorage or SharedPreferences

### Project Structure
```
src/
├── api/              # API service functions
├── components/       # Reusable UI components
├── screens/          # Screen/Page components
├── navigation/       # Navigation configuration
├── store/            # State management
├── utils/            # Helper functions
├── hooks/            # Custom hooks (React Native)
└── constants/        # App constants, API URLs
```

---

## Phase 1: Project Setup & Authentication

### Step 1.1: Configure API Client

Create an API client with interceptors for authentication.

```javascript
// api/client.js
const API_BASE_URL = 'https://your-domain.com/api';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add token to requests
apiClient.interceptors.request.use(async (config) => {
  const token = await getStoredToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 errors (token expired)
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await clearToken();
      // Navigate to login
    }
    return Promise.reject(error);
  }
);
```

### Step 1.2: Build Authentication Screens

#### Screens to Create:
1. **Splash Screen** - Check token, redirect to Login or Home
2. **Login Screen** - Email/password form
3. **Register Screen** - Registration form with profile picture
4. **Forgot Password Screen** - Email input
5. **Reset Password Screen** - New password form (deep link from email)

#### API Functions:
```javascript
// api/auth.js
export const authAPI = {
  login: (email, password) =>
    apiClient.post('/auth/login', { email, password }),

  register: (formData) =>
    apiClient.post('/auth/register', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),

  forgotPassword: (email) =>
    apiClient.post('/auth/forgot-password', { email }),

  resetPassword: (data) =>
    apiClient.post('/auth/reset-password', data),

  getProfile: () =>
    apiClient.get('/auth/profile'),

  updateProfile: (formData) =>
    apiClient.put('/auth/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),

  changePassword: (data) =>
    apiClient.put('/auth/change-password', data),

  logout: () =>
    apiClient.post('/auth/logout'),
};
```

#### Implementation Flow:

```
┌─────────────┐
│   Splash    │
└──────┬──────┘
       │
       ▼
  Has Token?
   │      │
  Yes     No
   │      │
   ▼      ▼
┌──────┐ ┌──────┐
│ Home │ │Login │
└──────┘ └───┬──┘
             │
    ┌────────┴────────┐
    ▼                 ▼
┌────────┐      ┌──────────┐
│Register│      │Forgot Pwd│
└────────┘      └──────────┘
```

#### Login Screen Implementation:
```javascript
// screens/LoginScreen.js
const LoginScreen = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleLogin = async () => {
    setLoading(true);
    setError('');

    try {
      const response = await authAPI.login(email, password);

      if (response.data.success) {
        // Store token securely
        await storeToken(response.data.data.token);
        // Store user data
        await storeUser(response.data.data.user);
        // Navigate to home
        navigation.replace('Home');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    // Your UI implementation
  );
};
```

#### Register Screen - Handle Profile Picture:
```javascript
const handleRegister = async () => {
  const formData = new FormData();
  formData.append('username', username);
  formData.append('email', email);
  formData.append('password', password);
  formData.append('password_confirmation', passwordConfirm);
  formData.append('full_name', fullName);

  if (profileImage) {
    formData.append('profile_picture', {
      uri: profileImage.uri,
      type: 'image/jpeg',
      name: 'profile.jpg',
    });
  }

  const response = await authAPI.register(formData);
};
```

### Step 1.3: Token Storage

```javascript
// utils/storage.js
import * as SecureStore from 'expo-secure-store'; // or AsyncStorage

export const storeToken = async (token) => {
  await SecureStore.setItemAsync('auth_token', token);
};

export const getStoredToken = async () => {
  return await SecureStore.getItemAsync('auth_token');
};

export const clearToken = async () => {
  await SecureStore.deleteItemAsync('auth_token');
};
```

---

## Phase 2: Home & Course Discovery

### Step 2.1: Build Home Screen

#### Components:
1. **Header** - User avatar, notifications badge
2. **Search Bar** - Course search
3. **Categories Horizontal List** - Scrollable category chips
4. **Featured Courses** - Horizontal carousel
5. **Continue Learning** - In-progress courses
6. **All Courses** - Vertical list with filters

#### API Functions:
```javascript
// api/courses.js
export const courseAPI = {
  getCategories: () =>
    apiClient.get('/categories'),

  getCourses: (params = {}) =>
    apiClient.get('/courses', { params }),

  getCoursesByCategory: (categoryId, params = {}) =>
    apiClient.get(`/categories/${categoryId}/courses`, { params }),

  getCourseDetails: (courseId) =>
    apiClient.get(`/courses/${courseId}`),

  searchCourses: (query) =>
    apiClient.get('/courses', {
      params: { 'filter[search]': query }
    }),
};

// api/dashboard.js
export const dashboardAPI = {
  getStats: () =>
    apiClient.get('/dashboard/stats'),

  getContinueLearning: () =>
    apiClient.get('/dashboard/continue-learning'),

  getRecentActivity: () =>
    apiClient.get('/dashboard/recent-activity'),
};
```

#### Home Screen Layout:
```
┌────────────────────────────┐
│  👤 Hi, John      🔔 (3)   │  <- Header
├────────────────────────────┤
│  🔍 Search courses...      │  <- Search Bar
├────────────────────────────┤
│ [All] [Web] [Mobile] [→]   │  <- Categories
├────────────────────────────┤
│  Continue Learning         │
│  ┌─────┐ ┌─────┐          │
│  │ 65% │ │ 30% │  →       │  <- Horizontal scroll
│  └─────┘ └─────┘          │
├────────────────────────────┤
│  Featured Courses          │
│  ┌─────┐ ┌─────┐          │
│  │     │ │     │  →       │
│  └─────┘ └─────┘          │
├────────────────────────────┤
│  All Courses    [Filter]   │
│  ┌────────────────────┐   │
│  │ Course 1           │   │
│  ├────────────────────┤   │
│  │ Course 2           │   │
│  └────────────────────┘   │
└────────────────────────────┘
```

### Step 2.2: Course List with Filters

#### Filter Component:
```javascript
const CourseFilters = ({ onApply }) => {
  const [level, setLevel] = useState('');
  const [pricingType, setPricingType] = useState('');
  const [priceRange, setPriceRange] = useState({ min: '', max: '' });
  const [sortBy, setSortBy] = useState('-created_at');

  const applyFilters = () => {
    const params = {};

    if (level) params['filter[level]'] = level;
    if (pricingType) params['filter[pricing_type]'] = pricingType;
    if (priceRange.min || priceRange.max) {
      params['filter[price_range]'] = `${priceRange.min},${priceRange.max}`;
    }
    params['sort'] = sortBy;

    onApply(params);
  };
};
```

#### Pagination Implementation:
```javascript
const CourseList = () => {
  const [courses, setCourses] = useState([]);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [loading, setLoading] = useState(false);

  const loadCourses = async (pageNum = 1, filters = {}) => {
    setLoading(true);

    const response = await courseAPI.getCourses({
      page: pageNum,
      per_page: 20,
      ...filters,
    });

    const newCourses = response.data.data;
    const pagination = response.data.pagination;

    if (pageNum === 1) {
      setCourses(newCourses);
    } else {
      setCourses(prev => [...prev, ...newCourses]);
    }

    setHasMore(pagination.current_page < pagination.total_pages);
    setLoading(false);
  };

  const loadMore = () => {
    if (!loading && hasMore) {
      setPage(prev => prev + 1);
      loadCourses(page + 1);
    }
  };
};
```

### Step 2.3: Course Details Screen

#### Components:
1. **Course Header** - Image, title, rating, price
2. **Course Info** - Level, duration, instructor
3. **Description** - Expandable text
4. **Curriculum** - Modules & lessons list
5. **Reviews** - Rating summary, review list
6. **Action Button** - Enroll / Continue / Completed

#### Implementation:
```javascript
const CourseDetailsScreen = ({ route }) => {
  const { courseId } = route.params;
  const [course, setCourse] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadCourse();
  }, [courseId]);

  const loadCourse = async () => {
    const response = await courseAPI.getCourseDetails(courseId);
    setCourse(response.data.data);
    setLoading(false);
  };

  const handleEnroll = async () => {
    if (course.is_free || course.pricing_type === 'free') {
      // Direct enrollment
      await enrollmentAPI.enroll(courseId);
      // Refresh course data
      loadCourse();
    } else {
      // Navigate to payment
      navigation.navigate('Payment', { course });
    }
  };

  const renderActionButton = () => {
    if (course.is_enrolled) {
      if (course.enrollment.status === 'completed') {
        return <Button>View Certificate</Button>;
      }
      return <Button onPress={goToLearning}>Continue Learning</Button>;
    }

    return (
      <Button onPress={handleEnroll}>
        {course.is_free ? 'Enroll Free' : `Enroll - $${course.price}`}
      </Button>
    );
  };
};
```

---

## Phase 3: Enrollment & Learning

### Step 3.1: Enrollment Flow

```javascript
// api/enrollments.js
export const enrollmentAPI = {
  getEnrollments: (params = {}) =>
    apiClient.get('/enrollments', { params }),

  enroll: (courseId) =>
    apiClient.post('/enrollments', { course_id: courseId }),

  getEnrollmentDetails: (enrollmentId) =>
    apiClient.get(`/enrollments/${enrollmentId}`),

  dropEnrollment: (enrollmentId) =>
    apiClient.delete(`/enrollments/${enrollmentId}`),
};
```

#### Enrollment Flow:
```
┌─────────────────┐
│ Course Details  │
└────────┬────────┘
         │
         ▼
    Is Free?
    │      │
   Yes     No
    │      │
    ▼      ▼
┌──────┐ ┌─────────┐
│Enroll│ │ Payment │
└──┬───┘ └────┬────┘
   │          │
   └────┬─────┘
        ▼
   ┌─────────┐
   │ Success │
   └────┬────┘
        │
        ▼
   ┌──────────┐
   │ Learning │
   └──────────┘
```

### Step 3.2: My Courses Screen

#### Tabs:
1. **In Progress** - Active enrollments
2. **Completed** - Finished courses
3. **All** - All enrollments

```javascript
const MyCoursesScreen = () => {
  const [activeTab, setActiveTab] = useState('in_progress');
  const [enrollments, setEnrollments] = useState([]);

  const loadEnrollments = async () => {
    const params = {};

    if (activeTab === 'in_progress') {
      params['filter[status]'] = 'active';
    } else if (activeTab === 'completed') {
      params['filter[status]'] = 'completed';
    }

    const response = await enrollmentAPI.getEnrollments(params);
    setEnrollments(response.data.data);
  };

  return (
    <View>
      <TabBar
        tabs={['In Progress', 'Completed', 'All']}
        activeTab={activeTab}
        onTabChange={setActiveTab}
      />

      <FlatList
        data={enrollments}
        renderItem={({ item }) => (
          <EnrollmentCard
            enrollment={item}
            onPress={() => navigation.navigate('Learning', {
              enrollmentId: item.id
            })}
          />
        )}
      />
    </View>
  );
};
```

### Step 3.3: Learning Screen (Course Player)

#### Components:
1. **Course Header** - Title, progress bar
2. **Module Accordion** - Expandable module list
3. **Lesson List** - Lessons with status indicators
4. **Current Lesson** - Video player / Text content / Quiz

#### API Functions:
```javascript
// api/lessons.js
export const lessonAPI = {
  getLesson: (lessonId) =>
    apiClient.get(`/lessons/${lessonId}`),

  updateProgress: (lessonId, data) =>
    apiClient.post(`/lessons/${lessonId}/progress`, data),

  getProgress: (lessonId) =>
    apiClient.get(`/lessons/${lessonId}/progress`),
};

// api/progress.js
export const progressAPI = {
  getCourseProgress: (courseId) =>
    apiClient.get(`/progress/courses/${courseId}`),

  updateLessonProgress: (lessonId, data) =>
    apiClient.put(`/progress/lessons/${lessonId}`, data),
};
```

#### Learning Screen Layout:
```
┌────────────────────────────┐
│ ← Course Name       65%    │  <- Header with progress
├────────────────────────────┤
│                            │
│   ┌──────────────────┐     │
│   │                  │     │
│   │   Video Player   │     │  <- Content Area
│   │                  │     │
│   └──────────────────┘     │
│                            │
├────────────────────────────┤
│ ▼ Module 1: Getting Started│  <- Module accordion
│   ✓ Lesson 1: Intro        │
│   ● Lesson 2: Setup  ←     │  <- Current lesson
│   ○ Lesson 3: Basics       │
│ ▶ Module 2: Advanced       │
│ ▶ Module 3: Projects       │
└────────────────────────────┘
```

### Step 3.4: Video Player Implementation

```javascript
const VideoLesson = ({ lesson, onComplete }) => {
  const [position, setPosition] = useState(0);
  const [duration, setDuration] = useState(0);
  const videoRef = useRef(null);

  // Resume from last position
  useEffect(() => {
    loadProgress();
  }, [lesson.id]);

  const loadProgress = async () => {
    const response = await lessonAPI.getProgress(lesson.id);
    if (response.data.data?.video_last_position) {
      setPosition(response.data.data.video_last_position);
      videoRef.current?.seek(response.data.data.video_last_position);
    }
  };

  // Save progress periodically
  const saveProgress = async (currentPosition) => {
    const progressPercentage = (currentPosition / duration) * 100;

    await lessonAPI.updateProgress(lesson.id, {
      status: progressPercentage >= 90 ? 'completed' : 'in_progress',
      progress_percentage: Math.min(progressPercentage, 100),
      video_last_position: Math.floor(currentPosition),
      time_spent_minutes: Math.floor(currentPosition / 60),
    });

    if (progressPercentage >= 90) {
      onComplete();
    }
  };

  // Save every 10 seconds
  useEffect(() => {
    const interval = setInterval(() => {
      saveProgress(position);
    }, 10000);

    return () => clearInterval(interval);
  }, [position]);

  return (
    <Video
      ref={videoRef}
      source={{ uri: lesson.video_url }}
      onProgress={({ currentTime }) => setPosition(currentTime)}
      onLoad={({ duration }) => setDuration(duration)}
    />
  );
};
```

### Step 3.5: Text Lesson Implementation

```javascript
const TextLesson = ({ lesson, onComplete }) => {
  const [scrollPosition, setScrollPosition] = useState(0);
  const [contentHeight, setContentHeight] = useState(0);

  const handleScroll = (event) => {
    const { contentOffset, contentSize, layoutMeasurement } = event.nativeEvent;
    setScrollPosition(contentOffset.y);

    const scrollPercentage =
      (contentOffset.y / (contentSize.height - layoutMeasurement.height)) * 100;

    if (scrollPercentage >= 90) {
      markAsComplete();
    }
  };

  const markAsComplete = async () => {
    await lessonAPI.updateProgress(lesson.id, {
      status: 'completed',
      progress_percentage: 100,
      scroll_position: scrollPosition,
    });
    onComplete();
  };

  return (
    <ScrollView onScroll={handleScroll} scrollEventThrottle={16}>
      <RenderHTML source={{ html: lesson.content }} />

      <Button onPress={markAsComplete}>
        Mark as Complete
      </Button>
    </ScrollView>
  );
};
```

---

## Phase 4: Quizzes

### Step 4.1: Quiz API Functions

```javascript
// api/quizzes.js
export const quizAPI = {
  getQuiz: (quizId) =>
    apiClient.get(`/quizzes/${quizId}`),

  startAttempt: (quizId) =>
    apiClient.post(`/quizzes/${quizId}/attempts`),

  submitAttempt: (attemptId, answers) =>
    apiClient.put(`/quizzes/attempts/${attemptId}`, { answers }),

  getAttempt: (attemptId) =>
    apiClient.get(`/quizzes/attempts/${attemptId}`),

  getAttemptHistory: (quizId) =>
    apiClient.get(`/quizzes/${quizId}/attempts`),
};
```

### Step 4.2: Quiz Flow

```
┌─────────────┐
│ Quiz Intro  │  <- Title, instructions, time limit
└──────┬──────┘
       │
       ▼
┌─────────────┐
│Start Attempt│
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Questions  │  <- One at a time or all at once
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Submit    │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Results   │  <- Score, pass/fail, correct answers
└─────────────┘
```

### Step 4.3: Quiz Screen Implementation

```javascript
const QuizScreen = ({ route }) => {
  const { quizId, lessonId } = route.params;
  const [quiz, setQuiz] = useState(null);
  const [attempt, setAttempt] = useState(null);
  const [answers, setAnswers] = useState({});
  const [currentQuestion, setCurrentQuestion] = useState(0);
  const [timeLeft, setTimeLeft] = useState(null);
  const [submitted, setSubmitted] = useState(false);
  const [results, setResults] = useState(null);

  // Load quiz
  useEffect(() => {
    loadQuiz();
  }, [quizId]);

  // Timer
  useEffect(() => {
    if (attempt && timeLeft > 0) {
      const timer = setInterval(() => {
        setTimeLeft(prev => {
          if (prev <= 1) {
            handleSubmit(); // Auto-submit when time runs out
            return 0;
          }
          return prev - 1;
        });
      }, 1000);

      return () => clearInterval(timer);
    }
  }, [attempt, timeLeft]);

  const loadQuiz = async () => {
    const response = await quizAPI.getQuiz(quizId);
    setQuiz(response.data.data);
  };

  const startQuiz = async () => {
    const response = await quizAPI.startAttempt(quizId);
    setAttempt(response.data.data);
    setTimeLeft(quiz.time_limit_minutes * 60);
  };

  const selectAnswer = (questionId, optionId) => {
    setAnswers(prev => ({
      ...prev,
      [questionId]: optionId,
    }));
  };

  const handleSubmit = async () => {
    const formattedAnswers = Object.entries(answers).map(
      ([questionId, optionId]) => ({
        question_id: parseInt(questionId),
        selected_option_id: optionId,
      })
    );

    const response = await quizAPI.submitAttempt(attempt.id, formattedAnswers);
    setResults(response.data.data);
    setSubmitted(true);
  };

  // Render quiz intro
  if (!attempt) {
    return (
      <View>
        <Text>{quiz?.quiz_title}</Text>
        <Text>{quiz?.instructions}</Text>
        <Text>Time Limit: {quiz?.time_limit_minutes} minutes</Text>
        <Text>Passing Score: {quiz?.passing_score}%</Text>
        <Button onPress={startQuiz}>Start Quiz</Button>
      </View>
    );
  }

  // Render results
  if (submitted) {
    return (
      <View>
        <Text>{results.passed ? 'Passed!' : 'Failed'}</Text>
        <Text>Score: {results.score_percentage}%</Text>
        <Text>Points: {results.total_points}/{results.max_points}</Text>
        <Button onPress={() => navigation.goBack()}>
          Back to Lesson
        </Button>
      </View>
    );
  }

  // Render questions
  const question = quiz.questions[currentQuestion];

  return (
    <View>
      <Text>Time Left: {formatTime(timeLeft)}</Text>
      <Text>Question {currentQuestion + 1} of {quiz.questions.length}</Text>

      <Text>{question.question_text}</Text>

      {question.options.map(option => (
        <TouchableOpacity
          key={option.id}
          onPress={() => selectAnswer(question.id, option.id)}
          style={[
            styles.option,
            answers[question.id] === option.id && styles.selected,
          ]}
        >
          <Text>{option.option_text}</Text>
        </TouchableOpacity>
      ))}

      <View style={styles.navigation}>
        {currentQuestion > 0 && (
          <Button onPress={() => setCurrentQuestion(prev => prev - 1)}>
            Previous
          </Button>
        )}

        {currentQuestion < quiz.questions.length - 1 ? (
          <Button onPress={() => setCurrentQuestion(prev => prev + 1)}>
            Next
          </Button>
        ) : (
          <Button onPress={handleSubmit}>Submit</Button>
        )}
      </View>
    </View>
  );
};
```

---

## Phase 5: Payments

### Step 5.1: Payment API Functions

```javascript
// api/payments.js
export const paymentAPI = {
  getPayments: (params = {}) =>
    apiClient.get('/payments', { params }),

  createPayment: (data) =>
    apiClient.post('/payments', data),

  getPayment: (paymentId) =>
    apiClient.get(`/payments/${paymentId}`),
};
```

### Step 5.2: Payment Flow

```
┌────────────────┐
│ Course Details │
└───────┬────────┘
        │
        ▼
┌────────────────┐
│ Payment Screen │
│                │
│ Course: $99.99 │
│                │
│ Payment Method │
│ ○ Credit Card  │
│ ○ PayPal       │
│ ○ Bank Transfer│
└───────┬────────┘
        │
        ▼
┌────────────────┐
│ Process Payment│  <- Integrate with payment gateway
└───────┬────────┘
        │
        ▼
┌────────────────┐
│    Success     │
│                │
│ Course Enrolled│
└────────────────┘
```

### Step 5.3: Payment Screen

```javascript
const PaymentScreen = ({ route }) => {
  const { course } = route.params;
  const [paymentMethod, setPaymentMethod] = useState('credit_card');
  const [processing, setProcessing] = useState(false);

  const handlePayment = async () => {
    setProcessing(true);

    try {
      // 1. Create payment record
      const response = await paymentAPI.createPayment({
        course_id: course.id,
        payment_method: paymentMethod,
        amount: course.price,
      });

      // 2. Enrollment is automatically created

      // 3. Navigate to success
      navigation.replace('PaymentSuccess', {
        course,
        payment: response.data.data,
      });

    } catch (error) {
      Alert.alert('Payment Failed', error.response?.data?.message);
    } finally {
      setProcessing(false);
    }
  };

  return (
    <View>
      <CourseCard course={course} />

      <Text>Total: ${course.price}</Text>

      <Text>Select Payment Method:</Text>

      <RadioGroup
        options={[
          { value: 'credit_card', label: 'Credit Card' },
          { value: 'debit_card', label: 'Debit Card' },
          { value: 'paypal', label: 'PayPal' },
          { value: 'bank_transfer', label: 'Bank Transfer' },
        ]}
        selected={paymentMethod}
        onSelect={setPaymentMethod}
      />

      <Button
        onPress={handlePayment}
        loading={processing}
      >
        Pay ${course.price}
      </Button>
    </View>
  );
};
```

---

## Phase 6: Certificates

### Step 6.1: Certificate API Functions

```javascript
// api/certificates.js
export const certificateAPI = {
  getCertificates: () =>
    apiClient.get('/certificates'),

  getCertificate: (certificateId) =>
    apiClient.get(`/certificates/${certificateId}`),

  downloadCertificate: (certificateId) =>
    apiClient.get(`/certificates/${certificateId}/download`, {
      responseType: 'blob',
    }),

  verifyCertificate: (code) =>
    apiClient.get(`/certificates/verify/${code}`),
};
```

### Step 6.2: Certificates Screen

```javascript
const CertificatesScreen = () => {
  const [certificates, setCertificates] = useState([]);

  useEffect(() => {
    loadCertificates();
  }, []);

  const loadCertificates = async () => {
    const response = await certificateAPI.getCertificates();
    setCertificates(response.data.data);
  };

  const downloadCertificate = async (certificate) => {
    try {
      // Download PDF
      const response = await certificateAPI.downloadCertificate(certificate.id);

      // Save to device
      const path = `${FileSystem.documentDirectory}${certificate.certificate_code}.pdf`;
      await FileSystem.writeAsStringAsync(path, response.data, {
        encoding: FileSystem.EncodingType.Base64,
      });

      // Share or open
      await Sharing.shareAsync(path);

    } catch (error) {
      Alert.alert('Download Failed', error.message);
    }
  };

  return (
    <FlatList
      data={certificates}
      renderItem={({ item }) => (
        <CertificateCard
          certificate={item}
          onDownload={() => downloadCertificate(item)}
          onShare={() => shareCertificate(item)}
        />
      )}
    />
  );
};
```

---

## Phase 7: Profile & Settings

### Step 7.1: Profile Screen

```javascript
const ProfileScreen = () => {
  const [user, setUser] = useState(null);
  const [editing, setEditing] = useState(false);

  useEffect(() => {
    loadProfile();
  }, []);

  const loadProfile = async () => {
    const response = await authAPI.getProfile();
    setUser(response.data.data);
  };

  const updateProfile = async (data) => {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        formData.append(key, value);
      }
    });

    await authAPI.updateProfile(formData);
    loadProfile();
    setEditing(false);
  };

  return (
    <View>
      <Avatar source={{ uri: user?.image_url }} />
      <Text>{user?.full_name}</Text>
      <Text>{user?.email}</Text>

      <Button onPress={() => setEditing(true)}>Edit Profile</Button>
      <Button onPress={() => navigation.navigate('ChangePassword')}>
        Change Password
      </Button>
      <Button onPress={handleLogout}>Logout</Button>
    </View>
  );
};
```

---

## Phase 8: Notifications

### Step 8.1: Notification API Functions

```javascript
// api/notifications.js
export const notificationAPI = {
  getNotifications: (params = {}) =>
    apiClient.get('/notifications', { params }),

  markAsRead: (notificationId) =>
    apiClient.put(`/notifications/${notificationId}/read`),

  markAllAsRead: () =>
    apiClient.put('/notifications/read-all'),

  deleteNotification: (notificationId) =>
    apiClient.delete(`/notifications/${notificationId}`),
};
```

### Step 8.2: Notifications Screen

```javascript
const NotificationsScreen = () => {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);

  const loadNotifications = async () => {
    const response = await notificationAPI.getNotifications({
      sort: '-created_at',
    });
    setNotifications(response.data.data);
    setUnreadCount(response.data.data.filter(n => !n.is_read).length);
  };

  const handleNotificationPress = async (notification) => {
    // Mark as read
    if (!notification.is_read) {
      await notificationAPI.markAsRead(notification.id);
    }

    // Navigate based on type
    switch (notification.type) {
      case 'enrollment':
        navigation.navigate('CourseDetails', {
          courseId: notification.related_id
        });
        break;
      case 'completion':
        navigation.navigate('Certificates');
        break;
      // ... handle other types
    }
  };

  return (
    <View>
      <Button onPress={markAllAsRead}>Mark All as Read</Button>

      <FlatList
        data={notifications}
        renderItem={({ item }) => (
          <NotificationItem
            notification={item}
            onPress={() => handleNotificationPress(item)}
            onDelete={() => deleteNotification(item.id)}
          />
        )}
      />
    </View>
  );
};
```

---

## Phase 9: Dashboard

### Step 9.1: Dashboard Screen

```javascript
const DashboardScreen = () => {
  const [stats, setStats] = useState(null);
  const [continueLearning, setContinueLearning] = useState([]);
  const [recentActivity, setRecentActivity] = useState([]);

  useEffect(() => {
    loadDashboard();
  }, []);

  const loadDashboard = async () => {
    const [statsRes, continueRes, activityRes] = await Promise.all([
      dashboardAPI.getStats(),
      dashboardAPI.getContinueLearning(),
      dashboardAPI.getRecentActivity(),
    ]);

    setStats(statsRes.data.data);
    setContinueLearning(continueRes.data.data);
    setRecentActivity(activityRes.data.data);
  };

  return (
    <ScrollView>
      {/* Stats Cards */}
      <View style={styles.statsGrid}>
        <StatCard
          title="Enrolled"
          value={stats?.enrolled_courses}
          icon="book"
        />
        <StatCard
          title="Completed"
          value={stats?.completed_courses}
          icon="check-circle"
        />
        <StatCard
          title="Certificates"
          value={stats?.certificates}
          icon="award"
        />
        <StatCard
          title="Learning Time"
          value={`${stats?.total_learning_time_hours}h`}
          icon="clock"
        />
      </View>

      {/* Continue Learning */}
      <Section title="Continue Learning">
        <HorizontalList
          data={continueLearning}
          renderItem={({ item }) => (
            <ContinueLearningCard
              enrollment={item}
              onPress={() => navigation.navigate('Learning', {
                enrollmentId: item.enrollment_id,
                lessonId: item.next_lesson?.id,
              })}
            />
          )}
        />
      </Section>

      {/* Recent Activity */}
      <Section title="Recent Activity">
        {recentActivity.map(activity => (
          <ActivityItem key={activity.id} activity={activity} />
        ))}
      </Section>
    </ScrollView>
  );
};
```

---

## Navigation Structure

```javascript
// navigation/AppNavigator.js
const Stack = createStackNavigator();
const Tab = createBottomTabNavigator();

const AuthStack = () => (
  <Stack.Navigator screenOptions={{ headerShown: false }}>
    <Stack.Screen name="Login" component={LoginScreen} />
    <Stack.Screen name="Register" component={RegisterScreen} />
    <Stack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
    <Stack.Screen name="ResetPassword" component={ResetPasswordScreen} />
  </Stack.Navigator>
);

const MainTabs = () => (
  <Tab.Navigator>
    <Tab.Screen name="Home" component={HomeScreen} />
    <Tab.Screen name="MyCourses" component={MyCoursesScreen} />
    <Tab.Screen name="Dashboard" component={DashboardScreen} />
    <Tab.Screen name="Profile" component={ProfileScreen} />
  </Tab.Navigator>
);

const MainStack = () => (
  <Stack.Navigator>
    <Stack.Screen name="MainTabs" component={MainTabs} />
    <Stack.Screen name="CourseDetails" component={CourseDetailsScreen} />
    <Stack.Screen name="Learning" component={LearningScreen} />
    <Stack.Screen name="Quiz" component={QuizScreen} />
    <Stack.Screen name="Payment" component={PaymentScreen} />
    <Stack.Screen name="Certificates" component={CertificatesScreen} />
    <Stack.Screen name="Notifications" component={NotificationsScreen} />
  </Stack.Navigator>
);

const AppNavigator = () => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    const token = await getStoredToken();
    setIsAuthenticated(!!token);
    setIsLoading(false);
  };

  if (isLoading) {
    return <SplashScreen />;
  }

  return (
    <NavigationContainer>
      {isAuthenticated ? <MainStack /> : <AuthStack />}
    </NavigationContainer>
  );
};
```

---

## Implementation Checklist

### Phase 1: Authentication
- [ ] API client setup with interceptors
- [ ] Token storage (secure)
- [ ] Login screen
- [ ] Register screen with image upload
- [ ] Forgot password screen
- [ ] Profile screen
- [ ] Change password screen
- [ ] Logout functionality

### Phase 2: Course Discovery
- [ ] Home screen layout
- [ ] Categories list
- [ ] Course list with pagination
- [ ] Course filters (level, price, category)
- [ ] Course search
- [ ] Course details screen

### Phase 3: Enrollment & Learning
- [ ] Enrollment flow (free/paid)
- [ ] My courses screen with tabs
- [ ] Learning screen with modules/lessons
- [ ] Video player with progress tracking
- [ ] Text lesson with scroll tracking
- [ ] Progress auto-save

### Phase 4: Quizzes
- [ ] Quiz intro screen
- [ ] Quiz questions UI
- [ ] Timer implementation
- [ ] Answer selection
- [ ] Quiz submission
- [ ] Results display

### Phase 5: Payments
- [ ] Payment screen
- [ ] Payment method selection
- [ ] Payment processing
- [ ] Success/failure handling

### Phase 6: Certificates
- [ ] Certificates list
- [ ] Certificate download (PDF)
- [ ] Certificate sharing

### Phase 7: Profile
- [ ] View profile
- [ ] Edit profile
- [ ] Update profile picture

### Phase 8: Notifications
- [ ] Notifications list
- [ ] Mark as read
- [ ] Notification navigation

### Phase 9: Dashboard
- [ ] Stats display
- [ ] Continue learning section
- [ ] Recent activity

---

## Tips

### Error Handling
```javascript
const handleAPIError = (error) => {
  if (error.response) {
    // Server responded with error
    const { status, data } = error.response;

    switch (status) {
      case 401:
        // Redirect to login
        break;
      case 422:
        // Validation errors
        return data.errors;
      case 403:
        Alert.alert('Access Denied', data.message);
        break;
      default:
        Alert.alert('Error', data.message || 'Something went wrong');
    }
  } else {
    // Network error
    Alert.alert('Network Error', 'Please check your connection');
  }
};
```

### Loading States
```javascript
const useAPI = (apiFunction) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const execute = async (...args) => {
    setLoading(true);
    setError(null);

    try {
      const response = await apiFunction(...args);
      setData(response.data.data);
      return response.data.data;
    } catch (err) {
      setError(err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  return { data, loading, error, execute };
};
```

### Offline Support
```javascript
// Cache important data
const cacheData = async (key, data) => {
  await AsyncStorage.setItem(key, JSON.stringify(data));
};

const getCachedData = async (key) => {
  const data = await AsyncStorage.getItem(key);
  return data ? JSON.parse(data) : null;
};

// Use cached data when offline
const loadCourses = async () => {
  try {
    const response = await courseAPI.getCourses();
    await cacheData('courses', response.data.data);
    return response.data.data;
  } catch (error) {
    // Return cached data if offline
    return await getCachedData('courses');
  }
};
```
