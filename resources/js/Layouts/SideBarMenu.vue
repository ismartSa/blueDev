<script setup>
import { usePage } from '@inertiajs/vue3'
import NavLink from '@/Components/NavLink.vue'
import {
    HomeIcon,
    AcademicCapIcon,
    UsersIcon,
    CogIcon,
    CircleStackIcon
} from '@heroicons/vue/24/outline'

const page = usePage()
</script>

<template>
    <div class="space-y-1">
        <!-- Dashboard Link -->
        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
            <template #icon>
                <HomeIcon class="w-5 h-5" />
            </template>
            Dashboard
        </NavLink>

        <!-- Quizzes Link -->
        <NavLink 
            v-if="$page.props.auth.user.permissions?.includes('manage courses')"
            :href="route('courses.index')" 
            :active="route().current('courses.*')"
        >
            <template #icon>
                <AcademicCapIcon class="w-5 h-5" />
            </template>
            Courses
        </NavLink>

        <!-- Users Link -->
        <NavLink 
            v-if="$page.props.auth.user.permissions?.includes('view users')"
            :href="route('users.index')" 
            :active="route().current('users.*')"
        >
            <template #icon>
                <UsersIcon class="w-5 h-5" />
            </template>
            Users
        </NavLink>

        <!-- Settings Link -->
        <NavLink 
            v-if="$page.props.auth.user.permissions?.includes('manage database')"
            :href="route('admin.settings')" 
            :active="route().current('admin.settings')"
        >
            <template #icon>
                <CogIcon class="w-5 h-5" />
            </template>
            Settings
        </NavLink>
    </div>
</template>
