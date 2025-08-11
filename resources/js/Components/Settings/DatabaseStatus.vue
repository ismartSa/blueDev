<template>
    <div class="space-y-4" data-database-status>
        <!-- Header Section -->
        <header class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Database Connections</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Monitor database connection status and performance</p>
            </div>
            <div v-if="isAdmin" class="flex items-center space-x-3">
                <ActionButton 
                    @click="showAddForm = true" 
                    variant="primary"
                    :icon="PlusIcon"
                    text="Add Connection"
                />
                <ActionButton 
                    @click="refreshStatus" 
                    :disabled="isRefreshing"
                    variant="secondary"
                    :icon="ArrowPathIcon"
                    :text="isRefreshing ? 'Refreshing...' : 'Refresh'"
                    :loading="isRefreshing"
                />
            </div>
        </header>

        <!-- Connections Grid -->
        <div class="grid gap-4">
            <div v-for="connection in connections" :key="connection.name" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <!-- Connection Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div :class="[
                            'w-3 h-3 rounded-full',
                            connection.active ? 'bg-green-500' : 'bg-red-500'
                        ]"></div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ connection.name }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ connection.driver }} • {{ connection.host }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium" :class="connection.active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                            {{ connection.active ? 'Connected' : 'Disconnected' }}
                        </div>
                        <div v-if="connection.response_time" class="text-xs text-gray-500 dark:text-gray-400">
                            {{ connection.response_time }}ms
                        </div>
                    </div>
                </div>
                
                <!-- Connection Status -->
                <div class="mb-4 p-3 rounded-md" :class="connection.active ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'">
                    <p class="text-sm" :class="connection.active ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'">
                        {{ connection.message }}
                    </p>
                </div>
                
                <!-- Databases List -->
                <div v-if="connection.active && connection.databases && connection.databases.length > 0">
                    <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Available Databases ({{ connection.databases.length }})</h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        <div v-for="database in connection.databases" :key="database" 
                             class="px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></div>
                                <span class="truncate">{{ database }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- No Databases Message -->
                <div v-else-if="connection.active && (!connection.databases || connection.databases.length === 0)" 
                     class="text-sm text-gray-500 dark:text-gray-400 italic">
                    No databases found or unable to list databases
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <EmptyState v-if="connections.length === 0" :is-admin="isAdmin" />

        <!-- Add/Edit Connection Modal -->
        <ConnectionModal 
                v-if="showAddForm || editingConnection"
                :editing="editingConnection"
                :form="form"
                :drivers="DRIVERS"
                :is-submitting="isSubmitting"
                @close="closeForm"
                @save="saveConnection"
                @update:form="form = $event"
            />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, shallowRef, nextTick, reactive, computed } from 'vue'
import { ArrowPathIcon, PlusIcon } from '@heroicons/vue/24/outline'

// Import child components
import ActionButton from './ActionButton.vue'
import ConnectionCard from './ConnectionCard.vue'
import ConnectionModal from './ConnectionModal.vue'
import EmptyState from './EmptyState.vue'

const props = defineProps({
    initialConnections: {
        type: Array,
        default: () => []
    },
    isAdmin: {
        type: Boolean,
        default: false
    }
})

// Constants
const DRIVERS = Object.freeze(['mysql', 'pgsql', 'sqlite', 'sqlsrv'])

// State - use shallowRef for better performance with proper fallback
// If no connections provided, show mock data for demonstration
const mockConnections = [
    {
        name: 'SQLite',
        driver: 'sqlite',
        host: 'N/A',
        database: '/Users/ismartsa/Sites/Laravel-Brive/database/database.sqlite',
        active: true,
        message: 'Connected successfully',
        response_time: 3.45,
        databases: ['database.sqlite']
    },
    {
        name: 'MySQL',
        driver: 'mysql',
        host: '127.0.0.1',
        database: 'laravel_brive',
        active: false,
        message: 'Connection failed: SQLSTATE[HY000] [2002] Connection refused',
        response_time: null,
        databases: []
    },
    {
        name: 'PostgreSQL',
        driver: 'pgsql',
        host: '127.0.0.1',
        database: 'laravel_brive',
        active: false,
        message: 'Connection failed: SQLSTATE[08006] [7] connection to server failed',
        response_time: null,
        databases: []
    }
]

