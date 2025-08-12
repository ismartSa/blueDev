<template>
  <div class="quiz-management">
    <!-- Header with stats -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-4">Quiz Management</h1>
      <StatsGrid :stats="quizStats" />
    </div>

    <!-- Filters and Actions -->
    <Card class="mb-6">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-4 flex-1">
          <Input
            v-model="filters.search"
            placeholder="Search quizzes..."
            icon="search"
            clearable
            class="flex-1 max-w-md"
          />
          
          <Dropdown trigger-text="Status" trigger-icon="filter">
            <DropdownItem 
              v-for="status in statusOptions" 
              :key="status.value"
              :active="filters.status === status.value"
              @click="filters.status = status.value"
            >
              <Badge :variant="status.variant" size="xs" class="mr-2" />
              {{ status.label }}
            </DropdownItem>
          </Dropdown>
          
          <Dropdown trigger-text="Course" trigger-icon="book">
            <DropdownItem 
              v-for="course in courses" 
              :key="course.id"
              :active="filters.courseId === course.id"
              @click="filters.courseId = course.id"
            >
              {{ course.title }}
            </DropdownItem>
          </Dropdown>
        </div>
        
        <div class="flex gap-2">
          <Button 
            variant="outline-primary" 
            icon="download"
            @click="exportQuizzes"
          >
            Export
          </Button>
          
          <Button 
            variant="primary" 
            icon="plus"
            @click="createQuiz"
          >
            Create Quiz
          </Button>
        </div>
      </div>
    </Card>

    <!-- Quizzes Table -->
    <Card>
      <OptimizedTable
        :data="paginatedQuizzes"
        :columns="tableColumns"
        :loading="loading"
        :pagination="pagination"
        searchable
        sortable
        selectable
        @row-click="viewQuiz"
        @selection-change="handleSelectionChange"
        @page-change="handlePageChange"
        @sort-change="handleSortChange"
      >
        <!-- Custom title column with preview -->
        <template #title="{ row }">
          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <Icon name="quiz" size="sm" class="text-blue-600" />
              </div>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ row.title }}
              </p>
              <p class="text-sm text-gray-500 truncate">
                {{ row.questions_count }} questions
              </p>
            </div>
          </div>
        </template>
        
        <!-- Custom status column -->
        <template #status="{ row }">
          <Badge 
            :variant="getStatusVariant(row.status)"
            size="sm"
          >
            {{ row.status }}
          </Badge>
        </template>
        
        <!-- Custom difficulty column -->
        <template #difficulty="{ row }">
          <div class="flex items-center space-x-2">
            <div class="flex space-x-1">
              <div 
                v-for="i in 5" 
                :key="i"
                class="w-2 h-2 rounded-full"
                :class="i <= getDifficultyLevel(row.difficulty) ? 'bg-yellow-400' : 'bg-gray-200'"
              />
            </div>
            <span class="text-xs text-gray-600 capitalize">{{ row.difficulty }}</span>
          </div>
        </template>
        
        <!-- Custom attempts column -->
        <template #attempts="{ row }">
          <div class="text-center">
            <div class="text-sm font-medium text-gray-900">{{ row.attempts_count }}</div>
            <div class="text-xs text-gray-500">attempts</div>
          </div>
        </template>
        
        <!-- Custom actions column -->
        <template #actions="{ row }">
          <Dropdown trigger-icon="dots-vertical" trigger-variant="ghost" trigger-size="sm">
            <DropdownItem icon="eye" @click="viewQuiz(row)">
              View Details
            </DropdownItem>
            <DropdownItem icon="edit" @click="editQuiz(row)">
              Edit Quiz
            </DropdownItem>
            <DropdownItem icon="copy" @click="duplicateQuiz(row)">
              Duplicate
            </DropdownItem>
            <DropdownItem icon="chart-bar" @click="viewAnalytics(row)">
              Analytics
            </DropdownItem>
            <DropdownItem 
              icon="trash" 
              variant="danger" 
              @click="deleteQuiz(row)"
              divider
            >
              Delete
            </DropdownItem>
          </Dropdown>
        </template>
      </OptimizedTable>
    </Card>

    <!-- Quiz Form Modal -->
    <BaseModal 
      v-model="showQuizModal" 
      :title="editingQuiz ? 'Edit Quiz' : 'Create Quiz'"
      size="xl"
      :loading="formLoading"
    >
      <OptimizedForm
        :fields="quizFormFields"
        :initial-data="editingQuiz"
        :loading="formLoading"
        @submit="handleQuizSubmit"
        @cancel="closeQuizModal"
      >
        <!-- Custom questions field -->
        <template #questions="{ field, value, updateValue }">
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <label class="block text-sm font-medium text-gray-700">
                Questions ({{ value?.length || 0 }})
              </label>
              <Button 
                variant="outline-primary" 
                size="sm" 
                icon="plus"
                @click="addQuestion(value, updateValue)"
              >
                Add Question
              </Button>
            </div>
            
            <div v-if="value?.length" class="space-y-3">
              <Card 
                v-for="(question, index) in value" 
                :key="index"
                class="p-4"
              >
                <div class="flex items-start justify-between mb-3">
                  <h4 class="text-sm font-medium text-gray-900">
                    Question {{ index + 1 }}
                  </h4>
                  <Button 
                    variant="ghost" 
                    size="sm" 
                    icon="trash"
                    @click="removeQuestion(value, index, updateValue)"
                  />
                </div>
                
                <Input
                  v-model="question.text"
                  label="Question Text"
                  placeholder="Enter your question..."
                  required
                  class="mb-3"
                />
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <Input
                    v-for="(option, optIndex) in question.options"
                    :key="optIndex"
                    v-model="question.options[optIndex]"
                    :label="`Option ${optIndex + 1}`"
                    :placeholder="`Option ${optIndex + 1}...`"
                    required
                  />
                </div>
                
                <div class="mt-3">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Correct Answer
                  </label>
                  <div class="flex space-x-4">
                    <label 
                      v-for="(option, optIndex) in question.options"
                      :key="optIndex"
                      class="flex items-center"
                    >
                      <input
                        v-model="question.correct_answer"
                        :value="optIndex"
                        type="radio"
                        class="mr-2"
                      >
                      Option {{ optIndex + 1 }}
                    </label>
                  </div>
                </div>
              </Card>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
              <Icon name="quiz" size="lg" class="mx-auto mb-2" />
              <p>No questions added yet</p>
              <p class="text-sm">Click "Add Question" to get started</p>
            </div>
          </div>
        </template>
      </OptimizedForm>
    </BaseModal>

    <!-- Analytics Modal -->
    <BaseModal 
      v-model="showAnalyticsModal" 
      title="Quiz Analytics"
      size="xl"
    >
      <div v-if="selectedQuizAnalytics" class="space-y-6">
        <!-- Analytics Stats -->
        <StatsGrid :stats="analyticsStats" />
        
        <!-- Performance Chart -->
        <Card title="Performance Over Time">
          <div class="h-64 flex items-center justify-center text-gray-500">
            <!-- Chart component would go here -->
            <div class="text-center">
              <Icon name="chart-bar" size="xl" class="mx-auto mb-2" />
              <p>Performance chart visualization</p>
            </div>
          </div>
        </Card>
        
        <!-- Question Analysis -->
        <Card title="Question Analysis">
          <div class="space-y-4">
            <div 
              v-for="(question, index) in selectedQuizAnalytics.questions"
              :key="index"
              class="border rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium">Question {{ index + 1 }}</h4>
                <Badge 
                  :variant="question.difficulty_score > 0.7 ? 'success' : question.difficulty_score > 0.4 ? 'warning' : 'danger'"
                  size="sm"
                >
                  {{ Math.round(question.difficulty_score * 100) }}% correct
                </Badge>
              </div>
              <p class="text-sm text-gray-600 mb-3">{{ question.text }}</p>
              <div class="grid grid-cols-2 gap-2">
                <div 
                  v-for="(option, optIndex) in question.options"
                  :key="optIndex"
                  class="flex items-center justify-between p-2 rounded"
                  :class="optIndex === question.correct_answer ? 'bg-green-50' : 'bg-gray-50'"
                >
                  <span class="text-sm">{{ option }}</span>
                  <span class="text-xs text-gray-500">
                    {{ question.option_stats[optIndex] || 0 }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </BaseModal>

    <!-- Bulk Actions -->
    <div 
      v-if="selectedQuizzes.length > 0" 
      class="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg border p-4"
    >
      <div class="flex items-center space-x-4">
        <span class="text-sm text-gray-600">
          {{ selectedQuizzes.length }} quiz(es) selected
        </span>
        
        <Dropdown trigger-text="Actions" trigger-size="sm">
          <DropdownItem icon="eye" @click="bulkPublish">
            Publish Selected
          </DropdownItem>
          <DropdownItem icon="eye-off" @click="bulkUnpublish">
            Unpublish Selected
          </DropdownItem>
          <DropdownItem icon="copy" @click="bulkDuplicate">
            Duplicate Selected
          </DropdownItem>
          <DropdownItem 
            icon="trash" 
            variant="danger" 
            @click="bulkDelete"
            divider
          >
            Delete Selected
          </DropdownItem>
        </Dropdown>
        
        <Button 
          variant="secondary" 
          size="sm"
          @click="clearSelection"
        >
          Clear
        </Button>
      </div>
    </div>

    <!-- Notification Center -->
    <NotificationCenter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import {
  Card,
  Button,
  Input,
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
  useTable,
  useForm,
  useNotifications
} from '@/Composables'
import QuizService from '@/Services/QuizService'

// Services and composables
const quizService = new QuizService()
const { get, post, put, delete: deleteRequest, loading } = useApi()
const { success, error, confirm } = useNotifications()

// Reactive state
const quizzes = ref([])
const courses = ref([])
const selectedQuizzes = ref([])
const showQuizModal = ref(false)
const showAnalyticsModal = ref(false)
const editingQuiz = ref(null)
const selectedQuizAnalytics = ref(null)
const formLoading = ref(false)

// Filters
const filters = ref({
  search: '',
  status: 'all',
  courseId: null,
  sortBy: 'created_at',
  sortOrder: 'desc'
})

// Pagination
const pagination = ref({
  currentPage: 1,
  perPage: 10,
  total: 0
})

// Table configuration
const tableColumns = [
  { key: 'title', label: 'Quiz', sortable: true, width: '300px' },
  { key: 'course.title', label: 'Course', sortable: true },
  { key: 'status', label: 'Status', sortable: true, width: '120px' },
  { key: 'difficulty', label: 'Difficulty', sortable: true, width: '150px' },
  { key: 'attempts', label: 'Attempts', sortable: true, width: '100px' },
  { key: 'created_at', label: 'Created', sortable: true, width: '120px' },
  { key: 'actions', label: 'Actions', width: '80px' }
]

// Form configuration
const quizFormFields = [
  {
    name: 'title',
    label: 'Quiz Title',
    type: 'text',
    required: true,
    icon: 'quiz',
    placeholder: 'Enter quiz title...'
  },
  {
    name: 'description',
    label: 'Description',
    type: 'textarea',
    rows: 3,
    placeholder: 'Enter quiz description...'
  },
  {
    name: 'course_id',
    label: 'Course',
    type: 'select',
    required: true,
    options: computed(() => courses.value.map(c => ({ value: c.id, label: c.title })))
  },
  {
    name: 'difficulty',
    label: 'Difficulty',
    type: 'select',
    required: true,
    options: [
      { value: 'easy', label: 'Easy' },
      { value: 'medium', label: 'Medium' },
      { value: 'hard', label: 'Hard' },
      { value: 'expert', label: 'Expert' }
    ]
  },
  {
    name: 'time_limit',
    label: 'Time Limit (minutes)',
    type: 'number',
    min: 1,
    max: 180,
    placeholder: '30'
  },
  {
    name: 'max_attempts',
    label: 'Maximum Attempts',
    type: 'number',
    min: 1,
    max: 10,
    placeholder: '3'
  },
  {
    name: 'passing_score',
    label: 'Passing Score (%)',
    type: 'number',
    min: 0,
    max: 100,
    placeholder: '70'
  },
  {
    name: 'status',
    label: 'Status',
    type: 'select',
    required: true,
    options: [
      { value: 'draft', label: 'Draft' },
      { value: 'published', label: 'Published' },
      { value: 'archived', label: 'Archived' }
    ]
  },
  {
    name: 'questions',
    label: 'Questions',
    type: 'custom',
    required: true
  }
]

// Status options
const statusOptions = [
  { value: 'all', label: 'All Quizzes', variant: 'secondary' },
  { value: 'draft', label: 'Draft', variant: 'warning' },
  { value: 'published', label: 'Published', variant: 'success' },
  { value: 'archived', label: 'Archived', variant: 'danger' }
]

// Computed properties
const filteredQuizzes = computed(() => {
  let filtered = quizzes.value
  
  // Apply filters
  if (filters.value.search) {
    const query = filters.value.search.toLowerCase()
    filtered = filtered.filter(quiz => 
      quiz.title.toLowerCase().includes(query) ||
      quiz.description?.toLowerCase().includes(query)
    )
  }
  
  if (filters.value.status !== 'all') {
    filtered = filtered.filter(quiz => quiz.status === filters.value.status)
  }
  
  if (filters.value.courseId) {
    filtered = filtered.filter(quiz => quiz.course_id === filters.value.courseId)
  }
  
  return filtered
})

const paginatedQuizzes = computed(() => {
  const start = (pagination.value.currentPage - 1) * pagination.value.perPage
  const end = start + pagination.value.perPage
  return filteredQuizzes.value.slice(start, end)
})

const quizStats = computed(() => [
  {
    title: 'Total Quizzes',
    value: quizzes.value.length,
    icon: 'quiz',
    color: 'blue'
  },
  {
    title: 'Published',
    value: quizzes.value.filter(q => q.status === 'published').length,
    icon: 'eye',
    color: 'green',
    change: '+5%',
    changeType: 'positive'
  },
  {
    title: 'Total Attempts',
    value: quizzes.value.reduce((sum, q) => sum + (q.attempts_count || 0), 0),
    icon: 'users',
    color: 'purple'
  },
  {
    title: 'Avg. Score',
    value: '78%',
    icon: 'chart-bar',
    color: 'yellow'
  }
])

const analyticsStats = computed(() => {
  if (!selectedQuizAnalytics.value) return []
  
  const analytics = selectedQuizAnalytics.value
  return [
    {
      title: 'Total Attempts',
      value: analytics.total_attempts,
      icon: 'users',
      color: 'blue'
    },
    {
      title: 'Average Score',
      value: `${analytics.average_score}%`,
      icon: 'chart-bar',
      color: 'green'
    },
    {
      title: 'Pass Rate',
      value: `${analytics.pass_rate}%`,
      icon: 'check-circle',
      color: 'success'
    },
    {
      title: 'Completion Rate',
      value: `${analytics.completion_rate}%`,
      icon: 'clock',
      color: 'purple'
    }
  ]
})

// Methods
const loadQuizzes = async () => {
  try {
    const response = await quizService.getPaginated({
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
      ...filters.value
    })
    quizzes.value = response.data
    pagination.value.total = response.total
  } catch (err) {
    error('Failed to load quizzes')
  }
}

const loadCourses = async () => {
  try {
    const response = await get('/api/courses')
    courses.value = response.data
  } catch (err) {
    error('Failed to load courses')
  }
}

const createQuiz = () => {
  editingQuiz.value = null
  showQuizModal.value = true
}

const editQuiz = (quiz) => {
  editingQuiz.value = { ...quiz }
  showQuizModal.value = true
}

const viewQuiz = (quiz) => {
  // Navigate to quiz detail page
  console.log('View quiz:', quiz)
}

const duplicateQuiz = async (quiz) => {
  try {
    await quizService.duplicate(quiz.id)
    success('Quiz duplicated successfully')
    await loadQuizzes()
  } catch (err) {
    error('Failed to duplicate quiz')
  }
}

const deleteQuiz = async (quiz) => {
  const confirmed = await confirm(
    'Delete Quiz',
    `Are you sure you want to delete "${quiz.title}"? This action cannot be undone.`,
    'danger'
  )
  
  if (confirmed) {
    try {
      await deleteRequest(`/api/quizzes/${quiz.id}`)
      success('Quiz deleted successfully')
      await loadQuizzes()
    } catch (err) {
      error('Failed to delete quiz')
    }
  }
}

const viewAnalytics = async (quiz) => {
  try {
    selectedQuizAnalytics.value = await quizService.getAnalytics(quiz.id)
    showAnalyticsModal.value = true
  } catch (err) {
    error('Failed to load quiz analytics')
  }
}

const closeQuizModal = () => {
  showQuizModal.value = false
  editingQuiz.value = null
}

const handleQuizSubmit = async (formData) => {
  formLoading.value = true
  
  try {
    if (editingQuiz.value) {
      await put(`/api/quizzes/${editingQuiz.value.id}`, formData)
      success('Quiz updated successfully')
    } else {
      await post('/api/quizzes', formData)
      success('Quiz created successfully')
    }
    
    await loadQuizzes()
    closeQuizModal()
  } catch (err) {
    error('Failed to save quiz')
  } finally {
    formLoading.value = false
  }
}

const addQuestion = (questions, updateValue) => {
  const newQuestion = {
    text: '',
    options: ['', '', '', ''],
    correct_answer: 0
  }
  
  const updated = [...(questions || []), newQuestion]
  updateValue(updated)
}

const removeQuestion = (questions, index, updateValue) => {
  const updated = questions.filter((_, i) => i !== index)
  updateValue(updated)
}

const handleSelectionChange = (selection) => {
  selectedQuizzes.value = selection
}

const handlePageChange = (page) => {
  pagination.value.currentPage = page
  loadQuizzes()
}

const handleSortChange = ({ key, order }) => {
  filters.value.sortBy = key
  filters.value.sortOrder = order
  loadQuizzes()
}

const clearSelection = () => {
  selectedQuizzes.value = []
}

const getStatusVariant = (status) => {
  const variants = {
    draft: 'warning',
    published: 'success',
    archived: 'danger'
  }
  return variants[status] || 'secondary'
}

const getDifficultyLevel = (difficulty) => {
  const levels = {
    easy: 1,
    medium: 3,
    hard: 4,
    expert: 5
  }
  return levels[difficulty] || 1
}

const exportQuizzes = async () => {
  try {
    const response = await get('/api/quizzes/export', { responseType: 'blob' })
    // Handle file download
    success('Quizzes exported successfully')
  } catch (err) {
    error('Failed to export quizzes')
  }
}

// Bulk actions
const bulkPublish = async () => {
  try {
    await post('/api/quizzes/bulk-publish', {
      ids: selectedQuizzes.value.map(q => q.id)
    })
    success(`${selectedQuizzes.value.length} quiz(es) published`)
    selectedQuizzes.value = []
    await loadQuizzes()
  } catch (err) {
    error('Failed to publish quizzes')
  }
}

const bulkUnpublish = async () => {
  try {
    await post('/api/quizzes/bulk-unpublish', {
      ids: selectedQuizzes.value.map(q => q.id)
    })
    success(`${selectedQuizzes.value.length} quiz(es) unpublished`)
    selectedQuizzes.value = []
    await loadQuizzes()
  } catch (err) {
    error('Failed to unpublish quizzes')
  }
}

const bulkDuplicate = async () => {
  try {
    await post('/api/quizzes/bulk-duplicate', {
      ids: selectedQuizzes.value.map(q => q.id)
    })
    success(`${selectedQuizzes.value.length} quiz(es) duplicated`)
    selectedQuizzes.value = []
    await loadQuizzes()
  } catch (err) {
    error('Failed to duplicate quizzes')
  }
}

const bulkDelete = async () => {
  const confirmed = await confirm(
    'Delete Quizzes',
    `Are you sure you want to delete ${selectedQuizzes.value.length} quiz(es)? This action cannot be undone.`,
    'danger'
  )
  
  if (confirmed) {
    try {
      await post('/api/quizzes/bulk-delete', {
        ids: selectedQuizzes.value.map(q => q.id)
      })
      success(`${selectedQuizzes.value.length} quiz(es) deleted`)
      selectedQuizzes.value = []
      await loadQuizzes()
    } catch (err) {
      error('Failed to delete quizzes')
    }
  }
}

// Watchers
watch([() => filters.value.search, () => filters.value.status, () => filters.value.courseId], () => {
  pagination.value.currentPage = 1
  loadQuizzes()
}, { debounce: 300 })

// Lifecycle
onMounted(() => {
  loadQuizzes()
  loadCourses()
})
</script>

<style scoped>
.quiz-management {
  @apply p-6 max-w-7xl mx-auto;
}

/* Custom styles for question builder */
:deep(.question-card) {
  @apply transition-all duration-200 hover:shadow-md;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .quiz-management {
    @apply p-4;
  }
}
</style>