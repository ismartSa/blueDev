<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'

defineProps({
    quiz: {
        type: Object,
        required: true
    },
    attempt: {
        type: Object,
        required: true
    },
    score: {
        type: Number,
        required: true
    },
    totalQuestions: {
        type: Number,
        required: true
    },
    passed: {
        type: Boolean,
        required: true
    },
    answers: {
        type: Array,
        required: true
    }
})
</script>

<template>
    <Head :title="`Quiz Results - ${quiz.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :title="`Quiz Results - ${quiz.title}`" :breadcrumbs="[
                { label: 'Dashboard', href: route('dashboard') },
                { label: 'Quizzes', href: route('quizzes.index') },
                { label: quiz.title, href: route('quizzes.show', quiz.id) },
                { label: 'Results', href: '#' }
            ]" />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                                Quiz Results
                            </h2>
                            <div class="text-4xl font-bold" :class="passed ? 'text-green-600' : 'text-red-600'">
                                {{ score }}%
                            </div>
                            <p class="mt-2 text-gray-600 dark:text-gray-300">
                                {{ passed ? 'Congratulations! You passed the quiz.' : 'You need to retake the quiz.' }}
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div v-for="(answer, index) in answers" :key="index" class="border-b dark:border-slate-700 pb-4 last:border-0">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-6 h-6 rounded-full flex items-center justify-center"
                                            :class="answer.correct ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
                                        >
                                            {{ index + 1 }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-900 dark:text-white font-medium">{{ answer.question }}</p>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            Your answer: {{ answer.user_answer }}
                                        </p>
                                        <p v-if="!answer.correct" class="mt-1 text-sm text-green-600">
                                            Correct answer: {{ answer.correct_answer }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
