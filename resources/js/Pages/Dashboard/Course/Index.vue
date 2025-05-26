<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextArea from "@/Components/TextArea.vue";
import { reactive, watch, computed, nextTick, ref } from "vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
import {
    PencilIcon,
    TrashIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon
} from "@heroicons/vue/24/solid";

const { debounce, pickBy } = pkg;

// Constants
const DEBOUNCE_DELAY = 300;
const PER_PAGE_OPTIONS = [
    { value: 10, label: '10 per page' },
    { value: 25, label: '25 per page' },
    { value: 50, label: '50 per page' },
    { value: 100, label: '100 per page' }
];

const STATUS_OPTIONS = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'draft', label: 'Draft' }
];

// Props with validation
const props = defineProps({
    title: { type: String, required: true },
    filters: { type: Object, default: () => ({}) },
    courses: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
    perPage: { type: Number, default: 10 },
    stats: { type: Object, default: () => ({}) },
});

// Reactive data
const data = reactive({
    params: {
        search: props.filters.search || '',
        field: props.filters.field || 'created_at',
        order: props.filters.order || 'desc',
        perPage: props.perPage,
    },
    selectedIds: [],
    loading: false,
    error: null,

    // Modal states
    createOpen: false,
    editOpen: false,
    deleteOpen: false,
    deleteBulkOpen: false,

    // UI state
    processing: false,
    showSuccess: false,
    successMessage: '',
    courseToDelete: null,
});

// Form handling with Inertia useForm
const createForm = useForm({
    title: '',
    category_id: null,
    status: 'active',
    description: '',
});

const editForm = useForm({
    title: '',
    category_id: null,
    status: 'active',
    description: '',
});

const deleteForm = useForm({});
const bulkDeleteForm = useForm({
    ids: []
});

// Refs for form inputs
const createTitleInput = ref(null);
const editTitleInput = ref(null);

// Computed properties
const isAllSelected = computed(() =>
    props.courses?.data?.length > 0 &&
    data.selectedIds.length === props.courses.data.length
);

const isIndeterminate = computed(() =>
    data.selectedIds.length > 0 &&
    data.selectedIds.length < props.courses.data.length
);

const hasSelectedItems = computed(() => data.selectedIds.length > 0);

const sortableColumns = computed(() => [
    { key: 'title', label: 'Course Title', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Created At', sortable: true },
    { key: 'updated_at', label: 'Updated At', sortable: true },
]);

const categoriesForSelect = computed(() => [
    { value: null, label: 'Select Category' },
    ...props.categories.map(cat => ({ value: cat.id, label: cat.name }))
]);

const filteredCoursesCount = computed(() => props.courses?.total || 0);

const currentPageRange = computed(() => {
    if (!props.courses?.data?.length) return '';

    const from = (props.courses.current_page - 1) * props.courses.per_page + 1;
    const to = Math.min(from + props.courses.data.length - 1, props.courses.total);

    return `${from}-${to} of ${props.courses.total}`;
});

// Methods
const performSearch = debounce(() => {
    data.loading = true;
    data.error = null;

    const params = pickBy(data.params, value => value !== '' && value !== null);

    router.get(route("courses.index"), params, {
        replace: true,
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            data.loading = false;
        },
        onError: (errors) => {
            data.error = 'Failed to load courses. Please try again.';
            data.loading = false;
            console.error('Loading error:', errors);
        }
    });
}, DEBOUNCE_DELAY);

const sortBy = (field) => {
    if (data.params.field === field) {
        data.params.order = data.params.order === "asc" ? "desc" : "asc";
    } else {
        data.params.field = field;
        data.params.order = "asc";
    }
};

const selectAll = (event) => {
    data.selectedIds = event.target.checked
        ? props.courses?.data?.map(course => course.id) || []
        : [];
};

const toggleSelection = (courseId) => {
    const index = data.selectedIds.indexOf(courseId);
    if (index > -1) {
        data.selectedIds.splice(index, 1);
    } else {
        data.selectedIds.push(courseId);
    }
};

const clearSelection = () => {
    data.selectedIds = [];
};

// Modal methods
const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    data.createOpen = true;

    nextTick(() => {
        createTitleInput.value?.focus();
    });
};

const openEditModal = (course) => {
    editForm.reset();
    editForm.clearErrors();

    editForm.title = course.title;
    editForm.category_id = course.category?.id || null;
    editForm.status = course.status.toLowerCase();
    editForm.description = course.description || '';

    data.courseToDelete = course;
    data.editOpen = true;

    nextTick(() => {
        editTitleInput.value?.focus();
    });
};

const openDeleteModal = (course) => {
    data.courseToDelete = course;
    data.deleteOpen = true;
};

const openBulkDeleteModal = () => {
    bulkDeleteForm.ids = [...data.selectedIds];
    data.deleteBulkOpen = true;
};

