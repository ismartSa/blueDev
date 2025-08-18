<script setup>
import { PlayIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/solid';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    lectures: { type: Array, default: () => [] }
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
        <div v-if="!lectures || lectures.length === 0" class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">No lectures available yet.</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Click "Add Lecture" to create your first lecture.</p>
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="(lecture, index) in lectures"
                :key="lecture.id"
                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 flex-1">
                        <!-- Lecture Number -->
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">
                                {{ index + 1 }}
                            </span>
                        </div>

                        <!-- Lecture Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
                                {{ lecture.title }}
                            </h3>
                            <p v-if="lecture.description" class="text-sm text-gray-600 dark:text-gray-300 mt-1 line-clamp-2">
                                {{ lecture.description }}
                            </p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <span v-if="lecture.duration">
                                    Duration: {{ formatDuration(lecture.duration) }}
                                </span>
                                <span v-if="lecture.type" class="capitalize">
                                    Type: {{ lecture.type }}
                                </span>
                                <span v-if="lecture.is_free" class="text-green-600 dark:text-green-400">
                                    Free
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-2 ml-4">
                        <SecondaryButton
                            @click="emit('play', lecture, index)"
                            class="!p-2"
                            title="Play Lecture"
                        >
                            <PlayIcon class="h-4 w-4" />
                        </SecondaryButton>

                        <SecondaryButton
                            @click="emit('edit', lecture)"
                            class="!p-2"
                            title="Edit Lecture"
                        >
                            <PencilIcon class="h-4 w-4" />
                        </SecondaryButton>

                        <DangerButton
                            @click="emit('delete', lecture)"
                            class="!p-2"
                            title="Delete Lecture"
                        >
                            <TrashIcon class="h-4 w-4" />
                        </DangerButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
