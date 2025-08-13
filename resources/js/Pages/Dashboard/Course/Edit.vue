<script setup>
import { Head } from '@inertiajs/vue3'
import { computed, watchEffect, ref, shallowRef } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CourseHeader from '@/Components/Course/CourseHeader.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import SelectInput from '@/Components/SelectInput.vue'
import TextInput from '@/Components/TextInput.vue'
import {
    CheckCircleIcon, ArrowPathIcon, CheckIcon, EyeIcon, ChevronRightIcon,
    PaintBrushIcon, SwatchIcon, PhotoIcon, Cog6ToothIcon, XMarkIcon,
    ExclamationTriangleIcon, ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

// Consolidated UI Configuration - DRY principle applied
const UI = {
    // Layout and structure
    HEADER: 'bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700',
    CONTAINER: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
    MAIN: 'min-h-screen bg-gray-50 dark:bg-gray-900 py-6',
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
    
    // Flash messages
    FLASH_CONTAINER: 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-6',
    FLASH_SUCCESS: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-xl flex items-center',
    FLASH_ERROR: 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-xl flex items-center',
    FLASH_ICON: 'w-5 h-5 mr-3 flex-shrink-0',
    
    // Content areas
    CONTENT_CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden',
    CONTENT_HEADER: 'px-6 py-4 border-b border-gray-200 dark:border-gray-700',
    CONTENT_TITLE: 'text-lg font-semibold text-gray-900 dark:text-white flex items-center',
    CONTENT_BODY: 'p-6',
    
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

// Form fields configuration for course editing
const FORM_FIELDS = [
    { 
        key: 'title', 
        type: 'text', 
        component: 'TextInput', 
        required: true,
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
        key: 'category_id', 
        type: 'select', 
        component: 'SelectInput', 
        required: true, 
        useDataSet: true,
        label: 'Course Category',
        description: 'Select the appropriate category for this course'
    }
]

// Component mapping
const COMPONENTS = { 
    TextInput, 
    SelectInput
}

const props = defineProps({
    course: { type: Object, required: true },
    categories: { type: Array, default: () => [] }
})

// Reactive state
const originalData = shallowRef({})
const isInitialized = ref(false)
const message = ref({ type: '', text: '', show: false })

// Initialize form with course data
const form = useForm({
    title: props.course?.title || '',
    description: props.course?.description || '',
    price: props.course?.price || 0,
    category_id: props.course?.category_id || ''
})

// Computed properties
const categoriesOptions = computed(() => 
    props.categories?.map(category => ({ label: category.name, value: category.id })) || []
)

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

// Enhanced update function
const update = () => {
    form.put(route('courses.update', props.course.id), {
        preserveScroll: true,
        onSuccess: () => { 
            showMessage('success', 'Course updated successfully!')
            initializeData()
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
    if (props.course) {
        const courseData = {
            title: props.course.title || '',
            description: props.course.description || '',
            price: props.course.price || 0,
            category_id: props.course.category_id || ''
        }
        
        Object.assign(form, courseData)
        originalData.value = { ...courseData }
        form.clearErrors()
        isInitialized.value = true
    }
}

// Initialize on mount
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

// Helper functions
const getFieldProps = (field) => ({
    id: field.key,
    type: field.type,
    class: getInputClasses(field.type),
    required: field.required,
    placeholder: field.label,
    error: form.errors[field.key],
    ...(field.useDataSet && { dataSet: categoriesOptions.value })
})

const getFieldComponent = (field) => COMPONENTS[field.component]

const getInputClasses = (type) => {
    const baseClasses = UI.INPUT_BASE
    const typeSpecificClasses = {
        textarea: 'min-h-[100px] resize-vertical',
        select: 'cursor-pointer'
    }
    
    return `${baseClasses} ${typeSpecificClasses[type] || ''}`
}

const getButtonClass = (variant) => {
    const variants = {
        save: 'bg-blue-600 hover:bg-blue-700 text-white px-6 py-2',
        cancel: 'bg-gray-500 hover:bg-gray-600 text-white px-4 py-2'
    }
    
    return `${UI.BUTTON_BASE} ${variants[variant] || variants.save}`
}
</script>

<template>
    <Head :title="`Edit ${course.title}`" />
    
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Course
            </h2>
        </template>

        <div class="py-12">
            <div :class="UI.CONTAINER">
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
                        <component :is="message.type === 'success' ? CheckCircleIcon : ExclamationTriangleIcon" 
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

                <!-- Course Header with Edit Modal -->
                <CourseHeader 
                    :course="course" 
                    :categories="categories"
                    @success="(msg) => showMessage('success', msg)"
                />
                
                <!-- Enhanced Course Edit Form -->
                <div :class="UI.CONTENT_CARD">
                    <div :class="UI.CONTENT_HEADER">
                        <h3 :class="UI.CONTENT_TITLE">
                            <Cog6ToothIcon :class="UI.BUTTON_ICON" />
                            Course Details
                        </h3>
                        <div v-if="form.processing" class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></div>
                            <span class="text-sm text-gray-500">Processing...</span>
                        </div>
                    </div>
                    
                    <form :class="UI.CONTENT_BODY" @submit.prevent="update">
                        <div class="space-y-6">
                            <div 
                                v-for="field in FORM_FIELDS" 
                                :key="field.key" 
                                :class="UI.SETTING_ITEM"
                            >
                                <div class="flex-1">
                                    <InputLabel 
                                        :for="field.key" 
                                        :class="UI.SETTING_LABEL"
                                        :value="field.label" 
                                    />
                                    <p v-if="field.description" :class="UI.SETTING_DESC">
                                        {{ field.description }}
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
                        
                        <!-- Action Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div :class="UI.BUTTON_CONTAINER">
                                <SecondaryButton
                                    :disabled="form.processing"
                                    @click="$inertia.visit(route('courses.index'))"
                                    :class="getButtonClass('cancel')"
                                >
                                    <XMarkIcon :class="UI.BUTTON_ICON" />
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton
                                    :class="[getButtonClass('save'), { [UI.BUTTON_PROCESSING]: form.processing }]"
                                    :disabled="form.processing || !hasChanges"
                                    @click="update"
                                >
                                    <component :is="form.processing ? ArrowPathIcon : CheckIcon" 
                                        :class="[UI.BUTTON_ICON, { 'animate-spin': form.processing }]" />
                                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>