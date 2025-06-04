<script setup>
import { reactive, computed } from "vue";
import QuizSelector from "@/Components/Course/QuizSelector.vue";
import QuizInterface from "@/Components/Course/QuizInterface.vue";
import CreateQuizModal from "@/Components/Course/CreateQuizModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { PlusIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    course: { type: Object, required: true },
    quizzes: { type: Object, default: () => ({}) },
    quizDomains: { type: Array, default: () => [] },
});

const emit = defineEmits(['success']);

const data = reactive({
    selectedDomain: '',
    selectedChapter: '',
    selectedQuiz: null,
    createQuizModalOpen: false,
    quizStarted: false,
});

const availableDomains = computed(() => {
    return Object.keys(props.quizzes || {});
});

const availableChapters = computed(() => {
    if (!data.selectedDomain || !props.quizzes[data.selectedDomain]) {
        return [];
    }
    return Object.keys(props.quizzes[data.selectedDomain]);
});

const availableQuizzes = computed(() => {
    if (!data.selectedDomain || !data.selectedChapter ||
        !props.quizzes[data.selectedDomain] ||
        !props.quizzes[data.selectedDomain][data.selectedChapter]) {
        return [];
    }
    return props.quizzes[data.selectedDomain][data.selectedChapter];
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Course Quizzes
                </h2>
                <PrimaryButton @click="data.createQuizModalOpen = true">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Create Quiz
                </PrimaryButton>
            </div>
            
            <!-- Quiz Content -->
            <div v-if="!data.quizStarted">
                <QuizSelector 
                    v-model:selectedDomain="data.selectedDomain"
                    v-model:selectedChapter="data.selectedChapter"
                    v-model:selectedQuiz="data.selectedQuiz"
                    :available-domains="availableDomains"
                    :available-chapters="availableChapters"
                    :available-quizzes="availableQuizzes"
                    @start-quiz="data.quizStarted = true"
                />
            </div>
            
            <div v-else>
                <QuizInterface 
                    :quiz="data.selectedQuiz"
                    @complete="data.quizStarted = false"
                    @back="data.quizStarted = false"
                />
            </div>
        </div>
    </div>
    
    <!-- Create Quiz Modal -->
    <CreateQuizModal 
        :show="data.createQuizModalOpen"
        :course="course"
        @close="data.createQuizModalOpen = false"
        @success="(msg) => { data.createQuizModalOpen = false; emit('success', msg); }"
    />
</template>
