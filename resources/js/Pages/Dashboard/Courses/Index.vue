<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import { Head, useForm } from "@inertiajs/vue3"
import PrimaryButton from "@/Components/PrimaryButton.vue"
import DangerButton from "@/Components/DangerButton.vue"
import SecondaryButton from "@/Components/SecondaryButton.vue"
import SelectInput from "@/Components/SelectInput.vue"
import Checkbox from "@/Components/Checkbox.vue"
import Pagination from "@/Components/Pagination.vue"
import Modal from "@/Components/Modal.vue"
import Breadcrumb from "@/Components/Breadcrumb.vue"
import { reactive, watch, computed, ref } from "vue"
import pkg from "lodash"
import { router } from "@inertiajs/vue3"
import {
    PencilIcon, TrashIcon, PlusIcon, MagnifyingGlassIcon,
    ArrowUpIcon, ArrowDownIcon, ExclamationTriangleIcon,
    CheckCircleIcon, EyeIcon, AcademicCapIcon
} from "@heroicons/vue/24/solid"

const { debounce, pickBy } = pkg

// Optimized constants
const CONFIG = {
    DEBOUNCE_DELAY: 300,
    PER_PAGE_OPTIONS: [10, 25, 50, 100].map(value => ({ value, label: `${value} per page` })),
    SORTABLE_COLUMNS: [
        { key: 'title', label: 'Course Title', sortable: true },
        { key: 'status', label: 'Status', sortable: true },
        { key: 'created_at', label: 'Created At', sortable: true },
        { key: 'updated_at', label: 'Updated At', sortable: true }
    ]
}

const props = defineProps({
    courses: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) }
})

// Reactive state
const state = reactive({
    params: {
        search: props.filters.search || '',
        field: props.filters.field || 'created_at',
        order: props.filters.order || 'desc',
        perPage: 10
    },
    selectedIds: [],
    loading: false,
    modals: {
        delete: false,
        bulkDelete: false
    },
    courseToDelete: null
})

// Forms
const deleteForm = useForm({})
const bulkDeleteForm = useForm({ ids: [] })

// Computed properties
const isAllSelected = computed(() => 
    props.courses?.data?.length > 0 && state.selectedIds.length === props.courses.data.length
)

const isIndeterminate = computed(() => 
    state.selectedIds.length > 0 && state.selectedIds.length < props.courses.data.length
)

const hasSelectedItems = computed(() => state.selectedIds.length > 0)

const currentPageRange = computed(() => {
    if (!props.courses?.data?.length) return ''
    const from = (props.courses.current_page - 1) * props.courses.per_page + 1
    const to = Math.min(from + props.courses.data.length - 1, props.courses.total)
    return `${from}-${to} of ${props.courses.total}`
})

// Methods
const performSearch = debounce(() => {
    state.loading = true
    const params = pickBy(state.params, value => value !== '' && value !== null)
    
    router.get(route("dashboard.courses.index"), params, {
        replace: true,
        preserveState: true,
        preserveScroll: true,
        onFinish: () => state.loading = false
    })
}, CONFIG.DEBOUNCE_DELAY)

const sortBy = (field) => {
    if (state.params.field === field) {
        state.params.order = state.params.order === "asc" ? "desc" : "asc"
    } else {
        state.params.field = field
        state.params.order = "asc"
    }
}

const selectAll = (event) => {
    state.selectedIds = event.target.checked 
        ? props.courses?.data?.map(course => course.id) || [] 
        : []
}

const toggleSelection = (courseId) => {
    const index = state.selectedIds.indexOf(courseId)
    index > -1 ? state.selectedIds.splice(index, 1) : state.selectedIds.push(courseId)
}

const openDeleteModal = (course) => {
    state.courseToDelete = course
    state.modals.delete = true
}

const openBulkDeleteModal = () => {
    bulkDeleteForm.ids = [...state.selectedIds]
    state.modals.bulkDelete = true
}

const deleteCourse = () => {
    if (!state.courseToDelete) return
    
    deleteForm.delete(route('courses.destroy', state.courseToDelete.id), {
        onSuccess: () => {
            state.modals.delete = false
            state.courseToDelete = null
        }
    })
}

const bulkDelete = () => {
    bulkDeleteForm.post(route('courses.destroy-bulk'), {
        onSuccess: () => {
            state.modals.bulkDelete = false
            state.selectedIds = []
        }
    })
}

// Manage enrollments functionality
const manageEnrollments = (courseId) => {
    router.visit(route('dashboard.courses.enrollments', courseId))
}

// Watchers
watch(() => state.params, performSearch, { deep: true })
</script>

