# Laravel Brive Project Flowchart

## 🏗️ Project Architecture Overview

```mermaid
graph TB
    %% User Authentication Flow
    A[User Access] --> B{Authenticated?}
    B -->|No| C[Login/Register]
    B -->|Yes| D[Dashboard]
    
    C --> C1[Email/Password Login]
    C --> C2[Google OAuth]
    C1 --> D
    C2 --> D
    
    %% Main Dashboard Flow
    D --> E{User Role?}
    E -->|Admin| F[Admin Dashboard]
    E -->|Student| G[Student Dashboard]
    E -->|Instructor| H[Instructor Dashboard]
    
    %% Admin Flow
    F --> F1[User Management]
    F --> F2[Course Management]
    F --> F3[Quiz Management]
    F --> F4[System Settings]
    F --> F5[Backup Management]
    
    %% Student Flow
    G --> G1[My Courses]
    G --> G2[Course Exploration]
    G --> G3[Wishlist]
    G --> G4[Quiz Attempts]
    
    %% Instructor Flow
    H --> H1[Create Courses]
    H --> H2[Manage Content]
    H --> H3[Student Progress]
    H --> H4[Quiz Creation]
```

## 📚 Course Management Flow

```mermaid
graph TB
    %% Course Creation Flow
    A[Course Creation] --> B[Course Form]
    B --> C[Upload Image]
    B --> D[Set Category]
    B --> E[Add Sections]
    E --> F[Add Lectures]
    F --> G[Upload Videos]
    F --> H[Set Duration]
    
    %% Course Structure
    I[Course] --> J[Sections]
    J --> K[Lectures]
    J --> L[Quizzes]
    
    %% Enrollment Flow
    M[Student Browse] --> N[Course Details]
    N --> O{Free Course?}
    O -->|Yes| P[Direct Enrollment]
    O -->|No| Q[Payment Process]
    Q --> P
    P --> R[Access Course Content]
    
    %% Learning Flow
    R --> S[Watch Lectures]
    S --> T[Mark as Complete]
    T --> U[Progress Tracking]
    U --> V[Quiz Access]
    V --> W[Certificate Generation]
```

## 🧩 Quiz System Flow

```mermaid
graph TB
    %% Quiz Creation
    A[Quiz Creation] --> B[Set Quiz Details]
    B --> C[Add Questions]
    C --> D[Multiple Choice]
    C --> E[True/False]
    C --> F[Essay Questions]
    
    %% Quiz Taking Flow
    G[Student Access Quiz] --> H{Prerequisites Met?}
    H -->|No| I[Show Requirements]
    H -->|Yes| J[Start Quiz]
    J --> K[Answer Questions]
    K --> L[Submit Quiz]
    L --> M[Calculate Score]
    M --> N[Store Attempt]
    N --> O[Show Results]
    O --> P[Update Progress]
```

## 🔐 Authentication & Authorization Flow

```mermaid
graph TB
    %% Authentication
    A[User Request] --> B{Authenticated?}
    B -->|No| C[Redirect to Login]
    B -->|Yes| D[Check Permissions]
    
    %% Permission Check
    D --> E{Has Permission?}
    E -->|No| F[Access Denied]
    E -->|Yes| G[Allow Access]
    
    %% Role-based Access
    G --> H{User Role}
    H -->|Admin| I[Full Access]
    H -->|Instructor| J[Course Management]
    H -->|Student| K[Learning Access]
```

## 🗄️ Database Relationships

```mermaid
erDiagram
    USER {
        int id PK
        string name
        string email
        timestamp created_at
    }
    
    COURSE {
        int id PK
        string title
        string slug
        text description
        string image
        int user_id FK
        int category_id FK
        decimal price
        string status
    }
    
    ENROLLMENT {
        int id PK
        int user_id FK
        int course_id FK
        string enrollment_status
        datetime enrollment_date
        int progress_percentage
    }
    
    SECTION {
        int id PK
        string title
        text description
        int course_id FK
        int order
    }
    
    LECTURE {
        int id PK
        string title
        string video_url
        int duration
        int section_id FK
        int course_id FK
    }
    
    QUIZ {
        int id PK
        string title
        int time_limit
        int passing_score
        int course_id FK
        string quiz_type
    }
    
    QUESTION {
        int id PK
        text question_title_en
        text question_title_ar
        int quiz_id FK
        int points
    }
    
    QUIZ_ATTEMPT {
        int id PK
        int user_id FK
        int quiz_id FK
        json answers
        int score
        datetime completed_at
    }
    
    USER ||--o{ ENROLLMENT : "enrolls in"
    USER ||--o{ COURSE : "creates"
    USER ||--o{ QUIZ_ATTEMPT : "attempts"
    COURSE ||--o{ ENROLLMENT : "has"
    COURSE ||--o{ SECTION : "contains"
    COURSE ||--o{ QUIZ : "includes"
    SECTION ||--o{ LECTURE : "has"
    QUIZ ||--o{ QUESTION : "contains"
    QUIZ ||--o{ QUIZ_ATTEMPT : "attempted in"
```