const connections = shallowRef([...(props.initialConnections?.length > 0 ? props.initialConnections : mockConnections)])

// Check if we have any connections data
const hasConnectionsData = computed(() => connections.value.length > 0)
const isRefreshing = ref(false)
const isVisible = ref(false)
const refreshController = ref(null)

// Form state
const showAddForm = ref(false)
const editingConnection = ref(null)
const isSubmitting = ref(false)
const form = reactive({
    name: '',
    driver: '',
    host: '',
    port: '',
    database: '',
    username: '',
    password: '',
    active: false
})

// Form methods
const resetForm = () => {
    Object.assign(form, {
        name: '',
        driver: '',
        host: '',
        port: '',
        database: '',
        username: '',
        password: '',
        active: false
    })
}

const editConnection = (connection) => {
    editingConnection.value = connection
    Object.assign(form, {
        name: connection.name,
        driver: connection.driver,
        host: connection.host,
        port: connection.port || '',
        database: connection.database,
        username: connection.username || '',
        password: '', // Don't populate password for security
        active: connection.active
    })
}

const closeForm = () => {
    showAddForm.value = false
    editingConnection.value = null
    resetForm()
}

const saveConnection = async () => {
    if (isSubmitting.value) return
    
    isSubmitting.value = true
    
    try {
        const endpoint = editingConnection.value 
            ? route('admin.settings.database.update', editingConnection.value.id)
            : route('admin.settings.database.store')
            
        const method = editingConnection.value ? 'PUT' : 'POST'
        
        const response = await fetch(endpoint, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(form)
        })
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        
        const data = await response.json()
        
        if (editingConnection.value) {
            // Update existing connection
            const index = connections.value.findIndex(c => c.id === editingConnection.value.id)
            if (index !== -1) {
                connections.value[index] = { ...connections.value[index], ...data.connection }
            }
        } else {
            // Add new connection
            connections.value = [...connections.value, data.connection]
        }
        
        closeForm()
        await refreshStatus() // Refresh to get updated status
        
    } catch (error) {
        console.error('Failed to save connection:', error)
        // You could add a toast notification here
    } finally {
        isSubmitting.value = false
    }
}

// Debounced refresh to prevent excessive requests
let refreshTimeout = null
const debouncedRefresh = () => {
    if (refreshTimeout) clearTimeout(refreshTimeout)
    refreshTimeout = setTimeout(refreshStatus, 300)
}

// Optimized refresh with abort controller
const refreshStatus = async () => {
    if (isRefreshing.value) return
    
    // Cancel previous request if still pending
    if (refreshController.value) {
        refreshController.value.abort()
    }
    
    isRefreshing.value = true
    refreshController.value = new AbortController()
    
    try {
        const response = await fetch(route('admin.settings.database.status'), {
            signal: refreshController.value.signal,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        
        const data = await response.json()
        connections.value = data.connections || []
    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error('Failed to refresh database status:', error)
        }
    } finally {
        isRefreshing.value = false
        refreshController.value = null
    }
}

// Intersection Observer for visibility-based refresh
let refreshInterval = null
let observer = null

const startAutoRefresh = () => {
    if (refreshInterval) return
    // Increased interval to 60 seconds for better performance
    refreshInterval = setInterval(() => {
        if (isVisible.value && document.visibilityState === 'visible') {
            debouncedRefresh()
        }
    }, 60000)
}

const stopAutoRefresh = () => {
    if (refreshInterval) {
        clearInterval(refreshInterval)
        refreshInterval = null
    }
}

// Initialize with visibility detection
onMounted(async () => {
    await nextTick()
    
    // Set up intersection observer for performance
    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver(
            (entries) => {
                isVisible.value = entries[0].isIntersecting
                if (isVisible.value) {
                    startAutoRefresh()
                } else {
                    stopAutoRefresh()
                }
            },
            { threshold: 0.1 }
        )
        
        const element = document.querySelector('[data-database-status]')
        if (element) observer.observe(element)
    } else {
        // Fallback for older browsers
        isVisible.value = true
        startAutoRefresh()
    }
})

// Cleanup on unmount
onUnmounted(() => {
    stopAutoRefresh()
    
    if (observer) {
        observer.disconnect()
    }
    
    if (refreshController.value) {
        refreshController.value.abort()
    }
    
    if (refreshTimeout) {
        clearTimeout(refreshTimeout)
    }
})
</script>