const closeModals = () => {
    data.createOpen = false;
    data.editOpen = false;
    data.deleteOpen = false;
    data.deleteBulkOpen = false;
    data.courseToDelete = null;
};

// Form submission methods
const submitCreate = () => {
    createForm.post(route('courses.store'), {
        onSuccess: () => {
            closeModals();
            showSuccessMessage('Course created successfully!');
        },
        onError: (errors) => {
            console.error('Create error:', errors);
        }
    });
};

const submitEdit = () => {
    editForm.put(route('courses.update', data.courseToDelete?.id), {
        onSuccess: () => {
            closeModals();
            showSuccessMessage('Course updated successfully!');
        },
        onError: (errors) => {
            console.error('Update error:', errors);
        }
    });
};

const submitDelete = () => {
    deleteForm.delete(route('courses.destroy', data.courseToDelete.id), {
        onSuccess: () => {
            closeModals();
            showSuccessMessage('Course deleted successfully!');
        },
        onError: (errors) => {
            console.error('Delete error:', errors);
        }
    });
};

const submitBulkDelete = () => {
    bulkDeleteForm.post(route('courses.bulk-delete'), {
        onSuccess: () => {
            closeModals();
            clearSelection();
            showSuccessMessage(`${bulkDeleteForm.ids.length} courses deleted successfully!`);
        },
        onError: (errors) => {
            console.error('Bulk delete error:', errors);
        }
    });
};

const showSuccessMessage = (message) => {
    data.successMessage = message;
    data.showSuccess = true;

    setTimeout(() => {
        data.showSuccess = false;
    }, 5000);
};

const getTableIndex = (index) => {
    if (!props.courses.current_page || !props.courses.per_page) return index + 1;
    return (props.courses.current_page - 1) * props.courses.per_page + index + 1;
};

const getStatusBadgeClass = (status) => {
    const statusLower = status.toLowerCase();
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        inactive: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        draft: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    };
    return classes[statusLower] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
};

const getSortIcon = (field) => {
    if (data.params.field !== field) return null;
    return data.params.order === 'asc' ? ArrowUpIcon : ArrowDownIcon;
};

// Watchers
watch(
    () => [data.params.search, data.params.field, data.params.order, data.params.perPage],
    performSearch
);

watch(
    () => data.selectedIds.length,
    () => {
        nextTick(() => {
            const selectAllCheckbox = document.querySelector('#select-all-checkbox');
            if (selectAllCheckbox) {
                selectAllCheckbox.indeterminate = isIndeterminate.value;
            }
        });
    }
);

// Keyboard shortcuts
const handleKeydown = (event) => {
    // Ctrl/Cmd + N for new course
    if ((event.ctrlKey || event.metaKey) && event.key === 'n') {
        event.preventDefault();
        openCreateModal();
    }

    // Escape to close modals
    if (event.key === 'Escape') {
        closeModals();
    }
};

// Add event listener for keyboard shortcuts
nextTick(() => {
    document.addEventListener('keydown', handleKeydown);
});

// Cleanup
const cleanup = () => {
    document.removeEventListener('keydown', handleKeydown);
};
</script>

