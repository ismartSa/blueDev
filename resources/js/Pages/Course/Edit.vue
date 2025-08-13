<script setup>
import { computed, watchEffect, ref, shallowRef } from 'vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import Modal from '@/Components/Modal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import SelectInput from '@/Components/SelectInput.vue'
import TextInput from '@/Components/TextInput.vue'
import {
    CheckCircleIcon, ArrowPathIcon, CheckIcon, EyeIcon, ChevronRightIcon,
    PaintBrushIcon, SwatchIcon, PhotoIcon, Cog6ToothIcon, XMarkIcon,
    ExclamationTriangleIcon, ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

// Consolidated UI Configuration - DRY principle applied (Enhanced from DesignSettings)
const UI = {
    // Modal specific styles
    MODAL_HEADER: 'text-lg font-medium text-slate-900 dark:text-slate-100',
    MODAL_CONTAINER: 'p-6',
    
    // Layout and structure
    HEADER: 'bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700',
    CONTAINER: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
    MAIN: 'min-h-screen bg-gray-50 dark:bg-gray-900 py-6',
    GRID: 'lg:grid lg:grid-cols-12 gap-6',
    CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 mt-6',
    
    // Form styling
    FORM_CONTAINER: 'my-6 space-y-4',
    FIELD_CONTAINER: 'space-y-1',
    INPUT_BASE: 'mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200',
    
    // Button styling
    BUTTON_CONTAINER: 'flex justify-end space-x-3',
    BUTTON_PROCESSING: 'opacity-25',
    BUTTON_BASE: 'inline-flex items-center justify-center font-medium rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200',
    BUTTON_ICON: 'w-4 h-4 mr-2',
    
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
    
    // Content areas
    CONTENT_CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden',
    CONTENT_HEADER: 'px-6 py-4 border-b border-gray-200 dark:border-gray-700',
    CONTENT_TITLE: 'text-lg font-semibold text-gray-900 dark:text-white flex items-center',
    CONTENT_BODY: 'p-6',
    CONTENT_GRID: 'space-y-6',
    
    // Setting items
    SETTING_ITEM: 'flex items-start justify-between bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4',
    SETTING_LABEL: 'block text-sm font-semibold text-gray-900 dark:text-white mb-1',
    SETTING_DESC: 'text-sm text-gray-600 dark:text-gray-400',
    
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

// Form fields configuration - Dynamic rendering approach (Enhanced structure)
const FORM_FIELDS = [
    { 
        key: 'name', 
        type: 'text', 
        component: 'TextInput', 
        required: true,
        label: 'Full Name',
        description: 'Enter the full name of the user'
    },
    { 
        key: 'email', 
        type: 'email', 
        component: 'TextInput', 
        required: false,
        label: 'Email Address',
        description: 'Valid email address for communication'
    },
    { 
        key: 'password', 
        type: 'password', 
        component: 'TextInput', 
        required: false,
        label: 'Password',
        description: 'Leave blank to keep current password'
    },
    { 
        key: 'password_confirmation', 
        type: 'password', 
        component: 'TextInput', 
        required: false,
        label: 'Confirm Password',
        description: 'Must match the password above'
    },
    { 
        key: 'role', 
        type: 'select', 
        component: 'SelectInput', 
        required: true, 
        useDataSet: true,
        label: 'User Role',
        description: 'Select the appropriate role for this user'
    },
    { 
        key: 'title', 
        type: 'text', 
        component: 'TextInput', 
        required: false,
        label: 'Course Title',
        description: 'Enter a descriptive title for the course'
    },
    { 
        key: 'description', 
        type: 'textarea', 
        component: 'TextInput', 
        required: false,
        label: 'Course Description',
        description: 'Provide a detailed description of the course content'
    },
    { 
        key: 'price', 
        type: 'number', 
        component: 'TextInput', 
        required: false,
        label: 'Course Price',
        description: 'Set the price for this course (leave 0 for free)'
    },
    { 
        key: 'image', 
        type: 'text', 
        component: 'TextInput', 
        required: false,
        label: 'Course Image URL',
        description: 'URL to the course thumbnail image'
    }
]

// Component mapping - Enhanced with input types
const COMPONENTS = { 
    TextInput, 
    SelectInput,
    input: 'input',
    select: 'select',
    textarea: 'textarea'
}

// Input component mapping for different types
const inputComponents = {
    color: 'input',
    text: 'input',
    email: 'input',
    password: 'input',
    number: 'input',
    range: 'input',
    select: 'select',
    checkbox: 'input',
    textarea: 'textarea'
}

const props = defineProps({
    show: Boolean,
    title: String,
    user: Object,
    roles: Object,
})

const emit = defineEmits(['close'])

// Reactive state - optimized with shallowRef (Enhanced from DesignSettings)
const originalData = shallowRef({})
const isInitialized = ref(false)
const message = ref({ type: '', text: '', show: false })

// Initialize form with default values
const form = useForm(Object.fromEntries(
    FORM_FIELDS.map(field => [field.key, field.type === 'number' ? 0 : ''])
))

// Computed properties for better performance (Enhanced)
const rolesOptions = computed(() => 
    props.roles?.map(role => ({ label: role.name, value: role.name })) || []
)

const visibleFields = computed(() => 
    FORM_FIELDS.filter(field => {
        // Show course-specific fields only when editing courses
        const courseFields = ['title', 'description', 'price', 'image']
        return !courseFields.includes(field.key) || props.title?.includes('Course')
    })
)

// Enhanced computed properties from DesignSettings pattern
const hasChanges = computed(() => 
    Object.keys(form.data()).some(key => form[key] !== originalData.value[key])
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

// Enhanced update function with better error handling
const update = () => {
    form.put(route('user.update', props.user?.id), {
        preserveScroll: true,
        onSuccess: () => { 
            emit('close')
            form.reset()
            showMessage('success', 'Changes saved successfully!')
        },
        onError: (errors) => {
            showMessage('error', 'Please check the form for errors.')
        },
        onFinish: () => {
            isInitialized.value = true
        },
    })
}

// Initialize data function
const initializeData = () => {
    if (props.show && props.user) {
        const userData = {
            name: props.user.name || '',
            email: props.user.email || '',
            role: props.user.roles?.[0]?.name || '',
            title: props.user.title || '',
            description: props.user.description || '',
            price: props.user.price || 0,
            image: props.user.image || ''
        }
        
        Object.assign(form, userData)
        originalData.value = { ...userData }
        form.clearErrors()
        isInitialized.value = true
    }
}

// Enhanced watcher with initialization
watchEffect(() => {
    initializeData()
})

// Message handling function
const showMessage = (type, text) => {
    message.value = { type, text, show: true }
    setTimeout(() => {
        message.value.show = false
    }, 3000)
}

// Enhanced helper functions with DesignSettings patterns
const getFieldProps = (field) => ({
    id: field.key,
    type: field.type,
    class: getInputClasses(field.type),
    required: field.required,
    placeholder: lang().placeholder?.[field.key] || field.label,
    error: form.errors[field.key],
    ...(field.useDataSet && { dataSet: rolesOptions.value })
})

const getFieldComponent = (field) => COMPONENTS[field.component]

// Input styling function from DesignSettings pattern
const getInputClasses = (type) => {
    const baseClasses = UI.INPUT_BASE
    const typeSpecificClasses = {
        color: 'h-10 w-20 rounded-lg border-2 cursor-pointer',
        range: 'w-full',
        select: 'cursor-pointer',
        textarea: 'min-h-[100px] resize-vertical'
    }
    
    return `${baseClasses} ${typeSpecificClasses[type] || ''}`
}

// Input component getter function
const getInputComponent = (type) => {
    return inputComponents[type] || 'input'
}

// Button class generator
const getButtonClass = (variant) => {
    const variants = {
        save: 'bg-blue-600 hover:bg-blue-700 text-white px-6 py-2',
        reset: 'bg-gray-500 hover:bg-gray-600 text-white px-4 py-2',
        export: 'bg-green-600 hover:bg-green-700 text-white px-4 py-2'
    }
    
    return `${UI.BUTTON_BASE} ${variants[variant] || variants.save}`
}
</script>

<template>
    <section class="space-y-6">
        <Modal :show="props.show" @close="emit('close')">
            <!-- Flash Messages -->
            <Transition v-bind="UI.TRANSITION">
                <div v-if="$page.props.flash?.success" :class="UI.FLASH_CONTAINER">
                    <div :class="UI.FLASH_SUCCESS">
                        <CheckCircleIcon :class="UI.FLASH_ICON" />
                        <span class="font-medium">{{ $page.props.flash.success }}</span>
                    </div>
                </div>
            </Transition>
            
            <!-- Enhanced Message Display -->
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
            
            <form :class="UI.MODAL_CONTAINER" @submit.prevent="update">
                <!-- Enhanced Modal Header -->
                <div :class="UI.CONTENT_HEADER">
                    <h2 :class="UI.MODAL_HEADER">
                        <Cog6ToothIcon :class="UI.BUTTON_ICON" />
                        {{ lang().label.edit }} {{ props.title }}
                    </h2>
                    <div v-if="form.processing" class="flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></div>
                        <span :class="UI.HEADER_SUBTITLE">Processing...</span>
                    </div>
                </div>
                
                <!-- Enhanced Dynamic Form Fields -->
                <div :class="UI.CONTENT_BODY">
                    <div :class="UI.CONTENT_GRID">
                        <div 
                            v-for="field in visibleFields" 
                            :key="field.key" 
                            :class="UI.SETTING_ITEM"
                            v-memo="[form[field.key], form.errors[field.key]]"
                        >
                            <div class="flex-1">
                                 <InputLabel 
                                     :for="field.key" 
                                     :class="UI.SETTING_LABEL"
                                     :value="field.label || lang().label?.[field.key] || field.key" 
                                 />
                                 <p v-if="field.description || lang().description?.[field.key]" :class="UI.SETTING_DESC">
                                     {{ field.description || lang().description?.[field.key] }}
                                 </p>
                             </div>
                            <div class="flex-shrink-0 w-48">
                                <component
                                    :is="getFieldComponent(field)"
                                    v-model="form[field.key]"
                                    v-bind="getFieldProps(field)"
                                />
                                <InputError 
                                    class="mt-2 text-xs" 
                                    :message="form.errors[field.key]" 
                                />
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Action Buttons -->
                <div :class="UI.CONTENT_HEADER">
                    <div :class="UI.BUTTON_CONTAINER">
                        <SecondaryButton
                            :disabled="form.processing"
                            @click="emit('close')"
                            :class="UI.BUTTON_BASE"
                        >
                            <XMarkIcon :class="UI.BUTTON_ICON" />
                            {{ lang().button.close }}
                        </SecondaryButton>
                        <PrimaryButton
                            :class="[UI.BUTTON_BASE, { [UI.BUTTON_PROCESSING]: form.processing }]"
                            :disabled="form.processing"
                            @click="update"
                        >
                            <component :is="form.processing ? 'ArrowPathIcon' : 'CheckIcon'" 
                                :class="[UI.BUTTON_ICON, { 'animate-spin': form.processing }]" />
                            {{ form.processing ? lang().button.save + '...' : lang().button.save }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </Modal>
    </section>
</template>
