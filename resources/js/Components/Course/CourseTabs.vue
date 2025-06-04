<script setup>
const props = defineProps({
    activeTab: { type: String, required: true },
    lessonsCount: { type: Number, default: 0 },
    quizzesCount: { type: Number, default: 0 },
});

const emit = defineEmits(['update:activeTab']);

const tabs = [
    { id: 'overview', name: 'Overview', icon: 'BookOpenIcon' },
    { id: 'lessons', name: `Lessons (${props.lessonsCount})`, icon: 'VideoCameraIcon' },
    { id: 'quizzes', name: `Quizzes (${props.quizzesCount})`, icon: 'AcademicCapIcon' },
];
</script>

<template>
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="emit('update:activeTab', tab.id)"
                :class="[
                    activeTab === tab.id
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                ]"
            >
                {{ tab.name }}
            </button>
        </nav>
    </div>
</template>
