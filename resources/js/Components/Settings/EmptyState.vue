<template>
    <div class="text-center py-12">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
            <DatabaseIcon class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
            {{ isAdmin ? 'No database connections available' : 'Database Status' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            <template v-if="isAdmin">
                Database connections are loaded from your Laravel configuration.
                <br>Add connections using the "Add Connection" button above.
            </template>
            <template v-else>
                Please <a href="/login" class="text-blue-600 dark:text-blue-400 hover:underline">log in</a> as an administrator to view and manage database connection status.
                <br><br>
                <span class="text-xs text-gray-400">Database monitoring requires admin privileges for security.</span>
            </template>
        </p>
    </div>
</template>

<script setup>
import { CircleStackIcon as DatabaseIcon } from '@heroicons/vue/24/outline'

defineProps({
    isAdmin: {
        type: Boolean,
        default: false
    }
})
</script>