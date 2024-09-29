<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, Link } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InfoButton from "@/Components/InfoButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import { reactive, watch } from "vue";
import DangerButton from "@/Components/DangerButton.vue";
import { debounce, pickBy } from "lodash";
import Pagination from "@/Components/Pagination.vue";
import {
    ChevronUpDownIcon,
    PencilIcon,
    TrashIcon,
} from "@heroicons/vue/24/solid";
import Delete from "@/Pages/Quizzes/Delete.vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    title: String,
    filters: Object,
    quizzes: Object,
    perPage: Number,
});

const data = reactive({
    params: {
        search: props.filters?.search || "",
        field: props.filters?.field || "",
        order: props.filters?.order || "",
        perPage: props.perPage,
    },
    selectedId: [],
    multipleSelect: false,
    deleteOpen: false,
    quizToDelete: null,
    dataSet: usePage().props.app.perpage,
});

const order = (field) => {
    data.params.field = field;
    data.params.order = data.params.order === "asc" ? "desc" : "asc";
};

watch(
    () => data.params,
    debounce(() => {
        let params = pickBy(data.params);
        router.get(route("quizzes.index"), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150)
);

const selectAll = (event) => {
    data.selectedId = event.target.checked ? props.quizzes.data.map(quiz => quiz.id) : [];
    data.multipleSelect = event.target.checked;
};

const select = () => {
    data.multipleSelect = props.quizzes.data.length === data.selectedId.length;
};

const openDeleteModal = (quiz) => {
    console.log('Opening delete modal for quiz:', quiz);
    data.quizToDelete = quiz;
    data.deleteOpen = true;
};

const navigateToCreateQuiz = () => {
    router.visit(route('quizzes.create'));
};
</script>

<template>
    <Head :title="props.title" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <div class="py-8 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl font-bold leading-tight text-gray-900 sm:text-4xl">
                            {{ props.title }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-500">Manage and take quizzes for this course</p>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4">
                        <PrimaryButton
                            class="rounded-none"
                            @click="navigateToCreateQuiz"
                        >
                            Add New Quiz
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <div class="relative bg-white dark:bg-slate-800 shadow sm:rounded-lg">
                <div class="flex justify-between p-2">
                    <div class="flex space-x-2">
                        <SelectInput
                            v-model="data.params.perPage"
                            :dataSet="data.dataSet"
                        />
                        <DangerButton
                            @click="data.deleteOpen = true"
                            v-show="data.selectedId.length != 0"
                            class="px-3 py-1.5"
                            v-tooltip="'Delete selected quizzes'"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                    </div>
                    <TextInput
                        v-model="data.params.search"
                        type="text"
                        class="block w-3/6 md:w-2/6 lg:w-1/6 rounded-lg"
                        placeholder="Search quizzes"
                    />
                </div>
                <div class="overflow-x-auto scrollbar-table">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/70">
                            <tr>
                                <th class="whitespace-nowrap py-4 px-2 sm:py-3 text-center">
                                    <input
                                        class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                        type="checkbox"
                                        @change="selectAll"
                                    />
                                </th>
                                <th class="px-2 py-4 cursor-pointer" @click="order('title')">
                                    <div class="flex justify-between items-center">
                                        <span>Title</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" @click="order('description')">
                                    <div class="flex justify-between items-center">
                                        <span>Description</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" @click="order('questions_count')">
                                    <div class="flex justify-between items-center">
                                        <span>Questions</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" @click="order('time_limit')">
                                    <div class="flex justify-between items-center">
                                        <span>Time Limit</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" @click="order('passing_score')">
                                    <div class="flex justify-between items-center">
                                        <span>Passing Score</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" @click="order('created_at')">
                                    <div class="flex justify-between items-center">
                                        <span>Created</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 cursor-pointer" @click="order('updated_at')">
                                    <div class="flex justify-between items-center">
                                        <span>Updated</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 sr-only">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(quiz, index) in props.quizzes.data"
                                :key="index"
                                class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                            >
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3 text-center">
                                    <input
                                        class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                        type="checkbox"
                                        @change="select"
                                        :value="quiz.id"
                                        v-model="data.selectedId"
                                    />
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    <Link :href="route('quizzes.show', quiz.id)" class="text-indigo-600 hover:text-indigo-900">
                                        {{ quiz.title }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ quiz.description }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ quiz.questions_count }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ quiz.time_limit }} minutes
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ quiz.passing_score }}%
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ quiz.created_at }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ quiz.updated_at }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    <div class="flex justify-center items-center">
                                        <div class="rounded-md overflow-hidden">
                                            <InfoButton
                                                type="button"
                                                @click="openDeleteModal(quiz)"
                                                class="px-2 py-1.5 rounded-none"
                                                v-tooltip="'Delete quiz'"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                            </InfoButton>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-between items-center p-2 border-t border-slate-200 dark:border-slate-700">

                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <Delete
        :show="data.deleteOpen"
        :quiz="data.quizToDelete"
        @close="data.deleteOpen = false"
    />
</template>
