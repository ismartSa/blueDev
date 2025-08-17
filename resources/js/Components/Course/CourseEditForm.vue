<script setup>
import { computed, watchEffect, ref, shallowRef } from 'vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import SelectInput from '@/Components/SelectInput.vue'
import TextInput from '@/Components/TextInput.vue'
import {
    CheckCircleIcon, ArrowPathIcon, CheckIcon, Cog6ToothIcon, XMarkIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Optimized UI Configuration
const UI = {
    CONTAINER: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
    INPUT_BASE: 'mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200',
    BUTTON_BASE: 'inline-flex items-center justify-center font-medium rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200',
    BUTTON_ICON: 'w-4 h-4 mr-2',
    CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden',
    HEADER: 'px-6 py-4 border-b border-gray-200 dark:border-gray-700',
    BODY: 'p-6',
    FIELD: 'flex items-start justify-between bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4',
    LABEL: 'block text-sm font-semibold text-gray-900 dark:text-white mb-1',
    DESC: 'text-sm text-gray-600 dark:text-gray-400'
}

// Form fields configuration
const FIELDS = [
    { key: 'title', type: 'text', component: TextInput, required: true, label: 'Course Title', desc: 'Enter a descriptive title for the course' },
    { key: 'description', type: 'textarea', component: TextInput, label: 'Course Description', desc: 'Provide a detailed description of the course content' },
    { key: 'price', type: 'number', component: TextInput, label: 'Course Price', desc: 'Set the price for this course (leave 0 for free)' },
    { key: 'category_id', type: 'select', component: SelectInput, required: true, useDataSet: true, label: 'Course Category', desc: 'Select the appropriate category for this course' },
    { key: 'status', type: 'select', component: SelectInput, required: true, useDataSet: true, label: 'Course Status', desc: 'Set the current status of the course' },
    { key: 'duration', type: 'number', component: TextInput, label: 'Duration (hours)', desc: 'Course duration in hours (optional)' },
    { key: 'level', type: 'select', component: SelectInput, useDataSet: true, label: 'Course Level', desc: 'Select the difficulty level (optional)' },
    { key: 'language', type: 'select', component: SelectInput, useDataSet: true, label: 'Course Language', desc: 'Select the course language (optional)' }
]

// Button configurations
const BUTTONS = {
    cancel: { variant: 'bg-gray-500 hover:bg-gray-600 text-white px-4 py-2', icon: XMarkIcon, text: 'Cancel' },
    save: { variant: 'bg-blue-600 hover:bg-blue-700 text-white px-6 py-2', icon: CheckIcon, text: 'Save Changes' }
}

const props = defineProps({
    course: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    updateRoute: { type: String, required: true },
    cancelRoute: { type: String, required: true },
    showHeader: { type: Boolean, default: true },
    headerTitle: { type: String, default: 'Course Details' }
})

const emit = defineEmits(['success', 'error'])

// Reactive state
const originalData = shallowRef({})
const message = ref({ type: '', text: '', show: false })

// Initialize form with course data
const form = useForm({
    title: props.course?.title || '',
    description: props.course?.description || '',
    price: props.course?.price || 0,
    category_id: props.course?.category_id || '',
    status: props.course?.status || 'draft',
    duration: props.course?.duration || '',
    level: props.course?.level || '',
    language: props.course?.language || ''
})

// Computed properties
const categoriesOptions = computed(() => 
    props.categories?.map(cat => ({ label: cat.name, value: cat.id })) || []
)

const statusOptions = computed(() => [
    { label: 'Active', value: 'active' },
    { label: 'Draft', value: 'draft' },
    { label: 'Inactive', value: 'inactive' }
])

const levelOptions = computed(() => [
    { label: 'Beginner', value: 'beginner' },
    { label: 'Intermediate', value: 'intermediate' },
    { label: 'Advanced', value: 'advanced' }
])

const languageOptions = computed(() => [
    { label: 'English', value: 'en' },
    { label: 'Arabic', value: 'ar' },
    { label: 'French', value: 'fr' },
    { label: 'Spanish', value: 'es' }
])

const hasChanges = computed(() => 
    Object.keys(form.data()).some(key => form[key] !== originalData.value[key])
)

// Methods
const update = () => {
    form.put(props.updateRoute, {
        preserveScroll: true,
        onSuccess: () => {
            showMessage('success', 'Course updated successfully!')
            emit('success')
            // Redirect to courses list after successful update
            setTimeout(() => {
                window.location.href = props.cancelRoute
            }, 1500)
        },
        onError: (errors) => {
            const errorCount = Object.keys(errors).length
            const message = errorCount === 1 
                ? 'Please fix the validation error below.'
                : `Please fix the ${errorCount} validation errors below.`
            showMessage('error', message)
            emit('error')
        }
    })
}

const showMessage = (type, text) => {
    message.value = { type, text, show: true }
    setTimeout(() => message.value.show = false, 3000)
}

const cancel = () => {
    window.location.href = props.cancelRoute
}

// Initialize form data
watchEffect(() => {
    if (props.course) {
        const data = {
            title: props.course.title || '',
            description: props.course.description || '',
            price: props.course.price || 0,
            category_id: props.course.category_id || '',
            status: props.course.status || 'draft',
            duration: props.course.duration || '',
            level: props.course.level || '',
            language: props.course.language || ''
        }
        Object.assign(form, data)
        originalData.value = { ...data }
    }
})

const getFieldProps = (field) => {
    let dataSet = []
    if (field.useDataSet) {
        switch (field.key) {
            case 'category_id':
                dataSet = categoriesOptions.value
                break
            case 'status':
                dataSet = statusOptions.value
                break
            case 'level':
                dataSet = levelOptions.value
                break
            case 'language':
                dataSet = languageOptions.value
                break
        }
    }
    
    return {
        id: field.key,
        type: field.type,
        class: `${UI.INPUT_BASE} ${field.type === 'textarea' ? 'min-h-[100px] resize-vertical' : field.type === 'select' ? 'cursor-pointer' : ''}`,
        required: field.required,
        placeholder: field.label,
        error: form.errors[field.key],
        ...(field.useDataSet && { dataSet })
    }
}
</script>

<template>
    <!-- Message Display -->
    <div v-if="message.show" class="fixed top-4 right-4 z-50 max-w-md p-4 rounded-lg shadow-lg transition-all duration-300" 
         :class="message.type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'">
        <div class="flex items-center gap-3">
            <component :is="message.type === 'success' ? CheckCircleIcon : ExclamationTriangleIcon" 
                       :class="['h-5 w-5', message.type === 'success' ? 'text-green-600' : 'text-red-600']" />
            <p class="text-sm font-medium">{{ message.text }}</p>
            <button @click="message.show = false" class="ml-auto text-gray-400 hover:text-gray-600">
                <XMarkIcon class="h-4 w-4" />
            </button>
        </div>
    </div>

    <!-- Course Edit Form -->
    <div :class="UI.CARD">
        <div v-if="showHeader" :class="UI.HEADER">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <Cog6ToothIcon :class="UI.BUTTON_ICON" />{{ headerTitle }}
            </h3>
        </div>
        
        <form :class="UI.BODY" @submit.prevent="update">
            <div class="space-y-6">
                <div v-for="field in FIELDS" :key="field.key" :class="UI.FIELD">
                    <div class="flex-1">
                        <InputLabel :for="field.key" :class="UI.LABEL" :value="field.label" />
                        <p v-if="field.desc" :class="UI.DESC">{{ field.desc }}</p>
                    </div>
                    <div class="flex-shrink-0 w-48">
                        <component :is="field.component" v-model="form[field.key]" v-bind="getFieldProps(field)" />
                        <InputError class="mt-2 text-xs" :message="form.errors[field.key]" />
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                <button type="button" :disabled="form.processing" @click="cancel" 
                        :class="[UI.BUTTON_BASE, BUTTONS.cancel.variant]">
                    <component :is="BUTTONS.cancel.icon" :class="UI.BUTTON_ICON" />{{ BUTTONS.cancel.text }}
                </button>
                <button type="submit" :disabled="form.processing || !hasChanges" 
                        :class="[UI.BUTTON_BASE, BUTTONS.save.variant, { 'opacity-25': form.processing }]">
                    <component :is="form.processing ? ArrowPathIcon : BUTTONS.save.icon" 
                               :class="[UI.BUTTON_ICON, { 'animate-spin': form.processing }]" />
                    {{ form.processing ? 'Saving...' : BUTTONS.save.text }}
                </button>
            </div>
        </form>
    </div>
</template>