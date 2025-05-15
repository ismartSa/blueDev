<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="stat in stats" :key="stat.name"
                         class="bg-white dark:bg-slate-800 overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <component :is="stat.icon"
                                             class="h-6 w-6 text-gray-400"
                                             aria-hidden="true" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                            {{ stat.name }}
                                        </dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                                {{ stat.value }}
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Quizzes -->
                <div class="mt-8">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Recent Quizzes
                    </h3>
                    <div class="mt-4 bg-white dark:bg-slate-800 shadow overflow-hidden sm:rounded-lg">
                        <ul role="list" class="divide-y divide-gray-200 dark:divide-slate-700">
                            <li v-for="quiz in recentQuizzes" :key="quiz.id" class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ quiz.title }}
                                        </p>
                                    </div>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                           :class="quiz.passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                            {{ quiz.score }}%
                                        </p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="mt-8">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Top Performers
                    </h3>
                    <div class="mt-4 bg-white dark:bg-slate-800 shadow overflow-hidden sm:rounded-lg">
                        <ul role="list" class="divide-y divide-gray-200 dark:divide-slate-700">
                            <li v-for="performer in topPerformers" :key="performer.id" class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <img class="h-8 w-8 rounded-full"
                                                 :src="performer.avatar"
                                                 :alt="performer.name" />
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ performer.name }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ performer.email }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ performer.averageScore }}%
                                        </p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import { ref, onMounted } from 'vue'
import {
    ChartBarIcon,
    ChartPieIcon,
    UserGroupIcon,
    AcademicCapIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    breadcrumbs: {
        type: Array,
        required: true
    },
    stats: {
        type: Object,
        required: true
    },
    recentQuizzes: {
        type: Array,
        required: true
    },
    topPerformers: {
        type: Array,
        required: true
    }
})

const stats = [
    {
        name: 'Total Quizzes',
        value: props.stats.totalQuizzes,
        icon: ChartBarIcon,
        color: 'bg-blue-500'
    },
    {
        name: 'Average Score',
        value: `${props.stats.averageScore}%`,
        icon: ChartPieIcon,
        color: 'bg-green-500'
    },
    {
        name: 'Total Participants',
        value: props.stats.totalParticipants,
        icon: UserGroupIcon,
        color: 'bg-purple-500'
    },
    {
        name: 'Pass Rate',
        value: `${props.stats.passRate}%`,
        icon: AcademicCapIcon,
        color: 'bg-yellow-500'
    }
]
</script>

