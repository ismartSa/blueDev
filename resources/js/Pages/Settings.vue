<template>
    <AuthenticatedLayout>
        <template #header>
            <div :class="UI.HEADER" v-memo="[saveStatus]">
                <div :class="UI.CONTAINER">
                    <div :class="UI.HEADER_CONTENT">
                        <div :class="UI.HEADER_LEFT">
                            <CogIcon :class="UI.HEADER_ICON" />
                            <div>
                                <h1 :class="UI.HEADER_TITLE">Settings</h1>
                                <p :class="UI.HEADER_SUBTITLE">Manage your application preferences</p>
                            </div>
                        </div>
                        <div :class="UI.HEADER_RIGHT">
                            <div class="w-2 h-2 rounded-full" :class="saveStatus.color"></div>
                            <span :class="UI.HEADER_SUBTITLE">{{ saveStatus.text }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div :class="UI.MAIN">
            <!-- Flash Messages -->
            <Transition v-bind="UI.TRANSITION">
                <div v-if="$page.props.flash.success" :class="UI.FLASH_CONTAINER">
                    <div :class="UI.FLASH_SUCCESS">
                        <CheckCircleIcon :class="UI.FLASH_ICON" />
                        <span class="font-medium">{{ $page.props.flash.success }}</span>
                    </div>
                </div>
            </Transition>

            <div :class="UI.CONTAINER">
                <div :class="UI.GRID">
                    <!-- Sidebar Navigation -->
                    <div class="lg:col-span-3">
                        <nav :class="UI.SIDEBAR" v-memo="[activeTab, isAdmin]">
                            <button
                                v-for="(groupName, groupKey) in allTabs"
                                :key="groupKey"
                                @click="setActiveTab(groupKey)"
                                :class="sidebarClasses[groupKey]"
                            >
                                <component :is="tabIcons[groupKey]" :class="UI.SIDEBAR_ICON" />
                                <span class="truncate">{{ groupName }}</span>
                                <ChevronRightIcon v-if="activeTab === groupKey" :class="UI.SIDEBAR_CHEVRON" />
                            </button>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <!-- Loading State -->
                        <div v-if="!isInitialized" :class="UI.CONTENT_CARD">
                            <div class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <span class="ml-3 text-gray-600 dark:text-gray-400">Loading settings...</span>
                            </div>
                        </div>
                        
                        <!-- No Data State -->
                        <div v-else-if="!hasValidData" :class="UI.CONTENT_CARD">
                            <div class="flex flex-col items-center justify-center">
                                <CogIcon class="w-12 h-12 text-gray-400 mb-4" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Settings Available</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-center">There are no settings configured for this application.</p>
                            </div>
                        </div>
                        
                        <!-- Success/Error Message -->
                        <div v-if="message.show" :class="[
                            'fixed top-4 right-4 z-50 max-w-md p-4 rounded-lg shadow-lg transition-all duration-300',
                            message.type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'
                        ]">
                            <div class="flex items-center gap-3">
                                <component :is="message.type === 'success' ? 'CheckCircleIcon' : 'ExclamationTriangleIcon'" 
                                    :class="[
                                        'h-5 w-5',
                                        message.type === 'success' ? 'text-green-600' : 'text-red-600'
                                    ]" />
                                <p class="text-sm font-medium">{{ message.text }}</p>
                                <button @click="message.show = false" class="ml-auto text-gray-400 hover:text-gray-600">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Settings Form -->
                         <form @submit.prevent="updateSettings" class="space-y-8">
                            <!-- Settings Content -->
                            <div v-for="(groupSettings, groupKey) in settings" :key="groupKey" v-show="activeTab === groupKey" v-memo="[activeTab, form.settings]">
                                <div :class="UI.CONTENT_CARD">
                                    <div :class="UI.CONTENT_HEADER">
                                        <h3 :class="UI.CONTENT_TITLE">
                                            <component :is="tabIcons[groupKey]" class="w-5 h-5 mr-2" />
                                            {{ groups[groupKey] }}
                                        </h3>
                                    </div>
                                    <div :class="UI.CONTENT_BODY">
                                        <div :class="UI.CONTENT_GRID">
                                            <div v-for="setting in groupSettings" :key="setting.key" :class="UI.SETTING_ITEM" v-memo="[form.settings[setting.key]]">
                                                <div>
                                                    <label :for="setting.key" :class="UI.SETTING_LABEL">
                                                        {{ settingLabels[setting.key] }}
                                                    </label>
                                                    <p v-if="setting.description" :class="UI.SETTING_DESC">
                                                        {{ setting.description }}
                                                    </p>
                                                </div>
                                                <component
                                                    :is="inputComponents[setting.type]"
                                                    :setting="setting"
                                                    :modelValue="form.settings[setting.key]"
                                                    @update:modelValue="updateSetting(setting.key, $event)"
                                                    @file-upload="handleFileUpload"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Database Status Tab -->
                            <div v-show="activeTab === 'database'" v-memo="[activeTab]">
                                <div :class="UI.CONTENT_CARD">
                                    <div :class="UI.CONTENT_HEADER">
                                        <h3 :class="UI.CONTENT_TITLE">
                                            <CircleStackIcon class="w-5 h-5 mr-2" />
                                            Database Status
                                        </h3>
                                    </div>
                                    <div :class="UI.CONTENT_BODY">
                                        <DatabaseStatus v-if="activeTab === 'database'" :initial-connections="isAdmin ? databaseConnections : []" :is-admin="isAdmin" />
                                    </div>
                                </div>
                            </div>

                            <!-- Action Bar -->
                            <div v-if="isInitialized" :class="UI.CARD">
                                <div class="flex items-center justify-between">
                                    <button type="button" @click="resetSettings" :disabled="form.processing" :class="getButtonClass('reset')">
                                        <ArrowPathIcon :class="UI.BUTTON_ICON" />
                                        Reset to Default
                                    </button>
                                    <div class="flex items-center space-x-3">
                                        <button type="button" @click="previewChanges" :class="getButtonClass('preview')">
                                            <EyeIcon :class="UI.BUTTON_ICON" />
                                            Preview
                                        </button>
                                        <button type="submit" :disabled="form.processing || !hasChanges" :class="getButtonClass('save')">
                                            <component :is="form.processing ? 'ArrowPathIcon' : 'CheckIcon'" :class="[UI.BUTTON_ICON, { 'animate-spin': form.processing }]" />
                                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, shallowRef } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {
    CheckCircleIcon, ArrowPathIcon, CheckIcon, EyeIcon, ChevronRightIcon,
    BuildingOfficeIcon, PhoneIcon, ShareIcon, CogIcon, CircleStackIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Direct imports for better compatibility
import StringInput from '@/Components/Settings/StringInput.vue'
import BooleanInput from '@/Components/Settings/BooleanInput.vue'
import IntegerInput from '@/Components/Settings/IntegerInput.vue'
import FileInput from '@/Components/Settings/FileInput.vue'
import DatabaseStatus from '@/Components/Settings/DatabaseStatus.vue'

// Service worker registration for caching
if ('serviceWorker' in navigator && import.meta.env.PROD) {
    navigator.serviceWorker.register('/sw.js').catch(console.error)
}



const props = defineProps({
    settings: Object,
    groups: Object,
    databaseConnections: {
        type: Array,
        default: () => []
    }
})

// Consolidated UI Configuration - DRY principle applied
const UI = {
    // Layout classes
    HEADER: 'bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700',
    CONTAINER: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
    MAIN: 'min-h-screen bg-gray-50 dark:bg-gray-900 py-6',
    GRID: 'lg:grid lg:grid-cols-12 gap-6',
    CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 mt-6',
    
    // Header components
    HEADER_CONTENT: 'flex items-center justify-between h-16',
    HEADER_LEFT: 'flex items-center space-x-4',
    HEADER_RIGHT: 'flex items-center space-x-2',
    HEADER_ICON: 'w-8 h-8 text-gray-600 dark:text-gray-400',
    HEADER_TITLE: 'text-2xl font-semibold text-gray-900 dark:text-white',
    HEADER_SUBTITLE: 'text-sm text-gray-500 dark:text-gray-400',
    
    // Flash messages
    FLASH_CONTAINER: 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-6',
    FLASH_SUCCESS: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-xl flex items-center',
    FLASH_ICON: 'w-5 h-5 mr-3 flex-shrink-0',
    
    // Sidebar navigation
    SIDEBAR: 'bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-2 space-y-1',
    SIDEBAR_ITEM_BASE: 'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 w-full text-left',
    SIDEBAR_ITEM_ACTIVE: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm',
    SIDEBAR_ITEM_INACTIVE: 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50',
    SIDEBAR_ICON: 'w-5 h-5 mr-3 flex-shrink-0',
    SIDEBAR_CHEVRON: 'w-4 h-4 ml-auto text-blue-600 dark:text-blue-400',
    
    // Content sections
    CONTENT_CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden',
    CONTENT_HEADER: 'px-6 py-4 border-b border-gray-200 dark:border-gray-700',
    CONTENT_TITLE: 'text-lg font-semibold text-gray-900 dark:text-white flex items-center',
    CONTENT_BODY: 'p-6',
    CONTENT_GRID: 'grid grid-cols-1 gap-6',
    
    // Setting items
    SETTING_ITEM: 'bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4',
    SETTING_LABEL: 'block text-sm font-semibold text-gray-900 dark:text-white mb-1',
    SETTING_DESC: 'text-sm text-gray-600 dark:text-gray-400',
    
    // Buttons
    BUTTON_BASE: 'inline-flex items-center justify-center font-medium rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200',
    BUTTON_ICON: 'w-4 h-4 mr-2',
    
    // Transitions
    TRANSITION: {
        'enter-active-class': 'transition ease-out duration-300',
        'enter-from-class': 'opacity-0 transform scale-95',
        'enter-to-class': 'opacity-100 transform scale-100',
        'leave-active-class': 'transition ease-in duration-200',
        'leave-from-class': 'opacity-100 transform scale-100',
        'leave-to-class': 'opacity-0 transform scale-95'
    }
}

// Configuration maps for dynamic rendering - cached for performance
const ICONS = Object.freeze({
    branding: BuildingOfficeIcon,
    contact: PhoneIcon,
    social: ShareIcon,
    system: CogIcon,
    database: CircleStackIcon
})

const COMPONENTS = Object.freeze({
    string: StringInput,
    boolean: BooleanInput,
    integer: IntegerInput,
    file: FileInput
})

// Access page props for user permissions
const page = usePage()
const isAdmin = computed(() => 
    page.props.auth.can?.['manage courses'] || 
    page.props.auth.user?.roles?.some(role => role.name === 'admin')
)

// Reactive state - use shallowRef for better performance
const activeTab = ref(Object.keys(props.groups || {})[0] || '')
const originalSettings = shallowRef({})
const fileUploads = shallowRef({})
const form = useForm({ settings: {} })
const isInitialized = ref(false)
const message = ref({ type: '', text: '', show: false })

// Check if we have valid data
const hasValidData = computed(() => 
    props.settings && props.groups && 
    Object.keys(props.settings).length > 0 && 
    Object.keys(props.groups).length > 0
)

// Initialize settings data asynchronously with performance monitoring
const initializeSettings = async () => {
    const startTime = performance.now()
    
    await nextTick()
    
    // Handle case where settings might be empty or undefined
    if (!props.settings || Object.keys(props.settings).length === 0) {
        isInitialized.value = true
        return
    }
    
    const allSettings = Object.values(props.settings).flat()
    const settings = {}
    const original = {}
    
    // Batch process settings for better performance
    allSettings.forEach(setting => {
        if (setting && setting.key !== undefined) {
            settings[setting.key] = setting.value
            original[setting.key] = setting.value
        }
    })
    
    form.settings = settings
    originalSettings.value = original
    isInitialized.value = true
    
    // Pre-compute settings map for O(1) lookups
    const settingsMap = new Map()
    allSettings.forEach(setting => {
        if (setting && setting.key !== undefined) {
            settingsMap.set(setting.key, setting)
        }
    })
    
    // Cache the map for performance
    window.__settingsCache = settingsMap
    
    // Log performance metrics in development
    if (import.meta.env.DEV) {
        const endTime = performance.now()
        console.log(`Settings initialization took ${(endTime - startTime).toFixed(2)}ms`)
    }
}

// Preload critical resources
const preloadResources = () => {
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => {
            // Preload database status route
            if (isAdmin.value) {
                const link = document.createElement('link')
                link.rel = 'prefetch'
                link.href = route('admin.settings.database.status')
                document.head.appendChild(link)
            }
        })
    }
}

