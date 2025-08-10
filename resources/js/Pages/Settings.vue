<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Project Settings
                </h2>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <span class="w-2 h-2 rounded-full" :class="saveStatus.color"></span>
                    <span>{{ saveStatus.text }}</span>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <Transition v-bind="transitionProps">
                    <div v-if="$page.props.flash.success" :class="alertClasses">
                        <CheckCircleIcon class="w-5 h-5 mr-2" />
                        {{ $page.props.flash.success }}
                    </div>
                </Transition>

                <!-- Settings Container -->
                <div :class="containerClasses">
                    <!-- Tab Navigation -->
                    <div :class="tabContainerClasses">
                        <nav class="flex space-x-8 px-6" aria-label="Tabs">
                            <button
                                v-for="(groupName, groupKey) in groups"
                                :key="groupKey"
                                @click="activeTab = groupKey"
                                :class="[baseTabClasses, tabClasses(groupKey)]"
                            >
                                <component :is="tabIcons[groupKey]" class="w-5 h-5 mr-2" />
                                {{ groupName }}
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <form @submit.prevent="updateSettings" class="p-6">
                        <div class="space-y-6">
                            <div v-for="(groupSettings, groupKey) in settings" :key="groupKey" v-show="activeTab === groupKey">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div v-for="setting in groupSettings" :key="setting.key" class="space-y-3">
                                        <div class="flex-1">
                                            <label :for="setting.key" :class="labelClasses">
                                                {{ formatLabel(setting.key) }}
                                            </label>
                                            <p v-if="setting.description" :class="descriptionClasses">
                                                {{ setting.description }}
                                            </p>
                                        </div>

                                        <!-- Dynamic Input Component -->
                                        <component
                                            :is="getInputComponent(setting.type)"
                                            :setting="setting"
                                            :modelValue="form.settings[setting.key]"
                                            @update:modelValue="form.settings[setting.key] = $event"
                                            @file-upload="handleFileUpload"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sticky Action Bar -->
                        <div :class="actionBarClasses">
                            <div class="flex items-center justify-between">
                                <button type="button" @click="resetSettings" :disabled="form.processing" :class="resetButtonClasses">
                                    <ArrowPathIcon class="w-4 h-4 mr-2" />
                                    Reset to Default
                                </button>

                                <div class="flex items-center space-x-3">
                                    <button type="button" @click="previewChanges" :class="previewButtonClasses">
                                        <EyeIcon class="w-4 h-4 mr-2" />
                                        Preview
                                    </button>
                                    <button type="submit" :disabled="form.processing || !hasChanges" :class="saveButtonClasses">
                                        <component :is="form.processing ? 'ArrowPathIcon' : 'CheckIcon'" :class="['w-4 h-4 mr-2', { 'animate-spin': form.processing }]" />
                                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StringInput from '@/Components/Settings/StringInput.vue'
import BooleanInput from '@/Components/Settings/BooleanInput.vue'
import IntegerInput from '@/Components/Settings/IntegerInput.vue'
import FileInput from '@/Components/Settings/FileInput.vue'
import {
    CheckCircleIcon, ArrowPathIcon, CheckIcon, EyeIcon,
    BuildingOfficeIcon, PhoneIcon, ShareIcon, CogIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    settings: Object,
    groups: Object
})

// Constants
const TAB_ICONS = {
    branding: BuildingOfficeIcon,
    contact: PhoneIcon,
    social: ShareIcon,
    system: CogIcon
}

const INPUT_COMPONENTS = {
    string: 'StringInput',
    boolean: 'BooleanInput',
    integer: 'IntegerInput',
    file: 'FileInput'
}

const BUTTON_BASE = 'inline-flex items-center font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200'
const INPUT_BASE = 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors duration-200'

// State
const activeTab = ref(Object.keys(props.groups)[0])
const originalSettings = ref({})
const fileUploads = ref({})
const form = useForm({ settings: {} })

// Initialize form
onMounted(() => {
    Object.values(props.settings).flat().forEach(setting => {
        form.settings[setting.key] = setting.value
        originalSettings.value[setting.key] = setting.value
    })
})

// Computed properties
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
    
    if (form.processing) return states.processing
    if (form.recentlySuccessful) return states.success
    if (hasChanges.value) return states.changed
    return states.default
})

// Style classes
const transitionProps = computed(() => ({
    'enter-active-class': 'transition ease-out duration-300',
    'enter-from-class': 'opacity-0 transform scale-95',
    'enter-to-class': 'opacity-100 transform scale-100',
    'leave-active-class': 'transition ease-in duration-200',
    'leave-from-class': 'opacity-100 transform scale-100',
    'leave-to-class': 'opacity-0 transform scale-95'
}))

const alertClasses = 'mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-center'
const containerClasses = 'bg-white dark:bg-gray-800 shadow-xl rounded-xl overflow-hidden'
const tabContainerClasses = 'border-b border-gray-200 dark:border-gray-700'
const baseTabClasses = 'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200'
const labelClasses = 'block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1'
const descriptionClasses = 'text-xs text-gray-500 dark:text-gray-400 mb-2'
const actionBarClasses = 'sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 -mx-6 px-6 py-4 mt-8'

const resetButtonClasses = `${BUTTON_BASE} px-4 py-2 border border-red-300 text-sm text-red-700 bg-red-50 hover:bg-red-100 focus:ring-red-500 disabled:opacity-50`
const previewButtonClasses = `${BUTTON_BASE} px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-indigo-500`
const saveButtonClasses = `${BUTTON_BASE} px-6 py-2 border border-transparent text-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed`

const tabIcons = TAB_ICONS

const tabClasses = (groupKey) => {
    const isActive = activeTab.value === groupKey
    return isActive 
        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
}

const getInputComponent = (type) => INPUT_COMPONENTS[type] || 'StringInput'

// Form handlers
const updateSettings = () => {
    const formData = new FormData()
    
    // Build settings array with optimized mapping
    const settingsArray = Object.entries(form.settings)
        .map(([key, value]) => {
            const setting = findSettingByKey(key)
            return setting ? { key, value, type: setting.type } : null
        })
        .filter(Boolean)

    formData.append('settings', JSON.stringify(settingsArray))
    
    // Append file uploads
    Object.entries(fileUploads.value).forEach(([key, file]) => {
        if (file) formData.append(`file_${key}`, file)
    })

    form.post(route('admin.settings.update'), {
        data: formData,
        forceFormData: true,
        onSuccess: () => {
            Object.assign(originalSettings.value, form.settings)
            fileUploads.value = {}
        }
    })
}

const handleFileUpload = (event, key) => {
    const file = event.target.files?.[0]
    if (!file) return
    
    fileUploads.value[key] = file
    const reader = new FileReader()
    reader.onload = (e) => form.settings[key] = e.target.result
    reader.readAsDataURL(file)
}

const resetSettings = () => {
    if (confirm('Are you sure you want to reset all settings to default values?')) {
        form.post(route('admin.settings.reset'), {
            onSuccess: () => location.reload()
        })
    }
}

const previewChanges = () => window.open('/', '_blank')

// Utility functions
const findSettingByKey = (key) => {
    return Object.values(props.settings)
        .flat()
        .find(setting => setting.key === key) || null
}

const formatLabel = (key) => key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
</script>
