# Laravel Brive - Frontend Components & Composables

This directory contains optimized Vue.js components and composables following the DRY (Don't Repeat Yourself) principle for better performance and maintainability.

## 📁 Directory Structure

```
resources/js/
├── Components/
│   ├── Base/                 # Reusable base components
│   │   ├── Icon.vue         # Icon component with Heroicons support
│   │   ├── Button.vue       # Optimized button component
│   │   ├── Input.vue        # Form input component
│   │   ├── Card.vue         # Card container component
│   │   ├── Badge.vue        # Badge/label component
│   │   ├── Dropdown.vue     # Dropdown menu component
│   │   ├── DropdownItem.vue # Dropdown item component
│   │   └── index.js         # Centralized exports
│   └── Optimized/           # Complex optimized components
│       ├── StatsGrid.vue    # Statistics grid component
│       ├── OptimizedTable.vue # Advanced data table
│       ├── OptimizedForm.vue  # Dynamic form component
│       ├── NotificationCenter.vue # Notification system
│       └── index.js         # Centralized exports
├── Composables/             # Vue composables for logic reuse
│   ├── useApi.js           # API communication composable
│   ├── useForm.js          # Form management composable
│   ├── useTable.js         # Table data management
│   ├── useNotifications.js # Notification system
│   └── index.js            # Centralized exports
├── Services/                # API service classes
│   ├── QuizService.js      # Quiz management service
│   └── index.js            # Centralized exports
├── Examples/                # Complete implementation examples
│   ├── UserManagement.vue  # User CRUD interface example
│   ├── QuizManagement.vue  # Quiz management example
│   ├── Dashboard.vue       # Dashboard interface example
│   └── index.js            # Centralized exports
└── README.md               # This documentation
```

## 🧩 Base Components

### Core UI Components
- **Icon** - SVG icon component with multiple icon sets
- **Button** - Versatile button with variants, sizes, and states
- **Input** - Enhanced input with validation and features
- **Card** - Flexible card layout with header, body, footer
- **Badge** - Status and notification badges

### Specialized Components
- **BaseModal** - Reusable modal with customizable content
- **Dropdown** - Accessible dropdown with keyboard navigation
- **DropdownItem** - Individual dropdown menu items
- **StatsCard** - Statistics display with progress indicators
- **VideoPlayer** - Optimized video player component

### Usage Example
```vue
<template>
  <Card title="User Profile" variant="elevated">
    <Input 
      v-model="form.name" 
      label="Name" 
      icon="user" 
      required 
    />
    <Button 
      variant="primary" 
      :loading="form.processing" 
      @click="submit"
    >
      Save Changes
    </Button>
  </Card>
</template>

<script setup>
import { Card, Input, Button } from '@/Components/Base'
</script>
```

## ⚡ Optimized Components

### Data Components
- **DataTable** - Feature-rich table with sorting, filtering, pagination
- **OptimizedTable** - Performance-optimized table with virtual scrolling
- **StatsGrid** - Grid layout for statistics and metrics

### Form Components
- **SmartForm** - Dynamic form with validation and field rendering
- **OptimizedForm** - Performance-optimized form handling

### Notification Components
- **NotificationCenter** - Global notification system

### Usage Example
```vue
<template>
  <OptimizedTable 
    :data="users" 
    :columns="columns"
    :loading="loading"
    searchable
    sortable
    @row-click="handleRowClick"
  />
</template>

<script setup>
import { OptimizedTable } from '@/Components/Optimized'
import { useTable } from '@/Composables'

const { data: users, loading } = useTable('/api/users')
</script>
```

## 🎣 Composables

### API Management
- **useApi** - HTTP requests with caching and error handling
- **useCrud** - CRUD operations for resources

### Form Management
- **useForm** - Form state, validation, and submission
- **useMultiStepForm** - Multi-step form handling

### Data Management
- **useTable** - Table data with sorting, filtering, pagination
- **useList** - Simple list data management

### UI Management
- **useNotifications** - Notification system
- **useToast** - Toast notifications
- **useGlobalNotifications** - Global notification state
- **useApiNotifications** - API response notifications

### Usage Example
```vue
<script setup>
import { useApi, useForm, useNotifications } from '@/Composables'

// API calls with caching
const { get, post, loading, error } = useApi()

// Form management
const { form, validate, submit, errors } = useForm({
  name: '',
  email: ''
})

// Notifications
const { success, error: showError } = useNotifications()

const handleSubmit = async () => {
  try {
    await post('/api/users', form.value)
    success('User created successfully!')
  } catch (err) {
    showError('Failed to create user')
  }
}
</script>
```

## 🛠️ Services

Service classes provide centralized API communication and business logic:

### QuizService
```javascript
import { QuizService } from '@/Services'

const quizService = new QuizService()

// CRUD operations
const quizzes = await quizService.getPaginated({ page: 1, per_page: 10 })
const quiz = await quizService.getById(1, ['questions', 'course'])
const newQuiz = await quizService.create(quizData)
const updated = await quizService.update(1, updateData)
await quizService.remove(1)

// Advanced operations
const duplicated = await quizService.duplicate(1)
const analytics = await quizService.getAnalytics(1)
const attempts = await quizService.getAttempts(1)

// Bulk operations
await quizService.bulkPublish([1, 2, 3])
await quizService.bulkDelete([4, 5, 6])
```

### Other Services
```javascript
import { 
  UserService, 
  CourseService, 
  NotificationService,
  AnalyticsService,
  FileService 
} from '@/Services'

// User management
const userService = new UserService()
const users = await userService.getPaginated()
await userService.activate(userId)

// Course management
const courseService = new CourseService()
const courses = await courseService.getAll()
await courseService.enrollStudent(courseId, studentId)

// File uploads
const fileService = new FileService()
const uploaded = await fileService.upload(file, { folder: 'avatars' })
```

## 📚 Examples

Complete implementation examples demonstrating best practices:

### UserManagement.vue
Comprehensive user management interface featuring:
- User listing with pagination and search
- Create/Edit user forms with validation
- Bulk operations (activate, deactivate, delete)
- Role management and filtering
- Export functionality
- Real-time notifications

### QuizManagement.vue
Advanced quiz management system including:
- Quiz CRUD operations with dynamic forms
- Question builder with multiple choice support
- Quiz analytics and performance tracking
- Status management (draft, published, archived)
- Bulk operations and filtering
- Attempt tracking and leaderboards

### Dashboard.vue
Modern dashboard interface with:
- Real-time statistics and KPIs
- Activity timeline and notifications
- Top performers leaderboard
- System status monitoring
- Quick actions and settings
- Auto-refresh functionality

```javascript
// Import examples
import { UserManagement, QuizManagement, Dashboard } from '@/Examples'

// Or load dynamically
import { loadExample } from '@/Examples'
const UserManagement = await loadExample('UserManagement')
```

## 🎨 Styling & Theming

All components use Tailwind CSS classes and follow a consistent design system:

- **Variants**: primary, secondary, success, danger, warning, info
- **Sizes**: xs, sm, md, lg, xl
- **States**: loading, disabled, active, hover, focus
- **Responsive**: Mobile-first responsive design

## 🚀 Performance Features

### Component Optimization
- **Lazy Loading**: Components load only when needed
- **Virtual Scrolling**: Large lists render efficiently
- **Memoization**: Computed properties cached appropriately
- **Event Debouncing**: Search and input events optimized
- **Tree Shaking**: Only used components are included in build
- **Code Splitting**: Automatic route-based code splitting

### Caching Strategy
- **API Caching**: Responses cached with TTL
- **Component State**: Form and table state preserved
- **Memory Management**: Automatic cleanup on unmount
- **Service Caching**: Intelligent API response caching with TTL
- **Bulk Operations**: Efficient batch processing for large datasets

## 📝 Best Practices

### Component Usage
1. **Import from index files** for better tree-shaking
2. **Use composables** for shared logic
3. **Leverage slots** for customization
4. **Follow naming conventions** for consistency

### Performance
1. **Use v-memo** for expensive computations
2. **Implement virtual scrolling** for large datasets
3. **Debounce user inputs** to reduce API calls
4. **Cache API responses** appropriately

### Accessibility
1. **ARIA attributes** included in components
2. **Keyboard navigation** supported
3. **Screen reader** compatibility
4. **Focus management** implemented

## 🔧 Development

### Adding New Components
1. Create component in appropriate directory
2. Add to index.js export file
3. Update this README
4. Add TypeScript definitions if needed

### Testing
```bash
# Run component tests
npm run test:components

# Run composable tests
npm run test:composables

# Run all tests
npm run test
```

### Building
```bash
# Development build
npm run dev

# Production build
npm run build

# Build with analysis
npm run build:analyze
```

## 📚 Examples

Check the `examples/` directory for complete implementation examples:
- User management dashboard
- Quiz creation form
- Statistics dashboard
- Notification system

## 🤝 Contributing

1. Follow the DRY principle
2. Optimize for performance
3. Maintain accessibility
4. Add comprehensive tests
5. Update documentation

## 📄 License

This project is part of Laravel Brive and follows the same licensing terms.