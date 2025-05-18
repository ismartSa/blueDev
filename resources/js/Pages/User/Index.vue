<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <!-- Header Section -->
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ title }}
                </h2>
                <!-- Add New User Button -->
                <Link
                    :href="route('users.create')"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md transition duration-150 ease-in-out"
                >
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Add New User
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Search and Filter Section -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Search Input -->
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <SearchIcon class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Search users..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 dark:border-gray-600 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                />
                            </div>
                            <!-- Role Filter -->
                            <select
                                v-model="filters.role"
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md dark:bg-gray-700"
                            >
                                <option value="">All Roles</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    User Info
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Role
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <!-- User Info Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img
                                                :src="user.avatar"
                                                :alt="user.name"
                                                class="h-10 w-10 rounded-full object-cover"
                                            />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ user.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ user.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <!-- Role Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-primary-800">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <!-- Status Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        user.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]">
                                        {{ user.active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <!-- Actions Column -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex space-x-3">
                                        <button
                                            @click="editUser(user)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </button>
                                        <button
                                            @click="confirmDelete(user)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
// Import necessary components and utilities
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {
    PlusIcon,
    MagnifyingGlassIcon as SearchIcon,
    PencilIcon,
    TrashIcon
} from '@heroicons/vue/24/outline'

// Define props
const props = defineProps({
    title: {
        type: String,
        required: true
    },
    users: {
        type: Array,
        required: true
    },
    roles: {
        type: Array,
        required: true
    }
})

// Initialize reactive state
const filters = ref({
    search: '',
    role: ''
})

// Computed property for filtered users
const filteredUsers = computed(() => {
    // التحقق من وجود المستخدمين وأنهم مصفوفة
    const usersList = Array.isArray(props.users) ? props.users : [];

    return usersList.filter(user => {
        const matchesSearch = !filters.value.search ||
            user.name.toLowerCase().includes(filters.value.search.toLowerCase()) ||
            user.email.toLowerCase().includes(filters.value.search.toLowerCase())

        const matchesRole = !filters.value.role || user.role_id === filters.value.role

        return matchesSearch && matchesRole
    })
})

// User actions
const editUser = (user) => {
    router.visit(route('users.edit', user.id))
}

const confirmDelete = (user) => {
    if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        router.delete(route('users.destroy', user.id), {
            onSuccess: () => {
                // Handle success
            },
            onError: () => {
                // Handle error
            }
        })
    }
}
</script>
