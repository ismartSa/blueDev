<script setup>
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    course: {
        type: Object,
        required: true
    }
})

const page = usePage()
</script>

<template>
    <div class="md:w-2/3">
        <!-- وصف الدورة -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                {{ $page.props.translations?.course?.label?.description || 'Description' }}
            </h3>
            <div class="prose dark:prose-invert max-w-none">
                <div v-html="course.description"></div>
            </div>
        </div>

        <!-- محتوى الدورة -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                {{ $page.props.translations?.course?.label?.what_you_will_learn || 'What You Will Learn' }}
            </h3>
            <ul class="space-y-3">
                <li v-for="(item, index) in course.learning_outcomes" :key="index"
                    class="flex items-start bg-gray-50 dark:bg-slate-700 p-3 rounded-lg">
                    <svg class="w-5 h-5 text-green-500 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-gray-700 dark:text-gray-300">{{ item }}</span>
                </li>
            </ul>
        </div>

        <!-- متطلبات الدورة -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                {{ $page.props.translations?.course?.label?.requirements || 'Requirements' }}
            </h3>
            <ul class="space-y-3">
                <li v-for="(requirement, index) in course.requirements" :key="index"
                    class="flex items-start bg-gray-50 dark:bg-slate-700 p-3 rounded-lg">
                    <svg class="w-5 h-5 text-blue-500 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-gray-700 dark:text-gray-300">{{ requirement }}</span>
                </li>
            </ul>
        </div>

        <!-- المستهدفون -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                {{ $page.props.translations?.course?.label?.who_is_this_course_for || 'Who Is This Course For?' }}
            </h3>
            <ul class="space-y-3">
                <li v-for="(target, index) in course.target_audience" :key="index"
                    class="flex items-start bg-gray-50 dark:bg-slate-700 p-3 rounded-lg">
                    <svg class="w-5 h-5 text-purple-500 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-gray-700 dark:text-gray-300">{{ target }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.prose {
    @apply text-gray-700 dark:text-gray-300;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4 {
    @apply text-gray-900 dark:text-white;
}

.prose a {
    @apply text-primary hover:text-primary/80;
}

.prose ul {
    @apply list-disc list-inside;
}

.prose ol {
    @apply list-decimal list-inside;
}

/* تحسينات إضافية للوضع المظلم */
.dark .bg-gray-50 {
    @apply bg-slate-700;
}

.dark .text-gray-700 {
    @apply text-gray-300;
}

/* تأثيرات حركية */
.rounded-lg {
    @apply transition-all duration-200;
}

.rounded-lg:hover {
    @apply shadow-md;
}
</style>
