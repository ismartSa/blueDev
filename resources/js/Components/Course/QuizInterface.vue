<script setup>
import { reactive, computed, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ArrowLeftIcon, ClockIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    quiz: { type: Object, required: true }
});

const emit = defineEmits(['complete', 'back']);

const data = reactive({
    currentQuestionIndex: 0,
    timeRemaining: props.quiz.time_limit ? props.quiz.time_limit * 60 : null,
    quizStarted: false,
    quizCompleted: false
});

const form = useForm({
    answers: {}
});

let timer = null;

const currentQuestion = computed(() => {
    return props.quiz.questions?.[data.currentQuestionIndex] || null;
});

const progress = computed(() => {
    if (!props.quiz.questions?.length) return 0;
    return ((data.currentQuestionIndex + 1) / props.quiz.questions.length) * 100;
});

const formattedTime = computed(() => {
    if (!data.timeRemaining) return null;
    const minutes = Math.floor(data.timeRemaining / 60);
    const seconds = data.timeRemaining % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

const canProceed = computed(() => {
    return form.answers[currentQuestion.value?.id] !== undefined;
});

const isLastQuestion = computed(() => {
    return data.currentQuestionIndex === (props.quiz.questions?.length - 1);
});

const startTimer = () => {
    if (!data.timeRemaining) return;

    timer = setInterval(() => {
        data.timeRemaining--;
        if (data.timeRemaining <= 0) {
            submitQuiz();
        }
    }, 1000);
};

const stopTimer = () => {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
};

const selectAnswer = (answerId) => {
    form.answers[currentQuestion.value.id] = answerId;
};

const nextQuestion = () => {
    if (data.currentQuestionIndex < props.quiz.questions.length - 1) {
        data.currentQuestionIndex++;
    }
};

const previousQuestion = () => {
    if (data.currentQuestionIndex > 0) {
        data.currentQuestionIndex--;
    }
};

const submitQuiz = () => {
    stopTimer();

    form.post(route('quiz.submit', props.quiz.id), {
        onSuccess: () => {
            data.quizCompleted = true;
            emit('complete');
        },
        onError: (errors) => {
            console.error('Quiz submission failed:', errors);
        }
    });
};

const startQuiz = () => {
    data.quizStarted = true;
    startTimer();
};

const goBack = () => {
    stopTimer();
    emit('back');
};

onMounted(() => {
    // Auto-start quiz or show start screen
    if (props.quiz.questions?.length > 0) {
        startQuiz();
    }
});

onUnmounted(() => {
    stopTimer();
});
</script>

<template>
    <div class="max-w-4xl mx-auto">
        <!-- Quiz Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ quiz.title }}
                    </h1>
                    <p v-if="quiz.description" class="text-gray-600 dark:text-gray-300 mt-1">
                        {{ quiz.description }}
                    </p>
                </div>
                <SecondaryButton @click="goBack" class="flex items-center gap-2">
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back
                </SecondaryButton>
            </div>

            <!-- Progress and Timer -->
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300 mb-2">
                        <span>Question {{ currentQuestionIndex + 1 }} of {{ quiz.questions?.length || 0 }}</span>
                        <span>{{ Math.round(progress) }}% Complete</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div
                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            :style="{ width: progress + '%' }"
                        ></div>
                    </div>
                </div>

                <div v-if="formattedTime" class="ml-6 flex items-center gap-2 text-lg font-mono">
                    <ClockIcon class="h-5 w-5 text-red-500" />
                    <span :class="data.timeRemaining < 300 ? 'text-red-500' : 'text-gray-700 dark:text-gray-300'">
                        {{ formattedTime }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Question Content -->
        <div v-if="currentQuestion" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                {{ currentQuestion.question }}
            </h2>

            <!-- Answer Options -->
            <div class="space-y-3 mb-8">
                <div
                    v-for="answer in currentQuestion.answers"
                    :key="answer.id"
                    @click="selectAnswer(answer.id)"
                    :class="[
                        'p-4 border rounded-lg cursor-pointer transition-all duration-200',
                        form.answers[currentQuestion.id] === answer.id
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                            : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'
                    ]"
                >
                    <div class="flex items-center">
                        <div :class="[
                            'w-4 h-4 rounded-full border-2 mr-3',
                            form.answers[currentQuestion.id] === answer.id
                                ? 'border-blue-500 bg-blue-500'
                                : 'border-gray-300 dark:border-gray-600'
                        ]">
                            <div v-if="form.answers[currentQuestion.id] === answer.id"
                                 class="w-2 h-2 bg-white rounded-full mx-auto mt-0.5">
                            </div>
                        </div>
                        <span class="text-gray-900 dark:text-white">{{ answer.answer }}</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex items-center justify-between">
                <SecondaryButton
                    @click="previousQuestion"
                    :disabled="currentQuestionIndex === 0"
                >
                    Previous
                </SecondaryButton>

                <div class="flex gap-3">
                    <SecondaryButton v-if="!isLastQuestion" @click="nextQuestion">
                        Skip
                    </SecondaryButton>

                    <PrimaryButton
                        v-if="isLastQuestion"
                        @click="submitQuiz"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Submitting...' : 'Submit Quiz' }}
                    </PrimaryButton>

                    <PrimaryButton
                        v-else
                        @click="nextQuestion"
                        :disabled="!canProceed"
                    >
                        Next
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <!-- No Questions -->
        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center">
            <p class="text-gray-500 dark:text-gray-400">No questions available for this quiz.</p>
        </div>
    </div>
</template>
