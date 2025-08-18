<template>
  <div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
          <p class="text-gray-600 mt-1">Welcome back! Here's what's happening today.</p>
        </div>
        
        <div class="flex items-center space-x-4">
          <!-- Quick Actions -->
          <Dropdown trigger-icon="plus" trigger-text="Quick Actions" trigger-variant="primary">
            <DropdownItem icon="quiz" @click="createQuiz">
              Create Quiz
            </DropdownItem>
            <DropdownItem icon="users" @click="createUser">
              Add User
            </DropdownItem>
            <DropdownItem icon="book" @click="createCourse">
              New Course
            </DropdownItem>
            <DropdownItem icon="chart-bar" @click="generateReport" divider>
              Generate Report
            </DropdownItem>
          </Dropdown>
          
          <!-- Notifications -->
          <Button 
            variant="ghost" 
            icon="bell"
            :badge="unreadNotifications"
            @click="showNotifications = !showNotifications"
          />
          
          <!-- User Menu -->
          <Dropdown trigger-icon="user" trigger-variant="ghost">
            <DropdownItem icon="user" @click="viewProfile">
              Profile
            </DropdownItem>
            <DropdownItem icon="cog" @click="openSettings">
              Settings
            </DropdownItem>
            <DropdownItem icon="logout" @click="logout" divider>
              Logout
            </DropdownItem>
          </Dropdown>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="mb-8">
      <StatsGrid :stats="overviewStats" />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Recent Activity -->
      <div class="lg:col-span-2">
        <Card title="Recent Activity" icon="clock">
          <template #actions>
            <Button variant="ghost" size="sm" icon="refresh" @click="refreshActivity" />
          </template>
          
          <div class="space-y-4">
            <div 
              v-for="activity in recentActivities" 
              :key="activity.id"
              class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <div class="flex-shrink-0">
                <div 
                  class="w-8 h-8 rounded-full flex items-center justify-center"
                  :class="getActivityColor(activity.type)"
                >
                  <Icon :name="getActivityIcon(activity.type)" size="xs" class="text-white" />
                </div>
              </div>
              
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">
                  {{ activity.title }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                  {{ formatTime(activity.created_at) }}
                </p>
              </div>
              
              <div class="flex-shrink-0">
                <Badge 
                  :variant="getActivityBadgeVariant(activity.type)"
                  size="xs"
                >
                  {{ activity.type }}
                </Badge>
              </div>
            </div>
            
            <div v-if="!recentActivities.length" class="text-center py-8 text-gray-500">
              <Icon name="clock" size="lg" class="mx-auto mb-2" />
              <p>No recent activity</p>
            </div>
          </div>
          
          <template #footer>
            <Button variant="outline" size="sm" class="w-full" @click="viewAllActivity">
              View All Activity
            </Button>
          </template>
        </Card>
      </div>
      
      <!-- Quick Stats -->
      <div class="space-y-6">
        <!-- Top Performers -->
        <Card title="Top Performers" icon="trophy">
          <div class="space-y-3">
            <div 
              v-for="(performer, index) in topPerformers" 
              :key="performer.id"
              class="flex items-center space-x-3"
            >
              <div class="flex-shrink-0">
                <div 
                  class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm"
                  :class="getRankColor(index)"
                >
                  {{ index + 1 }}
                </div>
              </div>
              
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">
                  {{ performer.name }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ performer.score }}% avg score
                </p>
              </div>
              
              <div class="flex-shrink-0">
                <Badge variant="success" size="xs">
                  {{ performer.quizzes_completed }}
                </Badge>
              </div>
            </div>
          </div>
        </Card>
        
        <!-- System Status -->
        <Card title="System Status" icon="server">
          <div class="space-y-3">
            <div 
              v-for="status in systemStatus" 
              :key="status.name"
              class="flex items-center justify-between"
            >
              <div class="flex items-center space-x-2">
                <div 
                  class="w-2 h-2 rounded-full"
                  :class="status.healthy ? 'bg-green-400' : 'bg-red-400'"
                />
                <span class="text-sm text-gray-700">{{ status.name }}</span>
              </div>
              
              <span class="text-xs text-gray-500">
                {{ status.value }}
              </span>
            </div>
          </div>
        </Card>
      </div>
    </div>

    <!-- Data Tables -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- Recent Quizzes -->
      <Card title="Recent Quizzes" icon="quiz">
        <template #actions>
          <Button variant="primary" size="sm" icon="plus" @click="createQuiz">
            New Quiz
          </Button>
        </template>
        
        <OptimizedTable
          :data="recentQuizzes"
          :columns="quizColumns"
          :loading="loadingQuizzes"
          compact
          @row-click="viewQuiz"
        >
          <template #title="{ row }">
            <div class="flex items-center space-x-2">
              <Icon name="quiz" size="xs" class="text-blue-500" />
              <span class="font-medium truncate">{{ row.title }}</span>
            </div>
          </template>
          
          <template #status="{ row }">
            <Badge :variant="getQuizStatusVariant(row.status)" size="xs">
              {{ row.status }}
            </Badge>
          </template>
          
          <template #actions="{ row }">
            <div class="flex space-x-1">
              <Button variant="ghost" size="xs" icon="eye" @click="viewQuiz(row)" />
              <Button variant="ghost" size="xs" icon="edit" @click="editQuiz(row)" />
            </div>
          </template>
        </OptimizedTable>
      </Card>
      
      <!-- Recent Users -->
      <Card title="Recent Users" icon="users">
        <template #actions>
          <Button variant="primary" size="sm" icon="plus" @click="createUser">
            Add User
          </Button>
        </template>
        
        <OptimizedTable
          :data="recentUsers"
          :columns="userColumns"
          :loading="loadingUsers"
          compact
          @row-click="viewUser"
        >
          <template #name="{ row }">
            <div class="flex items-center space-x-2">
              <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-xs font-medium text-blue-600">
                  {{ row.name.charAt(0).toUpperCase() }}
                </span>
              </div>
              <span class="font-medium truncate">{{ row.name }}</span>
            </div>
          </template>
          
          <template #role="{ row }">
            <Badge :variant="getRoleVariant(row.role)" size="xs">
              {{ row.role }}
            </Badge>
          </template>
          
          <template #actions="{ row }">
            <div class="flex space-x-1">
              <Button variant="ghost" size="xs" icon="eye" @click="viewUser(row)" />
              <Button variant="ghost" size="xs" icon="edit" @click="editUser(row)" />
            </div>
          </template>
        </OptimizedTable>
      </Card>
    </div>

    <!-- Modals -->
    <BaseModal v-model="showQuickCreateModal" title="Quick Create" size="lg">
      <OptimizedForm
        :fields="quickCreateFields"
        :loading="creatingItem"
        @submit="handleQuickCreate"
        @cancel="showQuickCreateModal = false"
      />
    </BaseModal>
    
    <!-- Settings Modal -->
    <BaseModal v-model="showSettingsModal" title="Dashboard Settings" size="md">
      <OptimizedForm
        :fields="settingsFields"
        :initial-data="dashboardSettings"
        @submit="updateSettings"
        @cancel="showSettingsModal = false"
      />
    </BaseModal>

    <!-- Notifications Panel -->
    <div 
      v-if="showNotifications"
      class="fixed inset-y-0 right-0 w-96 bg-white shadow-xl z-50 transform transition-transform"
      :class="showNotifications ? 'translate-x-0' : 'translate-x-full'"
    >
      <div class="h-full flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
            <Button 
              variant="ghost" 
              size="sm" 
              icon="x"
              @click="showNotifications = false"
            />
          </div>
        </div>
        
        <div class="flex-1 overflow-y-auto">
          <div class="p-4 space-y-3">
            <div 
              v-for="notification in notifications" 
              :key="notification.id"
              class="p-3 rounded-lg border cursor-pointer hover:bg-gray-50"
              :class="notification.read ? 'bg-white' : 'bg-blue-50 border-blue-200'"
              @click="markAsRead(notification)"
            >
              <div class="flex items-start space-x-3">
                <Icon 
                  :name="notification.icon" 
                  size="sm" 
                  :class="notification.read ? 'text-gray-400' : 'text-blue-500'"
                />
                
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900">
                    {{ notification.title }}
                  </p>
                  <p class="text-sm text-gray-500 mt-1">
                    {{ notification.message }}
                  </p>
                  <p class="text-xs text-gray-400 mt-2">
                    {{ formatTime(notification.created_at) }}
                  </p>
                </div>
                
                <div v-if="!notification.read" class="w-2 h-2 bg-blue-500 rounded-full" />
              </div>
            </div>
            
            <div v-if="!notifications.length" class="text-center py-8 text-gray-500">
              <Icon name="bell" size="lg" class="mx-auto mb-2" />
              <p>No notifications</p>
            </div>
          </div>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200">
          <Button variant="outline" size="sm" class="w-full" @click="markAllAsRead">
            Mark All as Read
          </Button>
        </div>
      </div>
    </div>

    <!-- Overlay for notifications panel -->
    <div 
      v-if="showNotifications"
      class="fixed inset-0 bg-black bg-opacity-25 z-40"
      @click="showNotifications = false"
    />

    <!-- Notification Center -->
    <NotificationCenter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import {
  Card,
  Button,
  Badge,
  Dropdown,
  DropdownItem,
  BaseModal,
  Icon
} from '@/Components/Base'
import {
  OptimizedTable,
  OptimizedForm,
  StatsGrid,
  NotificationCenter
} from '@/Components/Optimized'
import {
  useApi,
  useNotifications
} from '@/Composables'
import QuizService from '@/Services/QuizService'

