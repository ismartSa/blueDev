<script setup>
import { ChevronRightIcon } from '@heroicons/vue/24/outline'
import { Link } from '@inertiajs/vue3'

defineProps({
    title: String,
    breadcrumbs: { type: Array, default: () => [] }
})
</script>

<template>
    <div class="flex items-center justify-between py-4 px-4 sm:px-0 text-slate-500 dark:text-slate-300">
        <p>{{ title }}</p>
        <div class="hidden sm:flex space-x-2 items-center" v-if="breadcrumbs.length">
            <Link :href="route('dashboard')">Dashboard</Link>
            <template v-for="(breadcrumb, index) in breadcrumbs" :key="index">
                <ChevronRightIcon class="w-3 h-3" />
                <Link :href="breadcrumb.href">{{ breadcrumb.label }}</Link>
            </template>
        </div>
    </div>
</template>