// Initialize form data immediately
onMounted(async () => {
    // Initialize settings synchronously to prevent empty content
    await initializeSettings()
    
    // Preload resources after initialization
    preloadResources()
})

// Optimized computed properties
const hasChanges = computed(() => 
    Object.keys(form.settings).some(key => form.settings[key] !== originalSettings.value[key])
)

const saveStatus = computed(() => {
    const states = {
        processing: { color: 'bg-yellow-400', text: 'Saving...' },
        success: { color: 'bg-green-400', text: 'Saved' },
        changed: { color: 'bg-orange-400', text: 'Unsaved changes' },
        default: { color: 'bg-gray-400', text: 'Up to date' }
    }
    
    return form.processing ? states.processing :
           form.recentlySuccessful ? states.success :
           hasChanges.value ? states.changed : states.default
})

// Dynamic tabs - cached
const allTabs = computed(() => ({
    ...props.groups,
    database: 'Database Status'
}))

// Pre-computed classes - memoized for performance
const sidebarClasses = computed(() => {
    const classes = {}
    const tabs = allTabs.value
    const active = activeTab.value
    
    // More efficient class computation
    Object.keys(tabs).forEach(key => {
        classes[key] = key === active 
            ? `${UI.SIDEBAR_ITEM_BASE} ${UI.SIDEBAR_ITEM_ACTIVE}`
            : `${UI.SIDEBAR_ITEM_BASE} ${UI.SIDEBAR_ITEM_INACTIVE}`
    })
    return classes
})

