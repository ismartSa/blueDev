<script setup>
const props = defineProps({
    activeTab: { type: String, required: true },
    lessonsCount: { type: Number, default: 0 },
    quizzesCount: { type: Number, default: 0 },
});

const emit = defineEmits(['update:activeTab']);

const tabs = [
    { id: 'lessons', name: `Lessons (${props.lessonsCount})`, icon: 'VideoCameraIcon' },
    { id: 'quizzes', name: `Quizzes (${props.quizzesCount})`, icon: 'AcademicCapIcon' },
];
</script>

<template>
    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
        <nav class="flex space-x-8 px-6" dir="ltr"> <!-- Changed dir to ltr -->
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="emit('update:activeTab', tab.id)"
                :class="[
                    activeTab === tab.id
                        ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-white dark:bg-gray-800'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                    'whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg'
                ]"
            >
                {{ tab.name }}
            </button>
        </nav>
    </div>
</template>
