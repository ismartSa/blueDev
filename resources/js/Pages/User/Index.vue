<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { reactive, watch, computed } from "vue";
import pkg from "lodash";
import {
    CheckBadgeIcon,
    ChevronUpDownIcon,
    PencilIcon,
    TrashIcon,
} from "@heroicons/vue/24/solid";
import {
    Breadcrumb,
    TextInput,
    PrimaryButton,
    InfoButton,
    SelectInput,
    DangerButton,
    Pagination,
    Checkbox
} from "@/Components";
import {
    Create,
    Edit,
    Delete,
    DeleteBulk
} from "@/Pages/User";

// تبسيط استخراج الدوال من lodash
const { debounce, pickBy, cloneDeep } = pkg;

// تعريف Props
const props = defineProps({
    title: String,
    filters: Object,
    users: Object,
    roles: Object,
    breadcrumbs: Object,
    perPage: Number,
});

// تعريف حالة البيانات
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

// إضافة حالة التحميل
const loading = ref(false);

// إضافة معالجة الأخطاء
const handleError = (error) => {
    console.error(error);
};

// دالة الترتيب المحسنة
const order = async (field) => {
    try {
        loading.value = true;
        data.params.field = field;
        data.params.order = data.params.order === "asc" ? "desc" : "asc";
        await router.get(route("user.index"), pickBy(data.params), {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    } catch (error) {
        handleError(error);
    } finally {
        loading.value = false;
    }
};

// دالة التحديد المتعدد المحسنة
const handleSelectAll = (event) => {
    try {
        if (!props.users?.data) return;
        data.selectedId = event.target.checked ? props.users.data.map(user => user.id) : [];
        data.multipleSelect = event.target.checked;
    } catch (error) {
        handleError(error);
    }
};

const select = () => {
    data.multipleSelect = props.users?.data.length === data.selectedId.length;
};

// الترجمات
const translations = computed(() => ({
    label: {
        name: 'الاسم',
        email: 'البريد الإلكتروني',
        role: 'الدور',
        created: 'تاريخ الإنشاء',
        updated: 'تاريخ التحديث',
        data: 'البيانات',
        access: 'الصلاحيات',
        courses: 'الدورات',
        action: 'الإجراءات'
    },
    button: {
        add: 'إضافة',
        edit: 'تعديل',
        delete: 'حذف',
        save: 'حفظ',
        cancel: 'إلغاء'
    },
    tooltip: {
        edit: 'تعديل',
        delete: 'حذف',
        delete_selected: 'حذف المحدد'
    },
    placeholder: {
        search: 'بحث...'
    },
    message: {
        not_selected: 'غير محدد'
    }
}));

const lang = () => translations.value;

// التحقق من الصلاحيات
const can = (permissions) => {
    const userPermissions = usePage().props.auth?.user?.permissions || [];
    return permissions.some(permission => userPermissions.includes(permission));
};

// مراقبة تغييرات البحث والترتيب
watch(
    () => cloneDeep(data.params),
    debounce(() => {
        router.get(route("user.index"), pickBy(data.params), {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150)
);

// تحسين دالة التحديد المتعدد
const selectAll = (event) => {
    try {
        if (!props.users?.data) return;
        data.selectedId = event.target.checked ? props.users.data.map(user => user.id) : [];
        data.multipleSelect = event.target.checked;
    } catch (error) {
        handleError(error);
    }
};

// إضافة التحقق من البيانات
const validateData = (userData) => {
    const errors = [];
    if (!userData.name) errors.push('الاسم مطلوب');
    if (!userData.email) errors.push('البريد الإلكتروني مطلوب');
    return errors;
};
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
                    <table class="w-full">
                        <thead
                            class="uppercase text-sm border-t border-slate-200 dark:border-slate-700"
                        >
                            <tr class="dark:bg-slate-900/50 text-left">
                                <th class="px-2 py-4 text-center">
                                    <Checkbox
                                        v-model:checked="data.multipleSelect"
                                        @change="selectAll"
                                    />
                                </th>
                                <th class="px-2 py-4 text-center">#</th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                    v-on:click="order('name')"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.name }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                    v-on:click="order('email')"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.email }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4">
                                    {{ lang().label.role }}
                                </th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                    v-on:click="order('created_at')"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.created }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                    v-on:click="order('updated_at')"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.updated }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 sr-only">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(user, index) in users.data"
                                :key="index"
                                class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                            >
                                <td
                                    class="whitespace-nowrap py-4 px-2 sm:py-3 text-center"
                                >
                                    <input
                                        class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                        type="checkbox"
                                        @change="select"
                                        :value="user.id"
                                        v-model="data.selectedId"
                                    />
                                </td>
                                <td
                                    class="whitespace-nowrap py-4 px-2 sm:py-3 text-center"
                                >
                                    {{ ++index }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    <span
                                        class="flex justify-start items-center"
                                    >
                                        {{ user.name }}
                                        <CheckBadgeIcon
                                            class="ml-[2px] w-4 h-4 text-primary dark:text-white"
                                            v-show="user.email_verified_at"
                                        />
                                    </span>
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ user.email }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{
                                        user.roles.length == 0
                                            ? "not selected"
                                            : user.roles[0].name
                                    }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ user.created_at }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ user.updated_at }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    <div
                                        class="flex justify-center items-center"
                                    >
                                        <div class="rounded-md overflow-hidden">
                                            <InfoButton
                                                v-show="can(['update user'])"
                                                type="button"
                                                @click="
                                                    (data.editOpen = true),
                                                        (data.user = user)
                                                "
                                                class="px-2 py-1.5 rounded-none"
                                                v-tooltip="lang().tooltip.edit"
                                            >
                                                <PencilIcon class="w-4 h-4" />
                                            </InfoButton>
                                            <DangerButton
                                                v-show="can(['delete user'])"
                                                type="button"
                                                @click="
                                                    (data.deleteOpen = true),
                                                        (data.user = user)
                                                "
                                                class="px-2 py-1.5 rounded-none"
                                                v-tooltip="
                                                    lang().tooltip.delete
                                                "
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                            </DangerButton>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="flex justify-between items-center p-2 border-t border-slate-200 dark:border-slate-700"
                >
                    <Pagination :links="props.users" :filters="data.params" />
                </div>
            </div>
        </div>

        <!-- Loading indicator -->
        <div v-if="loading" class="flex justify-center items-center p-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <!-- No data message -->
        <div v-if="!props.users?.data?.length" class="text-center p-4">
            {{ lang().message.no_data || 'لا توجد بيانات' }}
        </div>
    </AuthenticatedLayout>
</template>