<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Success Message -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 transform translate-y-2"
                enter-to-class="opacity-100 transform translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 transform translate-y-0"
                leave-to-class="opacity-0 transform translate-y-2"
            >
                <div
                    v-if="data.showSuccess"
                    class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" />
                        <p class="text-green-800 dark:text-green-200">{{ data.successMessage }}</p>
                        <button
                            @click="data.showSuccess = false"
                            class="ml-auto text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Header with Stats -->
            <div class="bg-white dark:bg-slate-800 shadow rounded-lg p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ props.title }}
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Manage your courses and categories
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <SecondaryButton>
                            Export
                        </SecondaryButton>
                        <PrimaryButton
                            @click="openCreateModal"
                            class="flex items-center gap-2"
                            :title="'Create new course (Ctrl+N)'"
                        >
                            <PlusIcon class="w-4 h-4" />
                            Add Course
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                        <h3 class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Courses</h3>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ props.stats.total || 0 }}</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                        <h3 class="text-sm font-medium text-green-600 dark:text-green-400">Active</h3>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ props.stats.active || 0 }}</p>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <h3 class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Draft</h3>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ props.stats.draft || 0 }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</h3>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ props.stats.inactive || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white dark:bg-slate-800 shadow rounded-lg overflow-hidden">
                <!-- Table Controls -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center p-4 border-b border-gray-200 dark:border-gray-700 gap-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <SelectInput
                            v-model="data.params.perPage"
                            :dataSet="PER_PAGE_OPTIONS"
                            class="w-32"
                            aria-label="Items per page"
                        />

                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 transform scale-95"
                            enter-to-class="opacity-100 transform scale-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 transform scale-100"
                            leave-to-class="opacity-0 transform scale-95"
                        >
                            <DangerButton
                                v-show="hasSelectedItems"
                                @click="openBulkDeleteModal"
                                class="flex items-center gap-2"
                                :aria-label="`Delete ${data.selectedIds.length} selected courses`"
                            >
                                <TrashIcon class="w-4 h-4" />
                                Delete Selected ({{ data.selectedIds.length }})
                            </DangerButton>
                        </Transition>

                        <div v-if="filteredCoursesCount" class="text-sm text-gray-600 dark:text-gray-400">
                            Showing {{ currentPageRange }}
                        </div>
                    </div>

                    <div class="relative w-full lg:w-80">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <TextInput
                            v-model="data.params.search"
                            type="text"
                            class="w-full pl-10"
                            placeholder="Search courses, categories, status..."
                            aria-label="Search courses"
                        />
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="data.loading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 dark:border-blue-400"></div>
                    <span class="ml-3 text-gray-600 dark:text-gray-400">Loading courses...</span>
                </div>

                <!-- Error State -->
                <div v-else-if="data.error" class="text-center py-12">
                    <ExclamationTriangleIcon class="w-12 h-12 text-red-500 mx-auto mb-4" />
                    <p class="text-red-600 dark:text-red-400 mb-4">{{ data.error }}</p>
                    <PrimaryButton @click="performSearch">
                        Try Again
                    </PrimaryButton>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full" role="table" aria-label="Courses table">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-4 py-3 text-center w-16" scope="col">
                                    <Checkbox
                                        id="select-all-checkbox"
                                        v-model:checked="isAllSelected"
                                        @change="selectAll"
                                        :aria-label="isAllSelected ? 'Deselect all courses' : 'Select all courses'"
                                        class="rounded"
                                    />
                                </th>
                                <th class="px-4 py-3 text-left w-16 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider" scope="col">
                                    #
                                </th>
                                <th
                                    v-for="column in sortableColumns"
                                    :key="column.key"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                                    @click="sortBy(column.key)"
                                    scope="col"
                                    :aria-sort="data.params.field === column.key ? (data.params.order === 'asc' ? 'ascending' : 'descending') : 'none'"
                                >
                                    <div class="flex items-center justify-between">
                                        <span>{{ column.label }}</span>
                                        <component
                                            :is="getSortIcon(column.key)"
                                            v-if="getSortIcon(column.key)"
                                            class="w-4 h-4 text-blue-600 dark:text-blue-400"
                                        />
                                        <div v-else class="flex flex-col opacity-50">
                                            <ArrowUpIcon class="w-3 h-3" />
                                            <ArrowDownIcon class="w-3 h-3 -mt-1" />
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider" scope="col">
                                    Category
                                </th>
                                <th class="px-4 py-3 text-center w-32 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider" scope="col">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-if="!props.courses?.data?.length"
                                class="text-center"
                            >
                                <td colspan="8" class="px-4 py-16 text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <MagnifyingGlassIcon class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" />
                                        <p class="text-lg mb-2">No courses found</p>
                                        <p class="text-sm">Try adjusting your search criteria or create a new course</p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="(course, index) in props.courses.data"
                                :key="course.id"
                                class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors"
                                :class="{ 'bg-blue-50 dark:bg-blue-900/20': data.selectedIds.includes(course.id) }"
                            >
                                <td class="px-4 py-3 text-center">
                                    <input
                                        type="checkbox"
                                        :value="course.id"
                                        v-model="data.selectedIds"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-slate-700"
                                        :aria-label="`Select course: ${course.title}`"
                                    />
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ getTableIndex(index) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ course.title }}
                                    </div>
                                    <div v-if="course.description" class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                        {{ course.description }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="getStatusBadgeClass(course.status)"
                                    >
                                        {{ course.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    <time :datetime="course.created_at">
                                        {{ course.created_at }}
                                    </time>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    <time :datetime="course.updated_at">
                                        {{ course.updated_at }}
                                    </time>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span v-if="course.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        {{ course.category.name }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <PrimaryButton
                                            @click="openEditModal(course)"
                                            class="p-2 text-xs"
                                            :title="`Edit ${course.title}`"
                                            :aria-label="`Edit course: ${course.title}`"
                                        >
                                            <PencilIcon class="w-4 h-4" />
                                        </PrimaryButton>
                                        <DangerButton
                                            @click="openDeleteModal(course)"
                                            class="p-2 text-xs"
                                            :title="`Delete ${course.title}`"
                                            :aria-label="`Delete course: ${course.title}`"
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </DangerButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-slate-900/50">
                    <Pagination :links="props.courses" :filters="data.params" />
                </div>
            </div>
        </div>

        <!-- Create Course Modal -->
        <Modal :show="data.createOpen" @close="closeModals" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                    Create New Course
                </h2>

                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div>
                        <InputLabel for="create-title" value="Course Title" />
                        <TextInput
                            id="create-title"
                            ref="createTitleInput"
                            v-model="createForm.title"
                            type="text"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': createForm.errors.title }"
                            placeholder="Enter course title"
                            required
                        />
                        <InputError :message="createForm.errors.title" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="create-category" value="Category" />
                        <SelectInput
                            id="create-category"
                            v-model="createForm.category_id"
                            :dataSet="categoriesForSelect"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': createForm.errors.category_id }"
                        />
                        <InputError :message="createForm.errors.category_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="create-status" value="Status" />
                        <SelectInput
                            id="create-status"
                            v-model="createForm.status"
                            :dataSet="STATUS_OPTIONS"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': createForm.errors.status }"
                        />
                        <InputError :message="createForm.errors.status" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="create-description" value="Description" />
                        <TextArea
                            id="create-description"
                            v-model="createForm.description"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': createForm.errors.description }"
                            rows="4"
                            placeholder="Enter course description (optional)"
                        />
                        <InputError :message="createForm.errors.description" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="closeModals" type="button">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            type="submit"
                            :disabled="createForm.processing"
                            class="flex items-center gap-2"
                        >
                            <span v-if="createForm.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                            {{ createForm.processing ? 'Creating...' : 'Create Course' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Course Modal -->
        <Modal :show="data.editOpen" @close="closeModals" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                    Edit Course
                </h2>

                <form @submit.prevent="submitEdit" class="space-y-6">
                    <div>
                        <InputLabel for="edit-title" value="Course Title" />
                        <TextInput
                            id="edit-title"
                            ref="editTitleInput"
                            v-model="editForm.title"
                            type="text"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': editForm.errors.title }"
                            placeholder="Enter course title"
                            required
                        />
                        <InputError :message="editForm.errors.title" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="edit-category" value="Category" />
                        <SelectInput
                            id="edit-category"
                            v-model="editForm.category_id"
                            :dataSet="categoriesForSelect"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': editForm.errors.category_id }"
                        />
                        <InputError :message="editForm.errors.category_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="edit-status" value="Status" />
                        <SelectInput
                            id="edit-status"
                            v-model="editForm.status"
                            :dataSet="STATUS_OPTIONS"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': editForm.errors.status }"
                        />
                        <InputError :message="editForm.errors.status" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="edit-description" value="Description" />
                        <TextArea
                            id="edit-description"
                            v-model="editForm.description"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': editForm.errors.description }"
                            rows="4"
                            placeholder="Enter course description (optional)"
                        />
                        <InputError :message="editForm.errors.description" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="closeModals" type="button">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            type="submit"
                            :disabled="editForm.processing"
                            class="flex items-center gap-2"
                        >
                            <span v-if="editForm.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                            {{ editForm.processing ? 'Updating...' : 'Update Course' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="data.deleteOpen" @close="closeModals" max-width="md">
            <div class="p-6">
                <div class="flex items-center mb-6">
                    <ExclamationTriangleIcon class="w-8 h-8 text-red-600 dark:text-red-400 mr-4" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Delete Course
                    </h2>
                </div>

                <div class="mb-6">
                    <p class="text-gray-600 dark:text-gray-400">
                        Are you sure you want to delete "<span class="font-semibold text-gray-900 dark:text-gray-100">{{ data.courseToDelete?.title }}</span>"?
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">
                        This action cannot be undone.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="closeModals" type="button">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="submitDelete"
                        :disabled="deleteForm.processing"
                        class="flex items-center gap-2"
                    >
                        <span v-if="deleteForm.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                        {{ deleteForm.processing ? 'Deleting...' : 'Delete Course' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Bulk Delete Confirmation Modal -->
        <Modal :show="data.deleteBulkOpen" @close="closeModals" max-width="md">
            <div class="p-6">
                <div class="flex items-center mb-6">
                    <ExclamationTriangleIcon class="w-8 h-8 text-red-600 dark:text-red-400 mr-4" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Delete Multiple Courses
                    </h2>
                </div>

                <div class="mb-6">
                    <p class="text-gray-600 dark:text-gray-400">
                        Are you sure you want to delete <span class="font-semibold text-gray-900 dark:text-gray-100">{{ bulkDeleteForm.ids.length }}</span> selected courses?
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">
                        This action cannot be undone.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="closeModals" type="button">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="submitBulkDelete"
                        :disabled="bulkDeleteForm.processing"
                        class="flex items-center gap-2"
                    >
                        <span v-if="bulkDeleteForm.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                        {{ bulkDeleteForm.processing ? 'Deleting...' : `Delete ${bulkDeleteForm.ids.length} Courses` }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
