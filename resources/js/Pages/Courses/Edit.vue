<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CourseHeader from '@/Components/Course/CourseHeader.vue';
import { reactive } from 'vue';

const props = defineProps({
    course: { type: Object, required: true },
    categories: { type: Array, default: () => [] }
});

const data = reactive({
    successMessage: ''
});

const handleSuccess = (message) => {
    data.successMessage = message;
    // Optionally redirect or show success notification
};
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="data.successMessage" class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-green-800 dark:text-green-200">{{ data.successMessage }}</p>
                    </div>
                </div>

                <!-- Course Header with Edit Modal -->
                <CourseHeader 
                    :course="course" 
                    :categories="categories"
                    @success="handleSuccess"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>