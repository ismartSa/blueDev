<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InfoButton from "@/Components/InfoButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import { reactive, watch } from "vue";
import DangerButton from "@/Components/DangerButton.vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import {
    CheckBadgeIcon,
    ChevronUpDownIcon,
    LanguageIcon,
    PencilIcon,
    TrashIcon,
    PowerIcon,
    KeyIcon,
    UserIcon,
} from "@heroicons/vue/24/solid";
import Create from "@/Pages/User/Create.vue";
import Edit from "@/Pages/User/Edit.vue";
import Delete from "@/Pages/User/Delete.vue";
import DeleteBulk from "@/Pages/User/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
// WarningButton component doesn't exist, using DangerButton instead
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const { _, debounce, pickBy } = pkg;
const page = usePage();

// Permission check function
const can = (permissions) => {
    const userPermissions = page.props.auth?.can || {};
    const permissionArray = Array.isArray(permissions) ? permissions : [permissions];
    return permissionArray.some(permission => userPermissions[permission]);
};

// Translation function
const translations = computed(() => ({
    label: {
        name: 'Name',
        email: 'Email',
        created: 'Created',
        updated: 'Updated',
        role: 'Role'
    },
    tooltip: {
        edit: 'Edit',
        delete: 'Delete',
        delete_selected: 'Delete Selected',
        login_as_user: 'Login as User',
        loginAs: 'Login as User'
    },
    button: {
        add: 'Add User'
    },
    placeholder: {
        search: 'Search users...'
    }
}));

const lang = () => translations.value;
const props = defineProps({
    title: String,
    filters: Object,
    users: Object,
    roles: Object,
    breadcrumbs: Array,
    perPage: Number,
});
const data = reactive({
    params: {
        search: props.filters.search,
        field: props.filters.field,
        order: props.filters.order,
        perPage: props.perPage,
    },
    selectedId: [],
    multipleSelect: false,
    createOpen: false,
    editOpen: false,
    deleteOpen: false,
    deleteBulkOpen: false,
    user: null,
    dataSet: usePage().props.app.perpage,
});

const order = (field) => {
    data.params.field = field;
    data.params.order = data.params.order === "asc" ? "desc" : "asc";
};

watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        let params = pickBy(data.params);
        router.get(route("user.index"), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150)
);

const selectAll = (event) => {
    if (event.target.checked === false) {
        data.selectedId = [];
    } else {
        props.users?.data.forEach((user) => {
            data.selectedId.push(user.id);
        });
    }
};
const select = () => {
    if (props.users?.data.length == data.selectedId.length) {
        data.multipleSelect = true;
    } else {
        data.multipleSelect = false;
    }
};
const loginAsUser = (user) => {
    // Confirm action and warn about local environment restriction
    if (confirm(`Login as ${user.name}?\n\nNote: This feature only works in local environment for security reasons.`)) {
        router.post(route('user.loginAs'), {
            id: user.id
        }, {
            preserveScroll: true,
            onSuccess: () => {
                window.location.reload();
            },
            onError: (errors) => {
                console.error('Login as user failed:', errors);
                alert('Login failed. This feature is only available in local environment.');
            }
        });
    }
};

const toggleUserStatus = (user) => {
    router.patch(route('user.toggle-status', user.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local data to reflect the change immediately
            const userIndex = props.users.data.findIndex(u => u.id === user.id);
            if (userIndex !== -1) {
                props.users.data[userIndex].active = !props.users.data[userIndex].active;
            }
        },
        onError: (errors) => {
            console.error('Toggle user status failed:', errors);
        }
    });
};

const resetPassword = (user) => {
    if (confirm(`Are you sure you want to reset password for ${user.name}?`)) {
        router.post(route('user.reset-password', user.id), {}, {
            onSuccess: () => {
                alert('Password reset successfully. New password sent to user email.');
            },
            onError: (errors) => {
                console.error('Error resetting password:', errors);
            }
        });
    }
};

// Dynamic components for table optimization
const getStatusBadge = (isActive) => ({
    class: `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
        isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
    }`,
    text: isActive ? 'Active' : 'Inactive'
});

// Sortable column configuration
const sortableColumns = [
    { key: 'name', label: 'name' },
    { key: 'email', label: 'email' },
    { key: 'created_at', label: 'created' },
    { key: 'updated_at', label: 'updated' }
];

// Table cell classes for consistency
const cellClasses = 'whitespace-nowrap py-4 px-2 sm:py-3';
const centerCellClasses = `${cellClasses} text-center`;
const actionCellClasses = 'whitespace-nowrap py-4 px-3';

</script>

