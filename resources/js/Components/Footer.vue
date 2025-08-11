<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const currentYear = new Date().getFullYear()
const appName = computed(() => page.props.app.name)
</script>

<template>
    <footer class="bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="text-center text-sm text-slate-600 dark:text-slate-400">
                © {{ currentYear }} {{ appName }}. All rights reserved.
            </div>
        </div>
    </footer>
</template>
