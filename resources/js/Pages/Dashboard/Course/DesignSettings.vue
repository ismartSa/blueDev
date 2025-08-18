<template>
    <AuthenticatedLayout>
        <template #header>
            <div :class="UI.HEADER" v-memo="[saveStatus]">
                <div :class="UI.CONTAINER">
                    <div :class="UI.HEADER_CONTENT">
                        <div :class="UI.HEADER_LEFT">
                            <PaintBrushIcon :class="UI.HEADER_ICON" />
                            <div>
                                <h1 :class="UI.HEADER_TITLE">Design Settings</h1>
                                <p :class="UI.HEADER_SUBTITLE">Customize course appearance and layout</p>
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
                        <nav :class="UI.SIDEBAR" v-memo="[activeTab]">
                            <button
                                v-for="(section, key) in designSections"
                                :key="key"
                                @click="setActiveTab(key)"
                                :class="sidebarClasses[key]"
                            >
                                <component :is="sectionIcons[key]" :class="UI.SIDEBAR_ICON" />
                                <span class="truncate">{{ section.title }}</span>
                                <ChevronRightIcon v-if="activeTab === key" :class="UI.SIDEBAR_CHEVRON" />
                            </button>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <!-- Loading State -->
                        <div v-if="!isInitialized" :class="UI.CONTENT_CARD">
                            <div class="flex items-center justify-center py-12">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <span class="ml-3 text-gray-600 dark:text-gray-400">Loading design settings...</span>
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
                                    <XMarkIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Design Settings Form -->
                        <form @submit.prevent="updateDesignSettings" class="space-y-6">
                            <!-- Dynamic Design Sections -->
                            <div v-for="(section, sectionKey) in designSections" :key="sectionKey" v-show="activeTab === sectionKey" v-memo="[activeTab, form.settings]">
                                <div :class="UI.CONTENT_CARD">
                                    <div :class="UI.CONTENT_HEADER">
                                        <h3 :class="UI.CONTENT_TITLE">
                                            <component :is="sectionIcons[sectionKey]" class="w-5 h-5 mr-2" />
                                            {{ section.title }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ section.description }}</p>
                                    </div>
                                    <div :class="UI.CONTENT_BODY">
                                        <div :class="UI.CONTENT_GRID">
                                            <div v-for="setting in section.settings" :key="setting.key" :class="UI.SETTING_ITEM" v-memo="[form.settings[setting.key]]">
                                                <div class="flex-1">
                                                    <label :for="setting.key" :class="UI.SETTING_LABEL">
                                                        {{ setting.label }}
                                                    </label>
                                                    <p v-if="setting.description" :class="UI.SETTING_DESC">
                                                        {{ setting.description }}
                                                    </p>
                                                </div>
                                                <div class="flex-shrink-0 w-48">
                                                    <component
                                                        :is="getInputComponent(setting.type)"
                                                        :id="setting.key"
                                                        v-model="form.settings[setting.key]"
                                                        v-bind="setting.props || {}"
                                                        :class="getInputClasses(setting.type)"
                                                        @change="handleSettingChange(setting.key, $event)"
                                                    >
                                                        <option v-if="setting.type === 'select' && setting.options" 
                                                            v-for="option in setting.options" 
                                                            :key="option.value" 
                                                            :value="option.value"
                                                        >
                                                            {{ option.label }}
                                                        </option>
                                                    </component>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Section -->
                            <div v-if="activeTab === 'preview'" :class="UI.CONTENT_CARD">
                                <div :class="UI.CONTENT_HEADER">
                                    <h3 :class="UI.CONTENT_TITLE">
                                        <EyeIcon class="w-5 h-5 mr-2" />
                                        Live Preview
                                    </h3>
                                </div>
                                <div :class="UI.CONTENT_BODY">
                                    <div class="bg-gray-100 dark:bg-gray-700 rounded-xl p-6" :style="previewStyles">
                                        <div class="space-y-4">
                                            <div class="h-4 bg-current opacity-20 rounded" :style="{ backgroundColor: form.settings.primary_color }"></div>
                                            <div class="h-3 bg-current opacity-15 rounded w-3/4" :style="{ backgroundColor: form.settings.secondary_color }"></div>
                                            <div class="h-3 bg-current opacity-10 rounded w-1/2" :style="{ backgroundColor: form.settings.accent_color }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Bar -->
                            <div v-if="isInitialized" :class="UI.CARD">
                                <div class="flex items-center justify-between">
                                    <button type="button" @click="resetToDefaults" :disabled="form.processing" :class="getButtonClass('reset')">
                                        <ArrowPathIcon :class="UI.BUTTON_ICON" />
                                        Reset to Default
                                    </button>
                                    <div class="flex items-center space-x-3">
                                        <button type="button" @click="exportSettings" :class="getButtonClass('export')">
                                            <ArrowDownTrayIcon :class="UI.BUTTON_ICON" />
                                            Export
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
import { ref, onMounted, computed, shallowRef } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {
    CheckCircleIcon, ArrowPathIcon, CheckIcon, EyeIcon, ChevronRightIcon,
    PaintBrushIcon, SwatchIcon, PhotoIcon, Cog6ToothIcon, XMarkIcon,
    ExclamationTriangleIcon, ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

// Consolidated UI Configuration - DRY principle applied
const UI = {
    HEADER: 'bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700',
    CONTAINER: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
    MAIN: 'min-h-screen bg-gray-50 dark:bg-gray-900 py-6',
    GRID: 'lg:grid lg:grid-cols-12 gap-6',
    CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 mt-6',
    HEADER_CONTENT: 'flex items-center justify-between h-16',
    HEADER_LEFT: 'flex items-center space-x-4',
    HEADER_RIGHT: 'flex items-center space-x-2',
    HEADER_ICON: 'w-8 h-8 text-gray-600 dark:text-gray-400',
    HEADER_TITLE: 'text-2xl font-semibold text-gray-900 dark:text-white',
    HEADER_SUBTITLE: 'text-sm text-gray-500 dark:text-gray-400',
    FLASH_CONTAINER: 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-6',
    FLASH_SUCCESS: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-xl flex items-center',
    FLASH_ICON: 'w-5 h-5 mr-3 flex-shrink-0',
    SIDEBAR: 'bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-2 space-y-1',
    SIDEBAR_ITEM_BASE: 'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 w-full text-left',
    SIDEBAR_ITEM_ACTIVE: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm',
    SIDEBAR_ITEM_INACTIVE: 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50',
    SIDEBAR_ICON: 'w-5 h-5 mr-3 flex-shrink-0',
    SIDEBAR_CHEVRON: 'w-4 h-4 ml-auto text-blue-600 dark:text-blue-400',
    CONTENT_CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden',
    CONTENT_HEADER: 'px-6 py-4 border-b border-gray-200 dark:border-gray-700',
    CONTENT_TITLE: 'text-lg font-semibold text-gray-900 dark:text-white flex items-center',
    CONTENT_BODY: 'p-6',
    CONTENT_GRID: 'space-y-6',
    SETTING_ITEM: 'flex items-start justify-between bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4',
    SETTING_LABEL: 'block text-sm font-semibold text-gray-900 dark:text-white mb-1',
    SETTING_DESC: 'text-sm text-gray-600 dark:text-gray-400',
    BUTTON_BASE: 'inline-flex items-center justify-center font-medium rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200',
    BUTTON_ICON: 'w-4 h-4 mr-2',
    TRANSITION: {
        'enter-active-class': 'transition ease-out duration-300',
        'enter-from-class': 'opacity-0 transform scale-95',
        'enter-to-class': 'opacity-100 transform scale-100',
        'leave-active-class': 'transition ease-in duration-200',
        'leave-from-class': 'opacity-100 transform scale-100',
        'leave-to-class': 'opacity-0 transform scale-95'
    }
}

// Design sections configuration - optimized structure
const designSections = {
    colors: {
        title: 'Colors & Theme',
        description: 'Customize the color scheme and visual theme',
        settings: [
            { key: 'primary_color', label: 'Primary Color', type: 'color', description: 'Main brand color used throughout the course' },
            { key: 'secondary_color', label: 'Secondary Color', type: 'color', description: 'Supporting color for accents and highlights' },
            { key: 'accent_color', label: 'Accent Color', type: 'color', description: 'Color for buttons and interactive elements' },
            { key: 'background_color', label: 'Background Color', type: 'color', description: 'Main background color' },
            { key: 'text_color', label: 'Text Color', type: 'color', description: 'Primary text color' },
            { key: 'theme_mode', label: 'Theme Mode', type: 'select', options: [{ value: 'light', label: 'Light' }, { value: 'dark', label: 'Dark' }, { value: 'auto', label: 'Auto' }] }
        ]
    },
    typography: {
        title: 'Typography',
        description: 'Configure fonts and text styling',
        settings: [
            { key: 'font_family', label: 'Font Family', type: 'select', options: [{ value: 'inter', label: 'Inter' }, { value: 'roboto', label: 'Roboto' }, { value: 'poppins', label: 'Poppins' }] },
            { key: 'font_size', label: 'Base Font Size', type: 'range', props: { min: 12, max: 20, step: 1 } },
            { key: 'line_height', label: 'Line Height', type: 'range', props: { min: 1.2, max: 2.0, step: 0.1 } },
            { key: 'heading_weight', label: 'Heading Weight', type: 'select', options: [{ value: '400', label: 'Normal' }, { value: '500', label: 'Medium' }, { value: '600', label: 'Semibold' }, { value: '700', label: 'Bold' }] }
        ]
    },
    layout: {
        title: 'Layout & Spacing',
        description: 'Adjust layout structure and spacing',
        settings: [
            { key: 'container_width', label: 'Container Width', type: 'select', options: [{ value: 'sm', label: 'Small (640px)' }, { value: 'md', label: 'Medium (768px)' }, { value: 'lg', label: 'Large (1024px)' }, { value: 'xl', label: 'Extra Large (1280px)' }] },
            { key: 'border_radius', label: 'Border Radius', type: 'range', props: { min: 0, max: 20, step: 1 } },
            { key: 'card_shadow', label: 'Card Shadow', type: 'select', options: [{ value: 'none', label: 'None' }, { value: 'sm', label: 'Small' }, { value: 'md', label: 'Medium' }, { value: 'lg', label: 'Large' }] },
            { key: 'spacing_scale', label: 'Spacing Scale', type: 'range', props: { min: 0.8, max: 1.5, step: 0.1 } }
        ]
    },
    components: {
        title: 'Components',
        description: 'Customize individual component styles',
        settings: [
            { key: 'button_style', label: 'Button Style', type: 'select', options: [{ value: 'rounded', label: 'Rounded' }, { value: 'square', label: 'Square' }, { value: 'pill', label: 'Pill' }] },
            { key: 'input_style', label: 'Input Style', type: 'select', options: [{ value: 'outlined', label: 'Outlined' }, { value: 'filled', label: 'Filled' }, { value: 'underlined', label: 'Underlined' }] },
            { key: 'card_style', label: 'Card Style', type: 'select', options: [{ value: 'elevated', label: 'Elevated' }, { value: 'outlined', label: 'Outlined' }, { value: 'filled', label: 'Filled' }] }
        ]
    },
    preview: {
        title: 'Preview',
        description: 'See how your design changes look'
    }
}

// Icon mapping for sections
const sectionIcons = {
    colors: SwatchIcon,
    typography: 'H1',
    layout: Cog6ToothIcon,
    components: PhotoIcon,
    preview: EyeIcon
}

// Component mapping for input types
const inputComponents = {
    color: 'input',
    text: 'input',
    number: 'input',
    range: 'input',
    select: 'select',
    checkbox: 'input',
    textarea: 'textarea'
}

// Props and reactive state
const props = defineProps({
    currentSettings: { type: Object, default: () => ({}) }
})

// Reactive state - optimized with shallowRef
const activeTab = ref('colors')
const originalSettings = shallowRef({})
const form = useForm({ settings: {} })
const isInitialized = ref(false)
const message = ref({ type: '', text: '', show: false })

// Default settings
const defaultSettings = {
    primary_color: '#3b82f6',
    secondary_color: '#6366f1',
    accent_color: '#10b981',
    background_color: '#ffffff',
    text_color: '#1f2937',
    theme_mode: 'light',
    font_family: 'inter',
    font_size: 16,
    line_height: 1.6,
    heading_weight: '600',
    container_width: 'lg',
    border_radius: 8,
    card_shadow: 'md',
    spacing_scale: 1.0,
    button_style: 'rounded',
    input_style: 'outlined',
    card_style: 'elevated'
}

// Initialize settings
const initializeSettings = () => {
    const settings = { ...defaultSettings, ...props.currentSettings }
    form.settings = settings
    originalSettings.value = { ...settings }
    isInitialized.value = true
}

// Computed properties - optimized
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

const sidebarClasses = computed(() => {
    const classes = {}
    Object.keys(designSections).forEach(key => {
        classes[key] = key === activeTab.value 
            ? `${UI.SIDEBAR_ITEM_BASE} ${UI.SIDEBAR_ITEM_ACTIVE}`
            : `${UI.SIDEBAR_ITEM_BASE} ${UI.SIDEBAR_ITEM_INACTIVE}`
    })
    return classes
})

const previewStyles = computed(() => ({
    fontFamily: form.settings.font_family,
    fontSize: `${form.settings.font_size}px`,
    lineHeight: form.settings.line_height,
    backgroundColor: form.settings.background_color,
    color: form.settings.text_color,
    borderRadius: `${form.settings.border_radius}px`
}))

// Utility functions - optimized
const setActiveTab = (tabKey) => { activeTab.value = tabKey }

const getInputComponent = (type) => inputComponents[type] || 'input'

const getInputClasses = (type) => {
    const base = 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500'
    return type === 'color' ? `${base} h-10` : base
}

const getButtonClass = (type) => {
    const baseClasses = `${UI.BUTTON_BASE} px-6 py-2.5 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed shadow-sm`
    const variants = {
        reset: `${baseClasses} text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/30 focus:ring-red-500`,
        export: `${baseClasses} text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-blue-500`,
        save: `${baseClasses} text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500`
    }
    return variants[type] || variants.export
}

// Event handlers - optimized
const handleSettingChange = (key, event) => {
    const value = event.target ? event.target.value : event
    form.settings[key] = value
}

const updateDesignSettings = () => {
    form.post(route('courses.design.update'), {
        onSuccess: () => {
            Object.assign(originalSettings.value, form.settings)
            showMessage('success', 'Design settings updated successfully!')
        },
        onError: (errors) => {
            const errorMsg = Object.values(errors).flat().join(', ') || 'Failed to update design settings'
            showMessage('error', errorMsg)
        }
    })
}

const resetToDefaults = () => {
    if (confirm('Are you sure you want to reset all design settings to default values?')) {
        form.settings = { ...defaultSettings }
        showMessage('success', 'Design settings reset to defaults!')
    }
}

const exportSettings = () => {
    const dataStr = JSON.stringify(form.settings, null, 2)
    const dataBlob = new Blob([dataStr], { type: 'application/json' })
    const url = URL.createObjectURL(dataBlob)
    const link = document.createElement('a')
    link.href = url
    link.download = 'design-settings.json'
    link.click()
    URL.revokeObjectURL(url)
    showMessage('success', 'Design settings exported successfully!')
}

const showMessage = (type, text) => {
    message.value = { type, text, show: true }
    setTimeout(() => { message.value.show = false }, 5000)
}

// Initialize on mount
onMounted(() => {
    initializeSettings()
})
</script>

<style scoped>
.form-field {
    @apply space-y-1;
}

input[type="color"] {
    @apply cursor-pointer;
}

input[type="range"] {
    @apply accent-blue-600;
}
</style>