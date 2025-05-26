<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";
import { reactive, watch, computed, nextTick } from "vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
import { PencilIcon, TrashIcon, PlusIcon } from "@heroicons/vue/24/solid";

const { debounce, pickBy } = pkg;

// Constants
const DEBOUNCE_DELAY = 300;
const PER_PAGE_OPTIONS = [10, 25, 50, 100];

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

    // Form data
    form: {
        id: null,
        title: '',
        category_id: null,
        status: 'active',
        description: '',
    },

    // UI state
    processing: false,
});

// Computed properties
const isAllSelected = computed(() =>
    props.courses?.data?.length > 0 &&
    data.selectedIds.length === props.courses.data.length
);

const hasSelectedItems = computed(() => data.selectedIds.length > 0);

const sortableColumns = computed(() => [
    { key: 'title', label: 'Title' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Created' },
    { key: 'updated_at', label: 'Updated' },
]);

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
            data.error = 'Failed to load courses';
            data.loading = false;
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

const openCreateModal = () => {
    resetForm();
    data.createOpen = true;
};

const openEditModal = (course) => {
    data.form = { ...course };
    data.editOpen = true;
};

const openDeleteModal = (course) => {
    data.form = { ...course };
    data.deleteOpen = true;
};

const openBulkDeleteModal = () => {
    data.deleteBulkOpen = true;
};

const resetForm = () => {
    data.form = {
        id: null,
        title: '',
        category_id: null,
        status: 'active',
        description: '',
    };
};

const submitForm = () => {
    data.processing = true;

    const method = data.form.id ? 'put' : 'post';
    const url = data.form.id
        ? route('courses.update', data.form.id)
        : route('courses.store');

    router[method](url, data.form, {
        onSuccess: () => {
            data.createOpen = false;
            data.editOpen = false;
            resetForm();
        },
        onFinish: () => {
            data.processing = false;
        }
    });
};

const deleteCourse = () => {
    data.processing = true;

    router.delete(route('courses.destroy', data.form.id), {
        onSuccess: () => {
            data.deleteOpen = false;
            resetForm();
        },
        onFinish: () => {
            data.processing = false;
        }
    });
};

const bulkDelete = () => {
    data.processing = true;

    router.post(route('courses.bulk-delete'), {
        ids: data.selectedIds
    }, {
        onSuccess: () => {
            data.deleteBulkOpen = false;
            data.selectedIds = [];
        },
        onFinish: () => {
            data.processing = false;
        }
    });
};

const getTableIndex = (index) => {
    return (props.courses.current_page - 1) * props.courses.per_page + index + 1;
};

// Watchers
watch(
    () => [data.params.search, data.params.field, data.params.order, data.params.perPage],
    performSearch
);

watch(
    () => data.selectedIds.length,
    () => {
        // Update select all checkbox state
        nextTick(() => {
            const selectAllCheckbox = document.querySelector('#select-all');
            if (selectAllCheckbox) {
                selectAllCheckbox.indeterminate =
                    data.selectedIds.length > 0 &&
                    data.selectedIds.length < props.courses.data.length;
            }
        });
    }
);
</script>

<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header with Stats -->
            <div class="bg-white dark:bg-slate-800 shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ props.title }}
                    </h1>
                    <PrimaryButton @click="openCreateModal" class="flex items-center gap-2">
                        <PlusIcon class="w-4 h-4" />
                        Add Course
                    </PrimaryButton>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Courses</h3>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ props.stats.total || 0 }}</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-green-600 dark:text-green-400">Active</h3>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ props.stats.active || 0 }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</h3>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ props.stats.inactive || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white dark:bg-slate-800 shadow rounded-lg">
                <!-- Table Controls -->
                <div class="flex flex-col sm:flex-row justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700 gap-4">
                    <div class="flex items-center gap-4">
                        <SelectInput
                            v-model="data.params.perPage"
                            :dataSet="PER_PAGE_OPTIONS"
                            class="w-20"
                        />
                        <DangerButton
                            v-show="hasSelectedItems"
                            @click="openBulkDeleteModal"
                            class="flex items-center gap-2"
                        >
                            <TrashIcon class="w-4 h-4" />
                            Delete Selected ({{ data.selectedIds.length }})
                        </DangerButton>
                    </div>

                    <TextInput
                        v-model="data.params.search"
                        type="text"
                        class="w-full sm:w-80"
                        placeholder="Search courses..."
                    />
                </div>

                <!-- Loading State -->
                <div v-if="data.loading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>

                <!-- Error State -->
                <div v-else-if="data.error" class="text-center py-12 text-red-600">
                    {{ data.error }}
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full" role="table" aria-label="Courses table">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-4 py-3 text-center w-16">
                                    <Checkbox
                                        id="select-all"
                                        v-model:checked="isAllSelected"
                                        @change="selectAll"
                                        :aria-label="isAllSelected ? 'Deselect all courses' : 'Select all courses'"
                                    />
                                </th>
                                <th class="px-4 py-3 text-left w-16">#</th>
                                <th
                                    v-for="column in sortableColumns"
                                    :key="column.key"
                                    class="px-4 py-3 text-left cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-800"
                                    @click="sortBy(column.key)"
                                >
                                    <div class="flex items-center justify-between">
                                        <span>{{ column.label }}</span>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-xs"
                                                :class="data.params.field === column.key && data.params.order === 'asc' ? 'text-blue-600' : 'text-gray-400'"
                                            >▲</span>
                                            <span
                                                class="text-xs"
                                                :class="data.params.field === column.key && data.params.order === 'desc' ? 'text-blue-600' : 'text-gray-400'"
                                            >▼</span>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left">Category</th>
                                <th class="px-4 py-3 text-center w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-if="!props.courses?.data?.length"
                                class="text-center py-12"
                            >
                                <td colspan="7" class="px-4 py-12 text-gray-500">
                                    No courses found
                                </td>
                            </tr>
                            <tr
                                v-for="(course, index) in props.courses.data"
                                :key="course.id"
                                class="hover:bg-gray-50 dark:hover:bg-slate-700/50"
                            >
                                <td class="px-4 py-3 text-center">
                                    <input
                                        type="checkbox"
                                        :value="course.id"
                                        v-model="data.selectedIds"
                                        @change="toggleSelection"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ getTableIndex(index) }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ course.title }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800': course.status === 'Active',
                                            'bg-red-100 text-red-800': course.status === 'Inactive',
                                            'bg-yellow-100 text-yellow-800': course.status === 'Draft'
                                        }"
                                    >
                                        {{ course.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ course.created_at }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ course.updated_at }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ course.category?.name || '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <PrimaryButton
                                            @click="openEditModal(course)"
                                            class="p-2"
                                            :title="`Edit ${course.title}`"
                                        >
                                            <PencilIcon class="w-4 h-4" />
                                        </PrimaryButton>
                                        <DangerButton
                                            @click="openDeleteModal(course)"
                                            class="p-2"
                                            :title="`Delete ${course.title}`"
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
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <Pagination :links="props.courses" :filters="data.params" />
                </div>
            </div>
        </div>

        <!-- Modals would go here -->
        <!-- Create/Edit Modal -->
        <!-- Delete Confirmation Modal -->
        <!-- Bulk Delete Modal -->
    </AuthenticatedLayout>
</template>
