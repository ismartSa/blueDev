<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    sections: {
        type: Array,
        required: true
    },
    currentLectureId: {
        type: Number,
        required: true
    }
})
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm">
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Course Content</h3>
            <div class="space-y-4">
                <div v-for="section in sections" :key="section.id" class="border-b dark:border-slate-700 pb-4 last:border-0">
                    <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-2">{{ section.title }}</h4>
                    <ul class="space-y-2">
                        <li v-for="lecture in section.lectures" :key="lecture.id">
                            <Link
                                :href="route('courses.watch', { courseId: lecture.course_id, courseSlug: lecture.course_slug, lectureID: lecture.id })"
                                class="flex items-center justify-between p-2 rounded hover:bg-gray-50 dark:hover:bg-slate-700"
                                :class="{ 'bg-blue-50 dark:bg-blue-900': lecture.id === currentLectureId }"
                            >
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ lecture.title }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ lecture.duration }} min</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
