<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    AcademicCapIcon,
    BookOpenIcon,
    ChevronRightIcon,
    KeyIcon,
    ShieldCheckIcon,
    UserIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/solid";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

// Icon mapping for dynamic components
const iconComponents = {
    UserIcon,
    KeyIcon,
    ShieldCheckIcon,
    BookOpenIcon,
    AcademicCapIcon,
    UserGroupIcon
};

const props = defineProps({
    users: {
        type: Number,
        default: 0
    },
    roles: {
        type: Number,
        default: 0
    },
    permissions: {
        type: Number,
        default: 0
    },
    courses: {
        type: Number,
        default: 0
    },
    quizzes: {
        type: Number,
        default: 0
    },
    enrollments: {
        type: Number,
        default: 0
    }
});

// DRY principle: Define reusable card configuration
const cardConfigs = [
    { key: 'users', label: 'Users', icon: UserIcon, color: 'blue', route: 'user.index' },
    { key: 'roles', label: 'Roles', icon: KeyIcon, color: 'green', route: 'role.index' },
    { key: 'permissions', label: 'Permissions', icon: ShieldCheckIcon, color: 'amber', route: 'permission.index' },
    { key: 'courses', label: 'Courses', icon: BookOpenIcon, color: 'purple', route: 'courses.index' },
    { key: 'quizzes', label: 'Quizzes', icon: AcademicCapIcon, color: 'teal', route: 'quizzes.index' },
    { key: 'enrollments', label: 'Enrollments', icon: UserGroupIcon, color: 'indigo', route: 'dashboard' }
];

// Dynamic color classes generator
const getColorClasses = (color) => ({
    bg: `bg-${color}-600/70 dark:bg-${color}-500/80`,
    bgSolid: `bg-${color}-600 dark:bg-${color}-600/80`,
    hover: `hover:bg-${color}-600/90 dark:hover:bg-${color}-600/70`
});
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <Breadcrumb :title="'Dashboard'" :breadcrumbs="[]" />
        <div class="space-y-6">
            <!-- Dynamic rendering with DRY principle -->
            <div class="text-white dark:text-slate-100 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4 sm:gap-6 lg:gap-8 overflow-hidden shadow-sm">
                <div v-for="config in cardConfigs" :key="config.key" class="stat-card">
                    <!-- Card Header -->
                    <div :class="[
                        'rounded-t-none sm:rounded-t-lg px-6 py-8 flex justify-between items-center overflow-hidden',
                        getColorClasses(config.color).bg
                    ]">
                        <div class="flex flex-col">
                            <p class="text-4xl font-bold">{{ props[config.key] }}</p>
                            <p class="text-md md:text-lg uppercase">{{ config.label }}</p>
                        </div>
                        <div>
                            <config.icon class="w-16 h-auto" />
                        </div>
                    </div>
                    <!-- Card Footer -->
                    <div :class="[
                        'rounded-b-none sm:rounded-b-lg px-6 py-3 overflow-hidden',
                        getColorClasses(config.color).bgSolid,
                        getColorClasses(config.color).hover
                    ]">
                        <Link :href="route(config.route)" class="flex justify-between items-center">
                            <p>More</p>
                            <ChevronRightIcon class="w-5 h-5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Performance optimization: Reduce layout shifts */
.stat-card {
    @apply transition-all duration-200 ease-in-out;
}

/* Better hover effects */
.stat-card:hover {
    @apply transform scale-105;
}

/* Responsive grid optimization */
@media (max-width: 768px) {
    .stat-card {
        @apply transform-none;
    }
}
</style>