const tabIcons = computed(() => ICONS)
const inputComponents = computed(() => COMPONENTS)

// Pre-computed setting labels - cached and memoized
const settingLabels = computed(() => {
    if (!isInitialized.value) return {}
    
    const labels = {}
    const allSettings = Object.values(props.settings).flat()
    
    // Use more efficient string operations
    allSettings.forEach(setting => {
        const key = setting.key
        labels[key] = key.split('_').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ')
    })
    return labels
})



// Optimized utility functions - performance enhanced
const setActiveTab = (tabKey) => {
    if (activeTab.value !== tabKey) {
        activeTab.value = tabKey
    }
}

const updateSetting = (key, value) => {
    if (form.settings[key] !== value) {
        form.settings[key] = value
    }
}

const getButtonClass = (type) => {
    // Consistent button sizing and styling - DRY principle applied
    const baseClasses = `${UI.BUTTON_BASE} px-6 py-2.5 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed shadow-sm`
    
    const variants = {
        reset: `${baseClasses} text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/30 focus:ring-red-500`,
        preview: `${baseClasses} text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-blue-500`,
        save: `${baseClasses} text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500`
    }
    return variants[type] || variants.preview
}



// Optimized form handlers - performance enhanced
const updateSettings = () => {
    const formData = new FormData()
    const allSettings = Object.values(props.settings).flat()
    
    // Create settings map for O(1) lookup instead of O(n) find
    const settingsMap = new Map(allSettings.map(s => [s.key, s]))
    
    // Optimized settings array building with Map lookup
    const settingsArray = Object.entries(form.settings)
        .map(([key, value]) => {
            const setting = settingsMap.get(key)
            return setting ? { key, value, type: setting.type } : null
        })
        .filter(Boolean)

    formData.append('settings', JSON.stringify(settingsArray))
    
    // Append file uploads efficiently
    Object.entries(fileUploads.value).forEach(([key, file]) => {
        file && formData.append(`file_${key}`, file)
    })

    form.post(route('admin.settings.update'), {
        data: formData,
        forceFormData: true,
        onSuccess: () => {
            Object.assign(originalSettings.value, form.settings)
            fileUploads.value = {}
            showMessage('success', 'Settings updated successfully!')
        },
        onError: (errors) => {
            const errorMsg = Object.values(errors).flat().join(', ') || 'Failed to update settings'
            showMessage('error', errorMsg)
        }
    })
}

const handleFileUpload = (event, key) => {
    const file = event.target.files?.[0]
    if (!file) return
    
    fileUploads.value[key] = file
    const reader = new FileReader()
    reader.onload = (e) => updateSetting(key, e.target.result)
    reader.readAsDataURL(file)
}

const resetSettings = () => {
    confirm('Are you sure you want to reset all settings to default values?') &&
    form.post(route('admin.settings.reset'), {
        onSuccess: () => {
            showMessage('success', 'Settings reset to default values successfully!')
            setTimeout(() => location.reload(), 1500)
        },
        onError: (errors) => {
            const errorMsg = Object.values(errors).flat().join(', ') || 'Failed to reset settings'
            showMessage('error', errorMsg)
        }
    })
}

// Message handling utility
const showMessage = (type, text) => {
    message.value = { type, text, show: true }
    setTimeout(() => {
        message.value.show = false
    }, 5000)
}

const previewChanges = () => window.open('/', '_blank')
</script>