// Services and composables
const quizService = new QuizService()
const { get, post } = useApi()
const { success, error } = useNotifications()

// Reactive state
const recentActivities = ref([])
const topPerformers = ref([])
const systemStatus = ref([])
const recentQuizzes = ref([])
const recentUsers = ref([])
const notifications = ref([])
const dashboardSettings = ref({})

// UI state
const showNotifications = ref(false)
const showQuickCreateModal = ref(false)
const showSettingsModal = ref(false)
const loadingQuizzes = ref(false)
const loadingUsers = ref(false)
const creatingItem = ref(false)

// Auto-refresh interval
let refreshInterval = null

// Computed properties
const overviewStats = computed(() => [
  {
    title: 'Total Users',
    value: '2,543',
    icon: 'users',
    color: 'blue',
    change: '+12%',
    changeType: 'positive'
  },
  {
    title: 'Active Quizzes',
    value: '89',
    icon: 'quiz',
    color: 'green',
    change: '+5%',
    changeType: 'positive'
  },
  {
    title: 'Completed Today',
    value: '156',
    icon: 'check-circle',
    color: 'purple',
    change: '+23%',
    changeType: 'positive'
  },
  {
    title: 'Average Score',
    value: '84%',
    icon: 'chart-bar',
    color: 'yellow',
    change: '-2%',
    changeType: 'negative'
  }
])