<template>
    <Head title="Courses Management" />
    
    <AuthenticatedLayout>
        <!-- Breadcrumb Navigation -->
        <Breadcrumb 
            title="Course Management" 
            :breadcrumbs="[
                { label: 'Courses', href: route('dashboard.courses.index') }
            ]"
        />

        <div class="space-y-6">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <AcademicCapIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Courses</h1>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Manage your educational content and track enrollments</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ props.courses?.total || 0 }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total Courses</p>
                            </div>
                            <PrimaryButton @click="router.visit(route('courses.create'))" class="flex items-center space-x-2">
                                <PlusIcon class="w-4 h-4" />
                                <span>Add Course</span>
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Search and Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <!-- Search -->
                        <div class="relative flex-1 max-w-md">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                            <input
                                v-model="state.params.search"
                                type="text"
                                placeholder="Search courses..."
                                class="pl-10 pr-4 py-2 w-full border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-200"
                            />
                            <div v-if="state.loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center gap-3">
                            <SelectInput
                                v-model="state.params.perPage"
                                :options="CONFIG.PER_PAGE_OPTIONS"
                                class="w-40"
                            />
                            
                            <DangerButton
                                v-if="hasSelectedItems"
                                @click="openBulkDeleteModal"
                                :disabled="bulkDeleteForm.processing"
                                class="flex items-center space-x-2"
                            >
                                <TrashIcon class="h-4 w-4" />
                                <span>Delete ({{ state.selectedIds.length }})</span>
                            </DangerButton>
                        </div>
                    </div>
                 </div>
             </div>

            <!-- Courses Table -->
            <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <!-- Empty State -->
                <div v-if="!props.courses?.data?.length && !state.loading" class="text-center py-12">
                    <AcademicCapIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No courses found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by creating your first course.</p>
                    <PrimaryButton @click="router.visit(route('courses.create'))" class="flex items-center space-x-2 mx-auto">
                        <PlusIcon class="w-4 h-4" />
                        <span>Create Course</span>
                    </PrimaryButton>
                </div>
                
                <!-- Loading State -->
                <div v-else-if="state.loading" class="text-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-4"></div>
                    <p class="text-gray-500 dark:text-gray-400">Loading courses...</p>
                </div>
                
                <!-- Courses Table -->
                <div v-else class="w-full">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="w-10 px-3 py-2 text-left">
                                        <Checkbox
                                            :checked="isAllSelected"
                                            :indeterminate="isIndeterminate"
                                            @change="selectAll"
                                        />
                                    </th>
                                    <th
                                         v-for="column in CONFIG.SORTABLE_COLUMNS.filter(col => col.key !== 'updated_at')"
                                         :key="column.key"
                                         class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                         :class="{
                                             'w-auto': column.key === 'title',
                                             'w-24': column.key === 'status',
                                             'w-28': column.key === 'created_at'
                                         }"
                                         @click="sortBy(column.key)"
                                     >
                                        <div class="flex items-center gap-2">
                                            {{ column.label }}
                                            <component
                                                :is="state.params.order === 'asc' ? ArrowUpIcon : ArrowDownIcon"
                                                v-if="state.params.field === column.key"
                                                class="h-4 w-4"
                                            />
                                        </div>
                                    </th>
                                    <th class="w-28 px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="course in props.courses.data"
                                    :key="course.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
                                >
                                    <td class="px-3 py-2">
                                        <Checkbox
                                            :checked="state.selectedIds.includes(course.id)"
                                            @change="toggleSelection(course.id)"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                                    <AcademicCapIcon class="w-4 h-4 text-white" />
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ course.title }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    ID: {{ course.id }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': course.status === 'Active',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': course.status === 'Draft',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': course.status === 'Inactive'
                                            }"
                                        >
                                            <div class="w-1.5 h-1.5 rounded-full mr-1" :class="{
                                                'bg-green-500': course.status === 'Active',
                                                'bg-yellow-500': course.status === 'Draft',
                                                'bg-red-500': course.status === 'Inactive'
                                            }"></div>
                                            {{ course.status }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 truncate">
                                         {{ new Date(course.created_at).toLocaleDateString() }}
                                     </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-1">
                                            <!-- View Enrollments -->
                                            <button
                                                @click="manageEnrollments(course.id)"
                                                class="inline-flex items-center px-2 py-1 bg-purple-100 hover:bg-purple-200 dark:bg-purple-900 dark:hover:bg-purple-800 text-purple-700 dark:text-purple-300 text-xs font-medium rounded transition-colors"
                                                :title="`Manage enrollments for ${course.title}`"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3">
                                                    <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- Edit Button -->
                                            <button
                                                @click="router.visit(route('dashboard.courses.edit', course.id))"
                                                class="inline-flex items-center p-1 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 rounded transition-colors"
                                                :title="`Edit ${course.title}`"
                                            >
                                                <PencilIcon class="h-3 w-3" />
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <button
                                                @click="openDeleteModal(course)"
                                                class="inline-flex items-center p-1 bg-red-100 hover:bg-red-200 dark:bg-red-900 dark:hover:bg-red-800 text-red-700 dark:text-red-300 rounded transition-colors"
                                                :title="`Delete ${course.title}`"
                                            >
                                                <TrashIcon class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="w-full px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Showing {{ currentPageRange }}
                            </div>
                            <Pagination :links="props.courses.links" />
                        </div>
                    </div>
                </div>
            </div>

        <!-- Delete Modal -->
        <Modal :show="state.modals.delete" @close="state.modals.delete = false">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <ExclamationTriangleIcon class="h-8 w-8 text-red-600" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Delete Course
                    </h3>
                </div>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to delete "{{ state.courseToDelete?.title }}"? This action cannot be undone.
                </p>
                
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="state.modals.delete = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="deleteCourse"
                        :disabled="deleteForm.processing"
                    >
                        Delete Course
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Bulk Delete Modal -->
        <Modal :show="state.modals.bulkDelete" @close="state.modals.bulkDelete = false">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <ExclamationTriangleIcon class="h-8 w-8 text-red-600" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Delete Multiple Courses
                    </h3>
                </div>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to delete {{ state.selectedIds.length }} selected courses? This action cannot be undone.
                </p>
                
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="state.modals.bulkDelete = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="bulkDelete"
                        :disabled="bulkDeleteForm.processing"
                    >
                        Delete Courses
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>