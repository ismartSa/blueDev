<template>
  <div class="user-management">
    <!-- Header with actions -->
    <Card class="mb-6">
      <template #headerActions>
        <Button 
          variant="primary" 
          icon="plus" 
          @click="openCreateModal"
        >
          Add User
        </Button>
      </template>
      
      <div class="flex flex-col sm:flex-row gap-4">
        <Input
          v-model="searchQuery"
          placeholder="Search users..."
          icon="search"
          clearable
          class="flex-1"
        />
        
        <Dropdown trigger-text="Filter" trigger-icon="filter">
          <DropdownItem 
            v-for="status in statusOptions" 
            :key="status.value"
            :active="selectedStatus === status.value"
            @click="selectedStatus = status.value"
          >
            <Badge :variant="status.variant" size="xs" class="mr-2" />
            {{ status.label }}
          </DropdownItem>
        </Dropdown>
      </div>
    </Card>

    <!-- Stats Grid -->
    <StatsGrid :stats="userStats" class="mb-6" />

    <!-- Users Table -->
    <Card>
      <OptimizedTable
        :data="filteredUsers"
        :columns="tableColumns"
        :loading="loading"
        searchable
        sortable
        selectable
        @row-click="handleRowClick"
        @selection-change="handleSelectionChange"
      >
        <!-- Custom status column -->
        <template #status="{ row }">
          <Badge 
            :variant="getStatusVariant(row.status)"
            size="sm"
          >
            {{ row.status }}
          </Badge>
        </template>
        
        <!-- Custom actions column -->
        <template #actions="{ row }">
          <div class="flex space-x-2">
            <Button 
              variant="ghost" 
              size="sm" 
              icon="edit"
              @click.stop="editUser(row)"
            />
            <Button 
              variant="ghost" 
              size="sm" 
              icon="trash"
              @click.stop="deleteUser(row)"
            />
          </div>
        </template>
      </OptimizedTable>
    </Card>

    <!-- Create/Edit User Modal -->
    <BaseModal 
      v-model="showUserModal" 
      :title="editingUser ? 'Edit User' : 'Create User'"
      size="lg"
    >
      <OptimizedForm
        :fields="userFormFields"
        :initial-data="editingUser"
        :loading="formLoading"
        @submit="handleUserSubmit"
        @cancel="closeUserModal"
      />
    </BaseModal>

    <!-- Bulk Actions -->
    <div 
      v-if="selectedUsers.length > 0" 
      class="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg border p-4"
    >
      <div class="flex items-center space-x-4">
        <span class="text-sm text-gray-600">
          {{ selectedUsers.length }} user(s) selected
        </span>
        
        <Button 
          variant="danger" 
          size="sm" 
          icon="trash"
          @click="bulkDelete"
        >
          Delete Selected
        </Button>
        
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
import { ref, computed, onMounted } from 'vue'
import {
  Card,
  Button,
  Input,
  Badge,
  Dropdown,
  DropdownItem,
  BaseModal
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

// Composables
const { get, post, put, delete: deleteRequest, loading } = useApi()
const { success, error, confirm } = useNotifications()

// Reactive state
const searchQuery = ref('')
const selectedStatus = ref('all')
const showUserModal = ref(false)
const editingUser = ref(null)
const formLoading = ref(false)
const selectedUsers = ref([])
const users = ref([])

// Table configuration
const tableColumns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'created_at', label: 'Created', sortable: true },
  { key: 'actions', label: 'Actions', width: '120px' }
]

// Form configuration
const userFormFields = [
  {
    name: 'name',
    label: 'Full Name',
    type: 'text',
    required: true,
    icon: 'user'
  },
  {
    name: 'email',
    label: 'Email Address',
    type: 'email',
    required: true,
    icon: 'mail'
  },
  {
    name: 'password',
    label: 'Password',
    type: 'password',
    required: true,
    icon: 'lock',
    showIf: (data) => !data.id // Only show for new users
  },
  {
    name: 'status',
    label: 'Status',
    type: 'select',
    required: true,
    options: [
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
      { value: 'pending', label: 'Pending' }
    ]
  },
  {
    name: 'role',
    label: 'Role',
    type: 'select',
    required: true,
    options: [
      { value: 'admin', label: 'Administrator' },
      { value: 'user', label: 'User' },
      { value: 'moderator', label: 'Moderator' }
    ]
  }
]

