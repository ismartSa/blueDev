/**
 * Examples Index - Centralized exports for all example components
 * 
 * This file provides easy access to all example implementations that demonstrate
 * the usage of our optimized components, composables, and services.
 * 
 * Usage:
 * import { UserManagement, QuizManagement, Dashboard } from '@/Examples'
 * 
 * Or import specific examples:
 * import UserManagement from '@/Examples/UserManagement.vue'
 */

// Main example components
export { default as UserManagement } from './UserManagement.vue'
export { default as QuizManagement } from './QuizManagement.vue'
export { default as Dashboard } from './Dashboard.vue'

// Grouped exports for easier bulk imports
export const ManagementExamples = {
  UserManagement: () => import('./UserManagement.vue'),
  QuizManagement: () => import('./QuizManagement.vue')
}

export const DashboardExamples = {
  Dashboard: () => import('./Dashboard.vue')
}

// All examples for dynamic loading
export const AllExamples = {
  UserManagement: () => import('./UserManagement.vue'),
  QuizManagement: () => import('./QuizManagement.vue'),
  Dashboard: () => import('./Dashboard.vue')
}

/**
 * Example metadata for documentation and routing
 */
export const ExampleMetadata = {
  UserManagement: {
    name: 'User Management',
    description: 'Complete user management interface with CRUD operations, search, filtering, and bulk actions',
    features: [
      'User listing with pagination',
      'Advanced search and filtering',
      'Create/Edit user forms',
      'Bulk operations (activate, deactivate, delete)',
      'Role management',
      'Export functionality',
      'Real-time notifications'
    ],
    components: [
      'OptimizedTable',
      'OptimizedForm', 
      'StatsGrid',
      'Card',
      'Button',
      'Input',
      'Badge',
      'Dropdown',
      'BaseModal',
      'NotificationCenter'
    ],
    composables: [
      'useApi',
      'useTable',
      'useForm',
      'useNotifications'
    ],
    route: '/examples/user-management'
  },
  
  QuizManagement: {
    name: 'Quiz Management',
    description: 'Comprehensive quiz management system with question builder, analytics, and attempt tracking',
    features: [
      'Quiz CRUD operations',
      'Dynamic question builder',
      'Quiz analytics and reporting',
      'Attempt tracking',
      'Bulk operations',
      'Status management (draft, published, archived)',
      'Difficulty levels',
      'Export/Import functionality'
    ],
    components: [
      'OptimizedTable',
      'OptimizedForm',
      'StatsGrid',
      'Card',
      'Button',
      'Input',
      'Badge',
      'Dropdown',
      'BaseModal',
      'Icon',
      'NotificationCenter'
    ],
    composables: [
      'useApi',
      'useTable',
      'useForm',
      'useNotifications'
    ],
    services: [
      'QuizService'
    ],
    route: '/examples/quiz-management'
  },
  
  Dashboard: {
    name: 'Dashboard',
    description: 'Modern dashboard with real-time stats, activity feeds, notifications, and quick actions',
    features: [
      'Real-time statistics',
      'Activity timeline',
      'Top performers leaderboard',
      'System status monitoring',
      'Quick actions menu',
      'Notification center',
      'Auto-refresh functionality',
      'Responsive design',
      'Settings management'
    ],
    components: [
      'StatsGrid',
      'OptimizedTable',
      'OptimizedForm',
      'Card',
      'Button',
      'Badge',
      'Dropdown',
      'BaseModal',
      'Icon',
      'NotificationCenter'
    ],
    composables: [
      'useApi',
      'useNotifications'
    ],
    route: '/examples/dashboard'
  }
}

/**
 * Example categories for organization
 */
export const ExampleCategories = {
  management: {
    name: 'Management Interfaces',
    description: 'Complete CRUD interfaces for managing different entities',
    examples: ['UserManagement', 'QuizManagement']
  },
  
  dashboard: {
    name: 'Dashboards & Analytics',
    description: 'Dashboard interfaces with real-time data and analytics',
    examples: ['Dashboard']
  }
}

/**
 * Get example by name
 * @param {string} name - Example name
 * @returns {Object|null} Example metadata
 */