const unreadNotifications = computed(() => 
  notifications.value.filter(n => !n.read).length
)

// Table columns
const quizColumns = [
  { key: 'title', label: 'Title', width: '200px' },
  { key: 'status', label: 'Status', width: '100px' },
  { key: 'attempts', label: 'Attempts', width: '80px' },
  { key: 'actions', label: '', width: '80px' }
]

const userColumns = [
  { key: 'name', label: 'Name', width: '150px' },
  { key: 'email', label: 'Email', width: '180px' },
  { key: 'role', label: 'Role', width: '100px' },
  { key: 'actions', label: '', width: '80px' }
]

// Form fields
const quickCreateFields = [
  {
    name: 'type',
    label: 'Type',
    type: 'select',
    required: true,
    options: [
      { value: 'quiz', label: 'Quiz' },
      { value: 'user', label: 'User' },
      { value: 'course', label: 'Course' }
    ]
  },
  {
    name: 'title',
    label: 'Title',
    type: 'text',
    required: true,
    placeholder: 'Enter title...'
  },
  {
    name: 'description',
    label: 'Description',
    type: 'textarea',
    rows: 3,
    placeholder: 'Enter description...'
  }
]

const settingsFields = [
  {
    name: 'refresh_interval',
    label: 'Auto Refresh (seconds)',
    type: 'number',
    min: 30,
    max: 300,
    placeholder: '60'
  },
  {
    name: 'show_notifications',
    label: 'Show Notifications',
    type: 'checkbox'
  },
  {
    name: 'compact_view',
    label: 'Compact View',
    type: 'checkbox'
  }
]

// Methods
const loadDashboardData = async () => {
  try {
    const [activities, performers, status, quizzes, users, notifs] = await Promise.all([
      get('/api/dashboard/activities'),
      get('/api/dashboard/top-performers'),
      get('/api/dashboard/system-status'),
      get('/api/dashboard/recent-quizzes'),
      get('/api/dashboard/recent-users'),
      get('/api/notifications')
    ])
    
    recentActivities.value = activities.data
    topPerformers.value = performers.data
    systemStatus.value = status.data
    recentQuizzes.value = quizzes.data
    recentUsers.value = users.data
    notifications.value = notifs.data
  } catch (err) {
    error('Failed to load dashboard data')
  }
}

const refreshActivity = async () => {
  try {
    const response = await get('/api/dashboard/activities')
    recentActivities.value = response.data
    success('Activity refreshed')
  } catch (err) {
    error('Failed to refresh activity')
  }
}

const createQuiz = () => {
  // Navigate to quiz creation or open modal
  console.log('Create quiz')
}