// Status options for filtering
const statusOptions = [
  { value: 'all', label: 'All Users', variant: 'secondary' },
  { value: 'active', label: 'Active', variant: 'success' },
  { value: 'inactive', label: 'Inactive', variant: 'danger' },
  { value: 'pending', label: 'Pending', variant: 'warning' }
]

// Computed properties
const filteredUsers = computed(() => {
  let filtered = users.value
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    )
  }
  
  // Filter by status
  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter(user => user.status === selectedStatus.value)
  }
  
  return filtered
})

const userStats = computed(() => [
  {
    title: 'Total Users',
    value: users.value.length,
    icon: 'users',
    color: 'blue'
  },
  {
    title: 'Active Users',
    value: users.value.filter(u => u.status === 'active').length,
    icon: 'check-circle',
    color: 'green',
    change: '+12%',
    changeType: 'positive'
  },
  {
    title: 'Pending Users',
    value: users.value.filter(u => u.status === 'pending').length,
    icon: 'clock',
    color: 'yellow'
  },
  {
    title: 'Inactive Users',
    value: users.value.filter(u => u.status === 'inactive').length,
    icon: 'x-circle',
    color: 'red'
  }
])

// Methods
const loadUsers = async () => {
  try {
    const response = await get('/api/users')
    users.value = response.data
  } catch (err) {
    error('Failed to load users')
  }
}

const openCreateModal = () => {
  editingUser.value = null
  showUserModal.value = true
}

const editUser = (user) => {
  editingUser.value = { ...user }
  showUserModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  editingUser.value = null
}

const handleUserSubmit = async (formData) => {
  formLoading.value = true
  
  try {
    if (editingUser.value) {
      // Update existing user
      await put(`/api/users/${editingUser.value.id}`, formData)
      success('User updated successfully')
    } else {
      // Create new user
      await post('/api/users', formData)
      success('User created successfully')
    }
    
    await loadUsers()
    closeUserModal()
  } catch (err) {
    error('Failed to save user')
  } finally {
    formLoading.value = false
  }
}

const deleteUser = async (user) => {
  const confirmed = await confirm(
    'Delete User',
    `Are you sure you want to delete ${user.name}? This action cannot be undone.`,
    'danger'
  )
  
  if (confirmed) {
    try {
      await deleteRequest(`/api/users/${user.id}`)
      success('User deleted successfully')
      await loadUsers()
    } catch (err) {
      error('Failed to delete user')
    }
  }
}

const bulkDelete = async () => {
  const confirmed = await confirm(
    'Delete Users',
    `Are you sure you want to delete ${selectedUsers.value.length} user(s)? This action cannot be undone.`,
    'danger'
  )
  
  if (confirmed) {
    try {
      await post('/api/users/bulk-delete', {
        ids: selectedUsers.value.map(u => u.id)
      })
      success(`${selectedUsers.value.length} user(s) deleted successfully`)
      selectedUsers.value = []
      await loadUsers()
    } catch (err) {
      error('Failed to delete users')
    }
  }
}

const handleRowClick = (user) => {
  // Navigate to user detail page or open quick view
  console.log('User clicked:', user)
}

const handleSelectionChange = (selection) => {
  selectedUsers.value = selection
}

const clearSelection = () => {
  selectedUsers.value = []
}

const getStatusVariant = (status) => {
  const variants = {
    active: 'success',
    inactive: 'danger',
    pending: 'warning'
  }
  return variants[status] || 'secondary'
}

// Lifecycle
onMounted(() => {
  loadUsers()
})
</script>

<style scoped>
.user-management {
  @apply p-6 max-w-7xl mx-auto;
}

/* Custom table styles */
:deep(.table-row:hover) {
  @apply bg-gray-50;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .user-management {
    @apply p-4;
  }
}
</style>