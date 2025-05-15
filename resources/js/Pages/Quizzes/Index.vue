<script setup>
import { reactive, watch } from 'vue'
import { Head, router, Link, usePage } from '@inertiajs/vue3'
import { debounce, pickBy } from 'lodash'
import {
    ChevronUpDownIcon,
    PencilIcon,
    TrashIcon,
    PlusIcon
} from '@heroicons/vue/24/solid'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import SelectInput from '@/Components/SelectInput.vue'
import Pagination from '@/Components/Pagination.vue'
import Delete from '@/Pages/Quizzes/Delete.vue'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    filters: {
        type: Object,
        required: true
    },
    quizzes: {
        type: Object,
        required: true
    },
    perPage: {
        type: Number,
        required: true
    },
    breadcrumbs: {
        type: Array,
        required: true
    }
})

const data = reactive({
    params: {
        search: props.filters?.search || '',
        field: props.filters?.field || '',
        order: props.filters?.order || '',
        perPage: props.perPage
    },
    selectedId: [],
    multipleSelect: false,
    deleteOpen: false,
    quizToDelete: null,
    dataSet: usePage().props.app.perpage
})

const order = (field) => {
    data.params.field = field
    data.params.order = data.params.order === 'asc' ? 'desc' : 'asc'
}

watch(
    () => data.params,
    debounce(() => {
        let params = pickBy(data.params)
        router.get(route('quizzes.index'), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true
        })
    }, 150)
)

const selectAll = (event) => {
    data.selectedId = event.target.checked ? props.quizzes.data.map(quiz => quiz.id) : []
    data.multipleSelect = event.target.checked
}

const select = () => {
    data.multipleSelect = props.quizzes.data.length === data.selectedId.length
}

const openDeleteModal = (quiz) => {
    data.quizToDelete = quiz
    data.deleteOpen = true
}

const navigateToCreateQuiz = () => {
    router.visit(route('quizzes.create'))
}
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                            <div class="flex-1 min-w-0">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Manage and take quizzes for this course
                                </p>
                            </div>
                            <div class="mt-4 flex md:mt-0 md:ml-4">
                                <PrimaryButton
                                    @click="navigateToCreateQuiz"
                                    class="inline-flex items-center"
                                >
                                    <PlusIcon class="w-5 h-5 mr-2" />
                                    Add New Quiz
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                            <div class="flex items-center space-x-2">
                                <SelectInput
                                    v-model="data.params.perPage"
                                    :dataSet="data.dataSet"
                                />
                                <DangerButton
                                    v-if="data.selectedId.length > 0"
                                    @click="data.deleteOpen = true"
                                    class="inline-flex items-center px-3 py-1.5"
                                >
                                    <TrashIcon class="w-5 h-5" />
                                    <span class="ml-2">Delete Selected</span>
                                </DangerButton>
                            </div>
                            <TextInput
                                v-model="data.params.search"
                                type="text"
                                class="block w-full sm:w-64"
                                placeholder="Search quizzes..."
                            />
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                <thead class="bg-gray-50 dark:bg-slate-900/70">
                                    <tr>
                                        <th class="px-6 py-3 text-left">
                                            <input
                                                type="checkbox"
                                                class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                                                @change="selectAll"
                                            />
                                        </th>
                                        <th
                                            v-for="column in [
                                                { key: 'title', label: 'Title' },
                                                { key: 'description', label: 'Description' },
                                                { key: 'questions_count', label: 'Questions' },
                                                { key: 'time_limit', label: 'Time Limit' },
                                                { key: 'passing_score', label: 'Passing Score' },
                                                { key: 'created_at', label: 'Created' },
                                                { key: 'updated_at', label: 'Updated' }
                                            ]"
                                            :key="column.key"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer"
                                            @click="order(column.key)"
                                        >
                                            <div class="flex items-center space-x-1">
                                                <span>{{ column.label }}</span>
                                                <ChevronUpDownIcon class="w-4 h-4" />
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                    <tr
                                        v-for="quiz in quizzes.data"
                                        :key="quiz.id"
                                        class="hover:bg-gray-50 dark:hover:bg-slate-700/50"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input
                                                type="checkbox"
                                                class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                                                :value="quiz.id"
                                                v-model="data.selectedId"
                                                @change="select"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link
                                                :href="route('quizzes.show', quiz.id)"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                                            >
                                                {{ quiz.title }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ quiz.description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ quiz.questions_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ quiz.time_limit }} minutes
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ quiz.passing_score }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ new Date(quiz.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ new Date(quiz.updated_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link
                                                    :href="route('quizzes.edit', quiz.id)"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                                                >
                                                    <PencilIcon class="w-5 h-5" />
                                                </Link>
                                                <button
                                                    @click="openDeleteModal(quiz)"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                                >
                                                    <TrashIcon class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination :links="quizzes.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Delete
            :show="data.deleteOpen"
            :quiz="data.quizToDelete"
            @close="data.deleteOpen = false"
        />
    </AuthenticatedLayout>
</template>
