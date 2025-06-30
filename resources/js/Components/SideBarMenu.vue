<script setup>
import {
    HomeIcon, UserIcon, CheckBadgeIcon, KeyIcon,
    ShieldCheckIcon, PlusCircleIcon, TagIcon, AcademicCapIcon,
} from "@heroicons/vue/24/solid";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Improved permission check function
const can = (permissions) => {
    // Use the permissions array from props.auth.can instead of page.props.auth.user.permissions
    const userPermissions = page.props.auth.can || {}; // Ensure it's an object
    const permissionArray = Array.isArray(permissions) ? permissions : [permissions];
    return permissionArray.some(permission => userPermissions[permission]);
};

// Define translations
const translations = computed(() => ({
    label: {
        data: 'Data Management',
        user: 'Users',
        category: 'Categories',
        courses: 'Courses',
        create_course: 'Create Course',
        access: 'Access Control',
        role: 'Roles',
        permission: 'Permissions',
        quizzes: 'Quizzes',
        create_quiz: 'Create Quiz'
    }
}));

const lang = () => translations.value;

// Centralized menu configuration
const menuSections = [
    {
        title: 'dashboard',
        items: [{
            route: 'dashboard',
            icon: HomeIcon,
            label: 'Dashboard',
            isTranslated: false
        }]
    },
    {
        title: 'data',
        permission: 'read user',
        items: [{
            route: 'user.index',
            permission: 'read user',
            icon: UserIcon,
            label: 'user'
        }]
    },
    {
        title: 'Categories',
        permission: 'read category',
        isTranslated: false,
        items: [{
            route: 'category.index',
            permission: 'read category',
            icon: TagIcon,
            label: 'Categories',
            isTranslated: false
        }]
    },
    {
        title: 'courses',
        permission: 'read course',
        items: [
            {
                route: 'courses.index',
                permission: 'read course',
                icon: UserIcon,
                label: 'courses'
            },
            {
                route: 'courses.create',
                permission: 'create course',
                icon: PlusCircleIcon,
                label: 'create_course'
            }
        ]
    },
    {
        title: 'access',
        permission: ['read role', 'read permission'],
        items: [
            {
                route: 'role.index',
                permission: 'read role',
                icon: KeyIcon,
                label: 'role'
            },
            {
                route: 'permission.index',
                permission: 'read permission',
                icon: ShieldCheckIcon,
                label: 'permission'
            }
        ]
    },
    {
        title: 'quizzes',
        permission: 'read quiz',
        items: [
            {
                route: 'quizzes.index',
                permission: 'read quiz',
                icon: AcademicCapIcon,
                label: 'quizzes'
            },
            {
                route: 'quizzes.create',
                permission: 'create quiz',
                icon: PlusCircleIcon,
                label: 'create_quiz'
            }
        ]
    }
];

// Optimized function for user initials
const userInitials = computed(() => {
    const name = user.value?.name || '';
    return name.split(' ')
        .map(n => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

// Reusable classes
const classes = {
    menuItem: 'text-white rounded-lg hover:bg-primary dark:hover:bg-primary',
    active: 'bg-primary',
    inactive: 'bg-slate-700/40 dark:bg-slate-800/40',
    link: 'flex items-center py-2 px-4',
    icon: 'w-6 h-5'
};

// Optimized helper functions
const helpers = {
    canShowSection: permission => !permission || (Array.isArray(permission) ? permission.some(can) : can(permission)),
    canShowItem: permission => !permission || can(permission),
    getMenuItemClasses: routeName => `${classes.menuItem} ${route().current(routeName) ? classes.active : classes.inactive}`,
    getLabelText: (label, isTranslated = true) => isTranslated ? lang().label[label] || label : label
};

</script>

<template>
    <div class="text-slate-300 pt-5 pb-20">
        <!-- User Profile Section -->
        <div class="flex justify-center">
            <div class="rounded-full flex items-center justify-center bg-primary text-slate-300 w-24 h-24 text-4xl uppercase">
                {{ userInitials }}
            </div>
        </div>
        <div class="text-center py-3 px-4 border-b border-slate-700 dark:border-slate-800">
            <span class="flex items-center justify-center">
                <p class="truncate text-md">{{ user?.name }}</p>
                <CheckBadgeIcon
                    class="ml-[2px] w-4 h-4"
                    v-show="user?.email_verified_at"
                />
            </span>
            <span class="block text-sm font-medium truncate">
                {{ user?.roles?.[0]?.name || '' }}
            </span>
        </div>

        <!-- Dynamic Menu Sections -->
        <ul class="space-y-2 my-4">
            <template v-for="section in menuSections" :key="section.title">
                <template v-if="helpers.canShowSection(section.permission)">
                    <!-- Section Title (skip for dashboard) -->
                    <li v-if="section.title !== 'dashboard'" class="py-2">
                        <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">
                            {{ helpers.getLabelText(section.title, section.isTranslated !== false) }}
                        </p>
                    </li>

                    <!-- Section Items -->
                    <li
                        v-for="item in section.items"
                        :key="item.route"
                        v-show="helpers.canShowItem(item.permission)"
                        :class="helpers.getMenuItemClasses(item.route)"
                    >
                        <Link
                            :href="route(item.route)"
                            :class="classes.link"
                        >
                            <component :is="item.icon" :class="classes.icon" />
                            <span class="ml-3">{{ helpers.getLabelText(item.label, item.isTranslated !== false) }}</span>
                        </Link>
                    </li>
                </template>
            </template>
        </ul>
    </div>
</template>
