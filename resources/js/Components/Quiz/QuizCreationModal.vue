<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    Add Quiz to Course
                </h2>
                <button 
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form @submit.prevent="handleSubmit" class="p-6">
                <!-- Course Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Course
                    </label>
                    <select 
                        v-model="form.course_id" 
                        :class="inputClasses"
                        required
                    >
                        <option value="">Choose a course...</option>
                        <option 
                            v-for="course in courses" 
                            :key="course.id" 
                            :value="course.id"
                        >
                            {{ course.title }}
                        </option>
                    </select>
                </div>

                <!-- Quiz Title -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Quiz Title
                    </label>
                    <input 
                        v-model="form.title" 
                        type="text" 
                        :class="inputClasses"
                        placeholder="Enter quiz title..."
                        required
                    >
                </div>

                <!-- Quiz Description -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea 
                        v-model="form.description" 
                        :class="inputClasses"
                        rows="3"
                        placeholder="Enter quiz description..."
                    ></textarea>
                </div>

                <!-- Quiz Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Time Limit -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Time Limit (minutes)
                        </label>
                        <input 
                            v-model.number="form.time_limit" 
                            type="number" 
                            :class="inputClasses"
                            min="1"
                            placeholder="30"
                        >
                    </div>

                    <!-- Passing Score -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Passing Score (%)
                        </label>
                        <input 
                            v-model.number="form.passing_score" 
                            type="number" 
                            :class="inputClasses"
                            min="0"
                            max="100"
                            placeholder="70"
                        >
                    </div>
                </div>

                <!-- Quiz Options -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Quiz Options
                    </label>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input 
                                v-model="form.is_active" 
                                type="checkbox" 
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-700">Active (visible to students)</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                v-model="form.randomize_questions" 
                                type="checkbox" 
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-700">Randomize question order</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                v-model="form.show_results" 
                                type="checkbox" 
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-700">Show results immediately after completion</span>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button 
                        type="button" 
                        @click="$emit('close')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="isSubmitting"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 rounded-lg transition-colors flex items-center"
                    >
                        <svg 
                            v-if="isSubmitting" 
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" 
                            fill="none" 
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isSubmitting ? 'Creating...' : 'Create Quiz' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    courses: {
        type: Array,
        required: true
    }
})

const emit = defineEmits(['close', 'quiz-created'])

const isSubmitting = ref(false)

const form = reactive({
    course_id: '',
    title: '',
    description: '',
    time_limit: 30,
    passing_score: 70,
    is_active: true,
    randomize_questions: false,
    show_results: true
})

const inputClasses = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors'

const handleSubmit = async () => {
    if (isSubmitting.value) return
    
    isSubmitting.value = true
    
    try {
        await router.post(route('quizzes.store'), form, {
            preserveScroll: true,
            onSuccess: (page) => {
                // Emit success event with the created quiz data
                emit('quiz-created', page.props.quiz)
                // Reset form
                Object.keys(form).forEach(key => {
                    if (typeof form[key] === 'boolean') {
                        form[key] = key === 'is_active' || key === 'show_results'
                    } else if (typeof form[key] === 'number') {
                        form[key] = key === 'time_limit' ? 30 : 70
                    } else {
                        form[key] = ''
                    }
                })
            },
            onError: (errors) => {
                console.error('Quiz creation failed:', errors)
                // Handle validation errors here if needed
            }
        })
    } catch (error) {
        console.error('Quiz creation error:', error)
    } finally {
        isSubmitting.value = false
    }
}
</script>