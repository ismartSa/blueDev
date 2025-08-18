<script setup>
import { reactive, watch, computed, ref, nextTick, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import SelectInput from '@/Components/SelectInput.vue'
import FileInput from '@/Components/FileInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { XMarkIcon, CheckCircleIcon, ExclamationTriangleIcon, InformationCircleIcon, QuestionMarkCircleIcon } from '@heroicons/vue/24/solid'
import StatusSwitch from '@/Components/UI/StatusSwitch.vue'

// Component registration
const components = {
    Modal,
    InputLabel,
    TextInput,
    TextArea,
    SelectInput,
    FileInput,
    PrimaryButton,
    SecondaryButton,
    StatusSwitch
}

// Props and emits
const props = defineProps({
    show: { type: Boolean, default: false },
    mode: { type: String, default: 'create', validator: value => ['create', 'edit'].includes(value) },
    course: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    title: { type: String, default: '' },
    maxWidth: { type: String, default: '2xl' },
    closeable: { type: Boolean, default: true }
})

const emit = defineEmits(['close', 'success'])

// Dynamic field configuration
const FIELD_CONFIG = {
    title: { label: 'Course Title', component: 'TextInput', required: true, maxLength: 100, help: 'Choose a clear, descriptive title' },
    description: { label: 'Description', component: 'TextArea', rows: 4, maxLength: 1000, help: 'Describe what students will learn' },
    price: { label: 'Price ($)', component: 'TextInput', type: 'number', step: '0.01', min: '0', editOnly: true, help: 'Set to 0 for free courses' },
    category_id: { label: 'Category', component: 'SelectInput', help: 'Select relevant category for discoverability' },
    status: { label: 'Status', component: 'StatusSwitch', required: true, help: 'Draft: Hidden, Active: Published' },
    level: { label: 'Level', component: 'SelectInput', editOnly: true, help: 'Help students choose appropriate difficulty' },
    duration: { label: 'Duration (hours)', component: 'TextInput', type: 'number', min: '0.5', step: '0.5', editOnly: true, help: 'Estimated completion time' },
    language: { label: 'Language', component: 'SelectInput', editOnly: true, help: 'Primary course language' },
    thumbnail: { label: 'Thumbnail', component: 'FileInput', accept: 'image/*', editOnly: true, help: 'Upload course image (max 2MB)' }
}

// Field options
const FIELD_OPTIONS = {
    status: { draft: false, active: true }, // StatusSwitch uses boolean values
    level: [{ value: 'beginner', label: 'Beginner' }, { value: 'intermediate', label: 'Intermediate' }, { value: 'advanced', label: 'Advanced' }],
    language: [{ value: 'en', label: 'English' }, { value: 'ar', label: 'Arabic' }, { value: 'fr', label: 'French' }]
}

// Form initialization
const getDefaultData = () => ({ title: '', description: '', price: '', status: false, category_id: '', level: '', duration: '', language: '', thumbnail: null })
const form = useForm(getDefaultData())

// Reactive state
const state = reactive({
    isSubmitting: false,
    submitSuccess: false,
    validation: {},
    activeTooltip: null,
    autoSaveTimer: null,
    lastAutoSave: null
})

// Computed properties
const visibleFields = computed(() => 
    Object.entries(FIELD_CONFIG).filter(([key, config]) => !config.editOnly || props.mode === 'edit')
)

const isFormValid = computed(() => 
    visibleFields.value.every(([key, config]) => !config.required || form[key])
)

const modalTitle = computed(() => props.title || `${props.mode === 'create' ? 'Create' : 'Edit'} Course`)

// Dynamic field options getter
const getFieldOptions = (key) => {
    if (key === 'category_id') {
        return [{ value: '', label: 'Select category' }, ...props.categories.map(cat => ({ value: cat.id, label: cat.name }))]
    }
    return FIELD_OPTIONS[key] ? [{ value: '', label: `Select ${key}` }, ...FIELD_OPTIONS[key]] : []
}

// Validation logic
const validateField = (key, value) => {
    const config = FIELD_CONFIG[key]
    if (!config) return true
    
    let isValid = true, message = '', type = 'neutral'
    
    if (config.required && (!value || value.toString().trim() === '')) {
        isValid = false
        message = `${config.label} is required`
        type = 'error'
    } else if (config.maxLength && value && value.toString().length > config.maxLength) {
        isValid = false
        message = `${config.label} cannot exceed ${config.maxLength} characters`
        type = 'error'
    } else if (config.min !== undefined && value !== '' && parseFloat(value) < parseFloat(config.min)) {
        isValid = false
        message = `${config.label} must be at least ${config.min}`
        type = 'error'
    } else if (config.required && value) {
        type = 'success'
    }
    
    state.validation[key] = { valid: isValid, message, type }
    return isValid
}

// File handling
const handleFileChange = (event) => {
    const file = event.target.files[0]
    if (!file) return
    
    if (file.size > 2 * 1024 * 1024) {
        state.validation.thumbnail = { valid: false, message: 'File must be less than 2MB', type: 'error' }
        return
    }
    
    form.thumbnail = file
    state.validation.thumbnail = { valid: true, message: `Selected: ${file.name}`, type: 'success' }
}

// Auto-save functionality
const getAutoSaveKey = () => `course-form-${props.mode}-${props.course?.id || 'new'}`

const saveFormData = () => {
    if (props.mode === 'create') {
        try {
            const data = {}
            visibleFields.value.forEach(([key]) => {
                if (key !== 'thumbnail' && form[key] !== '') data[key] = form[key]
            })
            // Only save if there's actual data
            if (Object.keys(data).length > 0) {
                localStorage.setItem(getAutoSaveKey(), JSON.stringify({ data, timestamp: Date.now() }))
                state.lastAutoSave = new Date().toLocaleTimeString()
            }
        } catch (error) {
            console.warn('Failed to auto-save form data:', error)
        }
    }
}

const loadSavedData = () => {
    if (props.mode === 'create') {
        const saved = localStorage.getItem(getAutoSaveKey())
        if (saved) {
            try {
                const { data, timestamp } = JSON.parse(saved)
                if (Date.now() - timestamp < 24 * 60 * 60 * 1000) {
                    Object.assign(form, data)
                    state.lastAutoSave = new Date(timestamp).toLocaleTimeString()
                }
            } catch (e) {
                console.warn('Failed to restore auto-saved data:', e)
            }
        }
    }
}

const debouncedAutoSave = () => {
    if (state.autoSaveTimer) clearTimeout(state.autoSaveTimer)
    state.autoSaveTimer = setTimeout(saveFormData, 2000)
}

// Form submission
const submit = async () => {
    // Validate all fields
    let hasErrors = false
    visibleFields.value.forEach(([key]) => {
        if (!validateField(key, form[key])) hasErrors = true
    })
    
    if (hasErrors) return
    
    state.isSubmitting = true
    const isCreate = props.mode === 'create'
    const method = isCreate ? 'post' : 'put'
    const routeName = `dashboard.courses.${isCreate ? 'store' : 'update'}`
    const routeParams = isCreate ? [] : [props.course.id]
    
    // Convert boolean status back to string for API
    const formData = { ...form.data() }
    if (typeof formData.status === 'boolean') {
        formData.status = formData.status ? 'active' : 'draft'
    }
    
    // Create a temporary form with converted data
    const submitForm = useForm(formData)
    
    submitForm[method](route(routeName, ...routeParams), {
        onSuccess: () => {
            state.isSubmitting = false
            state.submitSuccess = true
            if (isCreate) localStorage.removeItem(getAutoSaveKey())
            // Update the original form's processing state
            form.processing = false
            emit('success', `Course ${isCreate ? 'created' : 'updated'} successfully!`)
            setTimeout(() => state.submitSuccess = false, 3000)
        },
        onError: async (errors) => {
            state.isSubmitting = false
            // Update the original form's processing state
            form.processing = false
            // Handle server validation errors
            Object.keys(errors).forEach(key => {
                const errorMessage = Array.isArray(errors[key]) ? errors[key][0] : errors[key]
                state.validation[key] = { valid: false, message: errorMessage, type: 'error' }
            })
            // Focus on first error field
            await nextTick()
            const firstErrorField = Object.keys(errors)[0]
            if (firstErrorField) {
                const element = document.getElementById(firstErrorField)
                element?.focus()
            }
        },
        onFinish: () => state.isSubmitting = false
    })
}

const closeModal = () => {
    state.isSubmitting = false
    state.submitSuccess = false
    state.activeTooltip = null
    if (state.autoSaveTimer) {
        clearTimeout(state.autoSaveTimer)
        state.autoSaveTimer = null
    }
    // Clear validation state
    state.validation = {}
    emit('close')
}

// Watchers
watch(() => props.show, (show) => {
    if (show) {
        if (props.mode === 'create') {
            form.reset()
            Object.assign(form, getDefaultData())
            loadSavedData()
        } else if (props.course) {
            // Reset form first, then populate with course data
            form.reset()
            const courseData = { ...getDefaultData(), ...props.course }
            // Convert status string to boolean for StatusSwitch
            if (courseData.status) {
                courseData.status = courseData.status === 'active'
            }
            // Ensure all form fields are properly set
            Object.keys(courseData).forEach(key => {
                if (form.hasOwnProperty(key)) {
                    form[key] = courseData[key]
                }
            })
        }
        state.validation = {}
        form.clearErrors()
        // Trigger validation for all fields in edit mode
        if (props.mode === 'edit') {
            nextTick(() => {
                visibleFields.value.forEach(([key]) => {
                    validateField(key, form[key])
                })
            })
        }
        // Focus first input field when modal opens
        nextTick(() => {
            const firstField = visibleFields.value[0]?.[0]
            if (firstField) {
                const element = document.getElementById(firstField)
                element?.focus()
            }
        })
    }
})

watch(() => form.data(), () => {
    if (props.show && props.mode === 'create') debouncedAutoSave()
}, { deep: true })

// Watch for course prop changes in edit mode
watch(() => props.course, (newCourse) => {
    if (props.show && props.mode === 'edit' && newCourse) {
        form.reset()
        const courseData = { ...getDefaultData(), ...newCourse }
        // Convert status string to boolean for StatusSwitch
        if (courseData.status) {
            courseData.status = courseData.status === 'active'
        }
        Object.keys(courseData).forEach(key => {
            if (form.hasOwnProperty(key)) {
                form[key] = courseData[key]
            }
        })
        state.validation = {}
        form.clearErrors()
        nextTick(() => {
            visibleFields.value.forEach(([key]) => {
                validateField(key, form[key])
            })
        })
    }
}, { deep: true })

// Cleanup
onUnmounted(() => {
    if (state.autoSaveTimer) clearTimeout(state.autoSaveTimer)
})

// Helper functions for dynamic rendering
const getValidationIcon = (key) => {
    const validation = state.validation[key]
    if (validation?.type === 'error') return ExclamationTriangleIcon
    if (validation?.type === 'success') return CheckCircleIcon
    return null
}

const getValidationClasses = (key) => {
    const validation = state.validation[key]
    const base = 'transition-all duration-200'
    if (validation?.type === 'error') return `${base} border-red-300 focus:border-red-500 focus:ring-red-500 bg-red-50`
    if (validation?.type === 'success') return `${base} border-green-300 focus:border-green-500 focus:ring-green-500 bg-green-50`
    return `${base} border-gray-300 focus:border-blue-500 focus:ring-blue-500`
}

const getSubmitButtonText = () => {
    if (state.isSubmitting || form.processing) return props.mode === 'create' ? 'Creating...' : 'Updating...'
    if (state.submitSuccess) return props.mode === 'create' ? 'Created!' : 'Updated!'
    return props.mode === 'create' ? 'Create Course' : 'Update Course'
}
</script>

<template>
    <Modal :show="show" @close="closeModal" :max-width="maxWidth" :closeable="closeable">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ modalTitle }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ mode === 'create' ? 'Fill in the details to create a new course' : 'Update the course information' }}
                    </p>
                </div>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors rounded-md p-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>
            
            <!-- Form -->
            <form @submit.prevent="submit" @keydown.esc="closeModal" class="flex-1 overflow-y-auto">
                <div class="p-6 space-y-6">
                    <!-- Dynamic Field Rendering -->
                    <div class="grid grid-cols-1 gap-6">
                        <div v-for="[key, config] in visibleFields" :key="key" class="space-y-2 relative">
                            <!-- Label with tooltip -->
                            <div class="flex items-center gap-2">
                                <InputLabel :for="key" :value="config.label" class="font-medium" :class="{ 'text-red-600': state.validation[key]?.type === 'error' }" />
                                <div v-if="config.help" class="relative">
                                    <button type="button" @mouseenter="state.activeTooltip = key" @mouseleave="state.activeTooltip = null" @focus="state.activeTooltip = key" @blur="state.activeTooltip = null" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 rounded">
                                        <QuestionMarkCircleIcon class="w-4 h-4" />
                                    </button>
                                    <!-- Tooltip -->
                                    <div v-if="state.activeTooltip === key" class="absolute left-0 top-6 z-50 w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-lg transform -translate-x-1/2">
                                        {{ config.help }}
                                        <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                                    </div>
                                </div>
                                <component v-if="getValidationIcon(key)" :is="getValidationIcon(key)" class="w-4 h-4" :class="{ 'text-red-500': state.validation[key]?.type === 'error', 'text-green-500': state.validation[key]?.type === 'success' }" />
                            </div>
                            
                            <!-- Input Field -->
                            <StatusSwitch
                                v-if="config.component === 'StatusSwitch'"
                                :id="key"
                                v-model="form[key]"
                                variant="primary"
                                show-status
                                show-icons
                                on-text="Active"
                                off-text="Draft"
                                class="block w-full"
                                :class="getValidationClasses(key)"
                                @change="validateField(key, form[key])"
                            />
                            <component 
                                v-else
                                :is="config.component"
                                :id="key"
                                v-model="form[key]"
                                v-bind="config"
                                class="block w-full"
                                :class="getValidationClasses(key)"
                                @blur="validateField(key, form[key])"
                                @input="config.component !== 'FileInput' ? validateField(key, form[key]) : null"
                                @change="config.component === 'FileInput' ? handleFileChange($event) : null"
                            >
                                <option v-if="config.component === 'SelectInput'" v-for="option in getFieldOptions(key)" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </component>
                            
                            <!-- Help Text -->
                            <div v-if="config.help && !state.validation[key]?.message" class="p-2 bg-blue-50 rounded-md border border-blue-200">
                                <p class="text-xs text-blue-700 flex items-start gap-2">
                                    <InformationCircleIcon class="w-4 h-4 mt-0.5 flex-shrink-0" />
                                    <span>{{ config.help }}</span>
                                </p>
                            </div>
                            
                            <!-- Validation Message -->
                            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0">
                                <div v-if="state.validation[key]?.message || form.errors[key]" class="flex items-start gap-2">
                                    <component :is="getValidationIcon(key)" class="w-4 h-4 mt-0.5 flex-shrink-0" :class="{ 'text-red-500': state.validation[key]?.type === 'error', 'text-green-500': state.validation[key]?.type === 'success' }" />
                                    <span class="text-sm" :class="{ 'text-red-600': state.validation[key]?.type === 'error', 'text-green-600': state.validation[key]?.type === 'success' }">
                                        {{ state.validation[key]?.message || form.errors[key] }}
                                    </span>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="sticky bottom-0 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            <span v-if="mode === 'create' && state.lastAutoSave" class="flex items-center gap-2 text-blue-600">
                                Auto-saved: {{ state.lastAutoSave }}
                            </span>
                        </div>
                        <div class="flex gap-3">
                            <SecondaryButton @click="closeModal" type="button">Cancel</SecondaryButton>
                            <PrimaryButton type="submit" :disabled="!isFormValid || state.isSubmitting || form.processing" class="flex items-center gap-2">
                                <svg v-if="state.isSubmitting || form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <CheckCircleIcon v-else class="w-4 h-4" />
                                <span>{{ getSubmitButtonText() }}</span>
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>