export function getExampleMetadata(name) {
  return ExampleMetadata[name] || null
}

/**
 * Get examples by category
 * @param {string} category - Category name
 * @returns {Array} Array of example names
 */
export function getExamplesByCategory(category) {
  return ExampleCategories[category]?.examples || []
}

/**
 * Get all example names
 * @returns {Array} Array of all example names
 */
export function getAllExampleNames() {
  return Object.keys(ExampleMetadata)
}

/**
 * Check if example exists
 * @param {string} name - Example name
 * @returns {boolean} Whether example exists
 */
export function hasExample(name) {
  return name in ExampleMetadata
}

/**
 * Get example component dynamically
 * @param {string} name - Example name
 * @returns {Promise<Component>} Vue component
 */
export async function loadExample(name) {
  if (!hasExample(name)) {
    throw new Error(`Example '${name}' not found`)
  }
  
  const loader = AllExamples[name]
  if (!loader) {
    throw new Error(`Loader for example '${name}' not found`)
  }
  
  return await loader()
}

/**
 * Example usage patterns and best practices
 */
export const ExampleUsagePatterns = {
  // Component composition pattern
  componentComposition: {
    description: 'How to compose multiple components together',
    example: `
// Import components
import { Card, Button, OptimizedTable } from '@/Components/Base'
import { StatsGrid } from '@/Components/Optimized'

// Use in template
<Card title="Data Table">
  <template #actions>
    <Button variant="primary" @click="createItem">Create</Button>
  </template>
  
  <OptimizedTable :data="items" :columns="columns" />
</Card>
    `
  },
  
  // Composable usage pattern
  composableUsage: {
    description: 'How to use composables for state management',
    example: `
// Import composables
import { useApi, useTable, useNotifications } from '@/Composables'

// Use in setup
const { get, post, loading } = useApi()
const { data, pagination, filters } = useTable()
const { success, error } = useNotifications()

// Use in methods
const loadData = async () => {
  try {
    const response = await get('/api/data')
    data.value = response.data
    success('Data loaded successfully')
  } catch (err) {
    error('Failed to load data')
  }
}
    `
  },
  
  // Service integration pattern
  serviceIntegration: {
    description: 'How to integrate services with components',
    example: `
// Import service
import QuizService from '@/Services/QuizService'

// Create service instance
const quizService = new QuizService()

// Use service methods
const loadQuizzes = async () => {
  const quizzes = await quizService.getPaginated({
    page: 1,
    per_page: 10
  })
  return quizzes
}
    `
  }
}

/**
 * Development guidelines for creating new examples
 */
export const DevelopmentGuidelines = {
  structure: {
    title: 'Example Structure',
    guidelines: [
      'Use composition API with <script setup>',
      'Import components from centralized index files',
      'Use composables for state management',
      'Implement proper error handling',
      'Add loading states for async operations',
      'Include responsive design considerations',
      'Add proper TypeScript types if using TS'
    ]
  },
  
  naming: {
    title: 'Naming Conventions',
    guidelines: [
      'Use PascalCase for component names',
      'Use camelCase for variables and methods',
      'Use descriptive names that indicate purpose',
      'Prefix boolean variables with is/has/should',
      'Use consistent naming across similar examples'
    ]
  },
  
  documentation: {
    title: 'Documentation Requirements',
    guidelines: [
      'Add component description at the top',
      'Document all props and their types',
      'Include usage examples in comments',
      'Document complex logic and algorithms',
      'Add metadata to ExampleMetadata object',
      'Update this index file when adding new examples'
    ]
  },
  
  performance: {
    title: 'Performance Considerations',
    guidelines: [
      'Use lazy loading for heavy components',
      'Implement proper pagination for large datasets',
      'Use computed properties for derived data',
      'Debounce search and filter inputs',
      'Optimize re-renders with proper key usage',
      'Use virtual scrolling for very large lists'
    ]
  }
}

// Default export for convenience
export default {
  UserManagement: () => import('./UserManagement.vue'),
  QuizManagement: () => import('./QuizManagement.vue'),
  Dashboard: () => import('./Dashboard.vue')
}