<template>
    <Head :title="props.title" />

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-4 sm:px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <PrimaryButton
                        v-show="can(['create user'])"
                        class="rounded-none"
                        @click="data.createOpen = true"
                    >
                        {{ lang().button.add }}
                    </PrimaryButton>
                    <Create
                        :show="data.createOpen"
                        @close="data.createOpen = false"
                        :roles="props.roles"
                        :title="props.title"
                    />
                    <Edit
                        :show="data.editOpen"
                        @close="data.editOpen = false"
                        :user="data.user"
                        :roles="props.roles"
                        :title="props.title"
                    />
                    <Delete
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :user="data.user"
                        :title="props.title"
                    />
                    <DeleteBulk
                        :show="data.deleteBulkOpen"
                        @close="
                            (data.deleteBulkOpen = false),
                                (data.multipleSelect = false),
                                (data.selectedId = [])
                        "
                        :selectedId="data.selectedId"
                        :title="props.title"
                    />
                </div>
            </div>
            <div
                class="relative bg-white dark:bg-slate-800 shadow sm:rounded-lg"
            >
                <div class="flex justify-between p-2">
                    <div class="flex space-x-2">
                        <SelectInput
                            v-model="data.params.perPage"
                            :dataSet="data.dataSet"
                        />
                        <DangerButton
                            @click="data.deleteBulkOpen = true"
                            v-show="
                                data.selectedId.length != 0 &&
                                can(['delete user'])
                            "
                            class="px-3 py-1.5"
                            v-tooltip="lang().tooltip.delete_selected"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                    </div>
                    <TextInput
                        v-model="data.params.search"
                        type="text"
                        class="block w-3/6 md:w-2/6 lg:w-1/6 rounded-lg"
                        :placeholder="lang().placeholder.search"
                    />
                </div>
                <div class="overflow-x-auto scrollbar-table">
                    <table class="w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="uppercase text-sm border-t border-slate-200 dark:border-slate-700">
                            <tr class="dark:bg-slate-900/50 text-left">
                                <th class="px-2 py-4 text-center">
                                    <Checkbox v-model:checked="data.multipleSelect" @change="selectAll" />
                                </th>
                                <th class="px-2 py-4 text-center">#</th>
                                <th
                                    v-for="column in sortableColumns"
                                    :key="column.key"
                                    class="px-2 py-4 cursor-pointer"
                                    @click="order(column.key)"
                                >
                                    <div class="flex justify-between items-center">
                                        <span>{{ lang().label[column.label] }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4">{{ lang().label.role }}</th>
                                <th class="px-2 py-4">Status</th>
                                <th class="px-2 py-4 sr-only">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(user, index) in users.data"
                                :key="user.id"
                                class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                            >
                                <td :class="centerCellClasses">
                                    <input
                                        class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                        type="checkbox"
                                        @change="select"
                                        :value="user.id"
                                        v-model="data.selectedId"
                                    />
                                </td>
                                <td :class="centerCellClasses">{{ index + 1 }}</td>
                                <td :class="cellClasses">
                                    <div class="flex justify-start items-center">
                                        {{ user.name }}
                                        <CheckBadgeIcon
                                            v-show="user.email_verified_at"
                                            class="ml-1 w-4 h-4 text-primary dark:text-white"
                                        />
                                    </div>
                                </td>
                                <td :class="cellClasses">{{ user.email }}</td>
                                <td :class="cellClasses">{{ user.created_at }}</td>
                                <td :class="cellClasses">{{ user.updated_at }}</td>
                                <td :class="cellClasses">
                                    {{ user.roles.length === 0 ? 'not selected' : user.roles[0].name }}
                                </td>
                                <td :class="cellClasses">
                                    <span :class="getStatusBadge(user.active).class">
                                        {{ getStatusBadge(user.active).text }}
                                    </span>
                                </td>
                                <td :class="actionCellClasses">
                                    <div class="flex justify-center items-center gap-1">
                                        <InfoButton
                                            v-show="can(['update user'])"
                                            @click="data.editOpen = true; data.user = user"
                                            class="px-3 py-2 rounded-lg"
                                            v-tooltip="lang().tooltip.edit"
                                        >
                                            <PencilIcon class="w-4 h-4" />
                                        </InfoButton>
                                        <DangerButton
                                            v-show="can(['delete user'])"
                                            @click="data.deleteOpen = true; data.user = user"
                                            class="px-3 py-2 rounded-lg"
                                            v-tooltip="lang().tooltip.delete"
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </DangerButton>
                                        <InfoButton
                                            v-show="can(['update user'])"
                                            @click="toggleUserStatus(user)"
                                            :class="`px-3 py-2 rounded-lg ${user.active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'} text-white`"
                                            v-tooltip="user.active ? 'Deactivate User' : 'Activate User'"
                                        >
                                            <PowerIcon class="w-4 h-4" />
                                        </InfoButton>
                                        <DangerButton
                                            v-show="can(['update user'])"
                                            @click="resetPassword(user)"
                                            class="px-3 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600"
                                            v-tooltip="'Reset Password'"
                                        >
                                            <KeyIcon class="w-4 h-4" />
                                        </DangerButton>
                                        <InfoButton
                                            v-show="can(['login as user'])"
                                            @click="loginAsUser(user)"
                                            class="px-3 py-2 rounded-lg bg-black text-white hover:bg-gray-800"
                                            v-tooltip="lang().tooltip.loginAs"
                                        >
                                            <LanguageIcon class="w-4 h-4" />
                                        </InfoButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-between items-center px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700">
                    <Pagination :links="props.users" :filters="data.params" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
