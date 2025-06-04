<script setup>
import { PlayIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/solid';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    lessons: { type: Array, default: () => [] }
});

const emit = defineEmits(['edit', 'play', 'delete', 'success']);

const formatDuration = (seconds) => {
    if (!seconds) return '0:00';
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};
</script>

<template>
    <div class="space-y-4">
        <div v-if="!lessons || lessons.length === 0" class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">No lessons available yet.</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Click "Add Lesson" to create your first lesson.</p>
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="(lesson, index) in lessons"
                :key="lesson.id"
                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 flex-1">
                        <!-- Lesson Number -->
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">
                                {{ index + 1 }}
                            </span>
                        </div>

                        <!-- Lesson Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
                                {{ lesson.title }}
                            </h3>
                            <p v-if="lesson.description" class="text-sm text-gray-600 dark:text-gray-300 mt-1 line-clamp-2">
                                {{ lesson.description }}
                            </p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <span v-if="lesson.duration">
                                    Duration: {{ formatDuration(lesson.duration) }}
                                </span>
                                <span v-if="lesson.type" class="capitalize">
                                    Type: {{ lesson.type }}
                                </span>
                                <span v-if="lesson.is_free" class="text-green-600 dark:text-green-400">
                                    Free
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-2 ml-4">
                        <SecondaryButton
                            @click="emit('play', lesson, index)"
                            class="!p-2"
                            title="Play Lesson"
                        >
                            <PlayIcon class="h-4 w-4" />
                        </SecondaryButton>

                        <SecondaryButton
                            @click="emit('edit', lesson)"
                            class="!p-2"
                            title="Edit Lesson"
                        >
                            <PencilIcon class="h-4 w-4" />
                        </SecondaryButton>

                        <DangerButton
                            @click="emit('delete', lesson)"
                            class="!p-2"
                            title="Delete Lesson"
                        >
                            <TrashIcon class="h-4 w-4" />
                        </DangerButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