const createUser = () => {
  // Navigate to user creation or open modal
  console.log('Create user')
}

const createCourse = () => {
  // Navigate to course creation or open modal
  console.log('Create course')
}

const generateReport = () => {
  // Open report generation modal
  console.log('Generate report')
}

const viewProfile = () => {
  // Navigate to profile page
  console.log('View profile')
}

const openSettings = () => {
  showSettingsModal.value = true
}

const logout = () => {
  // Handle logout
  console.log('Logout')
}

const viewAllActivity = () => {
  // Navigate to activity page
  console.log('View all activity')
}

const viewQuiz = (quiz) => {
  // Navigate to quiz detail
  console.log('View quiz:', quiz)
}

const editQuiz = (quiz) => {
  // Navigate to quiz edit
  console.log('Edit quiz:', quiz)
}

const viewUser = (user) => {
  // Navigate to user detail
  console.log('View user:', user)
}

const editUser = (user) => {
  // Navigate to user edit
  console.log('Edit user:', user)
}

const handleQuickCreate = async (data) => {
  creatingItem.value = true
  
  try {
    await post(`/api/${data.type}s`, data)
    success(`${data.type} created successfully`)
    showQuickCreateModal.value = false
    await loadDashboardData()
  } catch (err) {
    error(`Failed to create ${data.type}`)
  } finally {
    creatingItem.value = false
  }
}

const updateSettings = async (settings) => {
  try {
    await post('/api/dashboard/settings', settings)
    dashboardSettings.value = settings
    success('Settings updated successfully')
    showSettingsModal.value = false
    
    // Apply settings
    if (settings.refresh_interval) {
      setupAutoRefresh(settings.refresh_interval * 1000)
    }
  } catch (err) {
    error('Failed to update settings')
  }
}

const markAsRead = async (notification) => {
  try {
    await post(`/api/notifications/${notification.id}/read`)
    notification.read = true
  } catch (err) {
    error('Failed to mark notification as read')
  }
}

const markAllAsRead = async () => {
  try {
    await post('/api/notifications/mark-all-read')
    notifications.value.forEach(n => n.read = true)
    success('All notifications marked as read')
  } catch (err) {
    error('Failed to mark notifications as read')
  }
}

const setupAutoRefresh = (interval = 60000) => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
  
  refreshInterval = setInterval(() => {
    loadDashboardData()
  }, interval)
}

// Utility methods
const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleString()
}

const getActivityColor = (type) => {
  const colors = {
    quiz: 'bg-blue-500',
    user: 'bg-green-500',
    course: 'bg-purple-500',
    system: 'bg-gray-500'
  }
  return colors[type] || 'bg-gray-500'
}

const getActivityIcon = (type) => {
  const icons = {
    quiz: 'quiz',
    user: 'user',
    course: 'book',
    system: 'cog'
  }
  return icons[type] || 'info'
}

const getActivityBadgeVariant = (type) => {
  const variants = {
    quiz: 'primary',
    user: 'success',
    course: 'purple',
    system: 'secondary'
  }
  return variants[type] || 'secondary'
}

const getRankColor = (index) => {
  const colors = [
    'bg-yellow-500', // 1st
    'bg-gray-400',   // 2nd
    'bg-orange-600', // 3rd
    'bg-blue-500',   // 4th+
    'bg-blue-500'
  ]
  return colors[index] || 'bg-blue-500'
}

const getQuizStatusVariant = (status) => {
  const variants = {
    draft: 'warning',
    published: 'success',
    archived: 'danger'
  }
  return variants[status] || 'secondary'
}

const getRoleVariant = (role) => {
  const variants = {
    admin: 'danger',
    teacher: 'primary',
    student: 'success'
  }
  return variants[role] || 'secondary'
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
  setupAutoRefresh()
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>

<style scoped>
.dashboard {
  @apply p-6 max-w-7xl mx-auto;
}

.dashboard-header {
  @apply mb-8;
}

/* Custom scrollbar for notifications */
:deep(.overflow-y-auto) {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

:deep(.overflow-y-auto::-webkit-scrollbar) {
  width: 6px;
}

:deep(.overflow-y-auto::-webkit-scrollbar-track) {
  background: #f7fafc;
}

:deep(.overflow-y-auto::-webkit-scrollbar-thumb) {
  background: #cbd5e0;
  border-radius: 3px;
}

:deep(.overflow-y-auto::-webkit-scrollbar-thumb:hover) {
  background: #a0aec0;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .dashboard {
    @apply p-4;
  }
  
  .dashboard-header h1 {
    @apply text-2xl;
  }
}
</style>