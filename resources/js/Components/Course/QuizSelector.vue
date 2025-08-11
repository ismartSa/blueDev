<script setup>
import { computed } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { PlayIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    selectedDomain: { type: String, default: '' },
    selectedChapter: { type: String, default: '' },
    selectedQuiz: { type: Object, default: null },
    availableDomains: { type: Array, default: () => [] },
    availableChapters: { type: Array, default: () => [] },
    availableQuizzes: { type: Array, default: () => [] }
});

const emit = defineEmits([
    'update:selectedDomain',
    'update:selectedChapter',
    'update:selectedQuiz',
    'start-quiz'
]);

const domainOptions = computed(() => {
    return props.availableDomains.map(domain => ({
        value: domain,
        label: domain
    }));
});

const chapterOptions = computed(() => {
    return props.availableChapters.map(chapter => ({
        value: chapter,
        label: chapter
    }));
});

const updateDomain = (value) => {
    emit('update:selectedDomain', value);
    emit('update:selectedChapter', '');
    emit('update:selectedQuiz', null);
};

const updateChapter = (value) => {
    emit('update:selectedChapter', value);
    emit('update:selectedQuiz', null);
};

const selectQuiz = (quiz) => {
    emit('update:selectedQuiz', quiz);
};

const startQuiz = () => {
    if (props.selectedQuiz) {
        emit('start-quiz');
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Domain Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Select Domain
            </label>
            <SelectInput
                :model-value="selectedDomain"
                @update:model-value="updateDomain"
                :options="domainOptions"
                placeholder="Choose a domain..."
                class="w-full"
            />
        </div>

        <!-- Chapter Selection -->
        <div v-if="selectedDomain">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Select Chapter
            </label>
            <SelectInput
                :model-value="selectedChapter"
                @update:model-value="updateChapter"
                :options="chapterOptions"
                placeholder="Choose a chapter..."
                class="w-full"
            />
        </div>

        <!-- Quiz Selection -->
        <div v-if="selectedChapter && availableQuizzes.length > 0">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Available Quizzes
            </label>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="quiz in availableQuizzes"
                    :key="quiz.id"
                    @click="selectQuiz(quiz)"
                    :class="[
                        'p-4 border rounded-lg cursor-pointer transition-all duration-200',
                        selectedQuiz?.id === quiz.id
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                            : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'
                    ]"
                >
                    <h3 class="font-medium text-gray-900 dark:text-white mb-2">
                        {{ quiz.title }}
                    </h3>
                    <p v-if="quiz.description" class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                        {{ quiz.description }}
                    </p>
                    <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                        <span v-if="quiz.questions_count">
                            {{ quiz.questions_count }} questions
                        </span>
                        <span v-if="quiz.time_limit">
                            {{ quiz.time_limit }} min
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Quizzes Message -->
        <div v-else-if="selectedChapter && availableQuizzes.length === 0" class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">No quizzes available for this chapter.</p>
        </div>

        <!-- Start Quiz Button -->
        <div v-if="selectedQuiz" class="flex justify-center pt-4">
            <PrimaryButton @click="startQuiz" class="flex items-center gap-2">
                <PlayIcon class="h-4 w-4" />
                Start Quiz: {{ selectedQuiz.title }}
            </PrimaryButton>
        </div>
    </div>
</template>
