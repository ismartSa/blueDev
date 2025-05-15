<script setup>
defineProps({
    progress: {
        type: Number,
        required: true,
        default: 0
    }
})
</script>

<template>
    <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-2.5">
        <div
            class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
            :style="{ width: `${progress}%` }"
        ></div>
    </div>
    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
        {{ Math.round(progress) }}% Complete
    </div>
</template>
