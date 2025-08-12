<script setup>
import { reactive, watch, computed } from 'vue'
import { Head, router, Link, usePage } from '@inertiajs/vue3'
import { debounce, pickBy } from 'lodash'
import {
    MagnifyingGlassIcon, PlusIcon, TrashIcon,
    Squares2X2Icon, ListBulletIcon, FunnelIcon
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import SelectInput from '@/Components/SelectInput.vue'
import Pagination from '@/Components/Pagination.vue'
import Delete from '@/Pages/Quizzes/Delete.vue'
import QuizCard from '@/Components/Quiz/QuizCard.vue'
import QuizStats from '@/Components/Quiz/QuizStats.vue'
import QuizBadge from '@/Components/Quiz/QuizBadge.vue'

const props = defineProps({
    title: String,
    filters: Object,
    quizzes: Object,
    courses: Array, // Add this
    perPage: Number,
    breadcrumbs: Array
})

const data = reactive({
    params: {
        search: props.filters?.search || '',
        course_id: props.filters?.course_id || '', // Add this
        field: props.filters?.field || '',
        order: props.filters?.order || '',
        perPage: props.perPage
    },
    selectedId: [],
    deleteOpen: false,
    quizToDelete: null,
    dataSet: usePage().props.app.perpage,
    viewMode: 'grid',
    showFilters: false
})

// Computed properties for better performance
const totalQuestions = computed(() =>
    props.quizzes.data?.reduce((sum, q) => sum + q.questions_count, 0) || 0
)

const order = (field) => {
    data.params.field = field
    data.params.order = data.params.order === 'asc' ? 'desc' : 'asc'
}

watch(() => data.params, debounce(() => {
    router.get(route('quizzes.index'), pickBy(data.params), {
        replace: true, preserveState: true, preserveScroll: true
    })
}, 150))

const selectAll = (e) => data.selectedId = e.target.checked ? props.quizzes.data.map(q => q.id) : []
const openDeleteModal = (quiz) => { data.quizToDelete = quiz; data.deleteOpen = true }
const navigateToCreateQuiz = () => router.visit(route('quizzes.create'))
</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header with Stats -->
                <div class="bg-white dark:bg-slate-800 shadow rounded-lg p-6 mb-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Create, manage, and analyze quiz performance</p>
                        </div>
                        <div class="flex gap-3">
                            <SecondaryButton>Export</SecondaryButton>
                            <PrimaryButton @click="navigateToCreateQuiz" class="flex items-center gap-2">
                                <PlusIcon class="w-4 h-4" />Create Quiz
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <QuizStats :quizzes="quizzes" :total-questions="totalQuestions" />
                </div>

                <!-- Search & Filters -->
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow border p-6 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        <div class="flex-1 relative">
                            <MagnifyingGlassIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <TextInput v-model="data.params.search" type="text"
                                     class="pl-12 w-full rounded-lg"
                                     placeholder="Search quizzes..." />
                        </div>

                        <!-- Course Filter -->
                        <select v-model="data.params.course_id"
                                class="w-48 rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-purple-500 focus:ring-purple-500">
                            <option value="">All Courses</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">
                                {{ course.title }}
                            </option>
                        </select>

                        <div class="flex items-center gap-3">
                            <button @click="data.showFilters = !data.showFilters"
                                    class="flex items-center px-4 py-2 text-gray-600 border rounded-lg hover:border-purple-300">
                                <FunnelIcon class="w-4 h-4 mr-2" />Filters
                            </button>
                            <SelectInput v-model="data.params.perPage" :dataSet="data.dataSet" class="w-20" />
                            <div class="flex bg-gray-100 dark:bg-slate-700 rounded-lg p-1">
                                <button @click="data.viewMode = 'grid'"
                                        :class="[data.viewMode === 'grid' ? 'bg-white shadow text-purple-600' : 'text-gray-500', 'p-2 rounded transition-all']">
                                    <Squares2X2Icon class="w-5 h-5" />
                                </button>
                                <button @click="data.viewMode = 'table'"
                                        :class="[data.viewMode === 'table' ? 'bg-white shadow text-purple-600' : 'text-gray-500', 'p-2 rounded transition-all']">
                                    <ListBulletIcon class="w-5 h-5" />
                                </button>
                            </div>
                            <DangerButton v-if="data.selectedId.length > 0" @click="data.deleteOpen = true">
                                <TrashIcon class="w-4 h-4 mr-2" />Delete ({{ data.selectedId.length }})
                            </DangerButton>
                        </div>
                    </div>
                </div>

                <!-- Grid View -->
                <div v-if="data.viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
                    <QuizCard 
                        v-for="quiz in quizzes.data" 
                        :key="quiz.id"
                        :quiz="quiz"
                        :show-checkbox="true"
                        :is-selected="data.selectedId.includes(quiz.id)"
                        @toggle-select="(id) => {
                            const index = data.selectedId.indexOf(id)
                            index > -1 ? data.selectedId.splice(index, 1) : data.selectedId.push(id)
                        }"
                        @delete="openDeleteModal"
                    />
                </div>

                <!-- Table View -->
                <div v-if="data.viewMode === 'table'" class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden mb-6">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" @change="selectAll" class="rounded border-gray-300 text-purple-600" />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" @click="order('title')">
                                    Quiz Details
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Course
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Questions</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Duration</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pass Score</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer" @click="order('created_at')">
                                    Created
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                            <tr v-for="quiz in quizzes.data" :key="quiz.id" class="hover:bg-gray-50 dark:hover:bg-slate-700">
                                <td class="px-6 py-4">
                                    <input type="checkbox" :value="quiz.id" v-model="data.selectedId" class="rounded border-gray-300 text-purple-600" />
                                </td>
                                <td class="px-6 py-4">
                                    <Link :href="route('quizzes.show', quiz.id)" class="font-medium text-gray-900 dark:text-white hover:text-purple-600">
                                        {{ quiz.title }}
                                    </Link>
                                    <p class="text-sm text-gray-500 line-clamp-1">{{ quiz.description || 'No description' }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <QuizBadge 
                                        v-if="quiz.course" 
                                        :count="quiz.course.title" 
                                        label="" 
                                        color="indigo" 
                                    />
                                    <span v-else class="text-gray-400 text-sm">No Course</span>
                                </td>
                                <td class="px-6 py-4 text-center font-medium">
                                    <QuizBadge 
                                        :count="quiz.questions_count || 0" 
                                        label="" 
                                        color="blue" 
                                    />
                                </td>
                                <td class="px-6 py-4 text-center font-medium">{{ quiz.time_limit }}min</td>
                                <td class="px-6 py-4 text-center">
                                    <QuizBadge 
                                        :count="quiz.passing_score" 
                                        label="%" 
                                        color="green" 
                                    />
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500">
                                    {{ new Date(quiz.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <Link :href="route('quizzes.show', quiz.id)" class="p-2 text-purple-600 hover:bg-purple-50 rounded">
                                            <EyeIcon class="w-4 h-4" />
                                        </Link>
                                        <Link :href="route('quizzes.edit', quiz.id)" class="p-2 text-gray-600 hover:bg-gray-50 rounded">
                                            <PencilIcon class="w-4 h-4" />
                                        </Link>
                                        <button @click="openDeleteModal(quiz)" class="p-2 text-red-600 hover:bg-red-50 rounded">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="quizzes.data.length === 0" class="text-center py-12">
                    <div class="bg-purple-100 dark:bg-slate-700 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                        <AcademicCapIcon class="w-10 h-10 text-purple-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No quizzes found</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Start creating engaging quizzes to test knowledge.</p>
                    <PrimaryButton @click="navigateToCreateQuiz" class="px-6 py-3">
                        <PlusIcon class="w-5 h-5 mr-2" />Create Your First Quiz
                    </PrimaryButton>
                </div>

                <!-- Pagination -->
                <Pagination v-if="quizzes.data.length > 0" :links="quizzes.links" />
            </div>
        </div>

        <Delete :show="data.deleteOpen" :quiz="data.quizToDelete" @close="data.deleteOpen = false" />
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>


