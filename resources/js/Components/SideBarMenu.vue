<script setup>
import {
    HomeIcon,
    UserIcon,
    CheckBadgeIcon,
    KeyIcon,
    ShieldCheckIcon,
    ClipboardIcon,
    PlusCircleIcon,
    ChartBarIcon,
} from "@heroicons/vue/24/solid";
import { Link, usePage } from "@inertiajs/vue3";


// Get the route function from page props
const page = usePage();

</script>
<template>
    <div class="text-slate-300 pt-5 pb-20">
        <div class="flex justify-center">
            <div class="rounded-full flex items-center justify-center bg-primary text-slate-300 w-24 h-24 text-4xl uppercase">
                {{ userInitials }}
            </div>
        </div>
        <div class="text-center py-3 px-4 border-b border-slate-700 dark:border-slate-800">
            <span class="flex items-center justify-center">
                <p class="truncate text-md">{{ user?.name }}</p>
                <div>
                    <CheckBadgeIcon
                        class="ml-[2px] w-4 h-4"
                        v-show="user?.email_verified_at"
                    />
                </div>
            </span>
            <span class="block text-sm font-medium truncate">
                {{ user?.roles?.[0]?.name || '' }}
            </span>
        </div>
        <ul class="space-y-2 my-4">
            <li
                class="text-white rounded-lg hover:bg-primary dark:hover:bg-primary"
                v-bind:class="
                    route().current('dashboard')
                        ? 'bg-primary'
                        : 'bg-slate-700/40 dark:bg-slate-800/40'
                "
            >
                <Link
                    :href="route('dashboard')"
                    class="flex items-center py-2 px-4"
                >
                    <HomeIcon class="w-6 h-5" />
                    <span class="ml-3">Dashboard</span>
                </Link>
            </li>
            <li v-show="can(['read user'])" class="py-2">
                <p>{{ lang().label.data }}</p>
            </li>
            <li
                v-show="can(['read user'])"
                class="text-white rounded-lg hover:bg-primary dark:hover:bg-primary"
                v-bind:class="
                    route().current('user.index')
                        ? 'bg-primary'
                        : 'bg-slate-700/40 dark:bg-slate-800/40'
                "
            >
                <Link
                    :href="route('user.index')"
                    class="flex items-center py-2 px-4"
                >
                    <UserIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.user }}</span>
                </Link>
            </li>
            <!-- courses -->
            <li v-show="can(['read user'])" class="py-2">
                <p>{{ lang().label.courses }}</p>
            </li>
            <li
                v-show="can(['read user'])"
                class="text-white rounded-lg hover:bg-primary dark:hover:bg-primary"
                v-bind:class="
                    route().current('courses.index')
                        ? 'bg-primary'
                        : 'bg-slate-700/40 dark:bg-slate-800/40'
                "
            >
                <Link
                    :href="route('courses.index')"
                    class="flex items-center py-2 px-4"
                >
                    <UserIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.courses }}</span>
                </Link>
            </li>
            <li
                v-show="can(['create courses'])"
                class="text-white rounded-lg hover:bg-primary dark:hover:bg-primary"
                v-bind:class="
                    route().current('courses.create')
                        ? 'bg-primary'
                        : 'bg-slate-700/40 dark:bg-slate-800/40'
                "
            >
                <Link
                    :href="route('courses.create')"
                    class="flex items-center py-2 px-4"
                >
                    <PlusCircleIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.create_course }}</span>
                </Link>
            </li>
            <!--  end courses -->

            <li v-show="can(['read role', 'read permission'])" class="py-2">
                <p>{{ lang().label.access }}</p>
            </li>
            <li
                v-show="can(['read role'])"
                class="text-white rounded-lg hover:bg-primary dark:hover:bg-primary"
                v-bind:class="
                    route().current('role.index')
                        ? 'bg-primary'
                        : 'bg-slate-700/40 dark:bg-slate-800/40'
                "
            >
                <Link
                    :href="route('role.index')"
                    class="flex items-center py-2 px-4"
                >
                    <KeyIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.role }}</span>
                </Link>
            </li>
            <li
                v-show="can(['read permission'])"
                class="text-white rounded-lg hover:bg-primary dark:hover:bg-primary"
                v-bind:class="
                    route().current('permission.index')
                        ? 'bg-primary'
                        : 'bg-slate-700/40 dark:bg-slate-800/40'
                "
            >
                <Link
                    :href="route('permission.index')"
                    class="flex items-center py-2 px-4"
                >
                    <ShieldCheckIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.permission }}</span>
                </Link>
            </li>

            <!-- إضافة قسم الاختبارات -->
            <li v-show="can(['read quiz'])" class="py-2">
                <p>{{ lang().label.quizzes }}</p>
            </li>
            <li
                v-for="item in quizItems"
                :key="item.route"
                v-show="can([item.permission])"
                class="text-white rounded-lg hover:bg-primary dark:hover:bg-primary"
                :class="route().current(item.route) ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link
                    :href="route(item.route)"
                    class="flex items-center py-2 px-4"
                >
                    <component :is="item.icon" class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label[item.label] }}</span>
                </Link>
            </li>

            <!-- العناصر الأخرى -->
        </ul>
    </div>
</template>