## 🎨 Frontend Component Structure

```mermaid
graph TB
    %% Main Layout
    A[AuthenticatedLayout] --> B[Header]
    A --> C[Sidebar]
    A --> D[Main Content]
    
    %% Course Learning Interface
    E[Learn.vue] --> F[Course Navigation]
    E --> G[Content Display]
    E --> H[Progress Tracking]
    
    F --> F1[Course Overview]
    F --> F2[Section List]
    F --> F3[Lecture List]
    
    G --> G1[Video Player]
    G --> G2[Lecture Content]
    G --> G3[Quiz Interface]
    
    %% Dashboard Components
    I[Dashboard] --> J[Stats Cards]
    I --> K[Recent Activity]
    I --> L[Quick Actions]
    
    %% Course Management
    M[Course Management] --> N[Course List]
    M --> O[Course Editor]
    M --> P[Content Manager]
```

## 🔄 API Endpoints Flow

```mermaid
graph LR
    %% Public Routes
    A[Public Routes] --> A1[/]
    A --> A2[/courses/explore]
    A --> A3[/auth/google]
    
    %% Protected Routes
    B[Protected Routes] --> B1[/dashboard]
    B --> B2[/courses/*]
    B --> B3[/my-courses]
    B --> B4[/profile]
    
    %% Admin Routes
    C[Admin Routes] --> C1[/user/*]
    C --> C2[/role/*]
    C --> C3[/permission/*]
    C --> C4[/courses/create]
    
    %% Course Routes
    D[Course Routes] --> D1[GET /courses/{id}/details]
    D --> D2[POST /courses/{id}/enroll]
    D --> D3[GET /courses/{id}/learn]
    D --> D4[GET /courses/{id}/player]
```

## 🚀 Deployment & Git Workflow

```mermaid
graph TB
    %% Development Flow
    A[Local Development] --> B[Feature Branch]
    B --> C[Code Changes]
    C --> D[Testing]
    D --> E[Commit & Push]
    
    %% Git Branches
    E --> F{Branch Type}
    F -->|Feature| G[feature/*]
    F -->|Bug Fix| H[bugfix/*]
    F -->|Course Fix| I[course-fixes]
    
    %% Merge Process
    G --> J[Pull Request]
    H --> J
    I --> J
    J --> K[Code Review]
    K --> L[Merge to Main]
    
    %% Deployment
    L --> M[Staging Environment]
    M --> N[Testing]
    N --> O[Production Deployment]
```

## 📊 Performance Optimization Flow

```mermaid
graph TB
    %% Caching Strategy
    A[Request] --> B{Cache Hit?}
    B -->|Yes| C[Return Cached Data]
    B -->|No| D[Query Database]
    D --> E[Store in Cache]
    E --> F[Return Data]
    
    %% Database Optimization
    G[Database Query] --> H[Eager Loading]
    H --> I[Index Optimization]
    I --> J[Query Result]
    
    %% Frontend Optimization
    K[Vue Component] --> L[Lazy Loading]
    L --> M[Code Splitting]
    M --> N[Optimized Bundle]
```

## 🔧 Key Features Summary

### Core Functionality
- **User Management**: Registration, authentication, role-based access
- **Course Management**: Creation, editing, content organization
- **Learning System**: Video playback, progress tracking, completion
- **Quiz System**: Multiple question types, scoring, attempts tracking
- **Enrollment System**: Course enrollment, wishlist, recommendations

### Technical Stack
- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: Vue.js 3, Inertia.js, Tailwind CSS
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Sanctum, Google OAuth
- **File Storage**: Laravel Storage (local/cloud)
- **Caching**: Redis/Memcached

### Security Features
- Role-based permissions (Spatie)
- CSRF protection
- Input validation
- SQL injection prevention
- XSS protection

---

*This flowchart represents the current state of the Laravel Brive project as of the latest commit on the `course-fixes` branch.*