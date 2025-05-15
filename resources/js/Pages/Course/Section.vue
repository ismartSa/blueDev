<template>
    <div class="section mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ section.title }}</h3>
            <button @click="toggleCollapse" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                {{ isCollapsed ? 'Show' : 'Hide' }}
            </button>
        </div>

        <div v-if="!isCollapsed" class="mt-4">
            <p class="text-gray-600 dark:text-gray-300 mb-4">{{ section.description }}</p>
            <ul class="space-y-2">
                <li v-for="lecture in lectures" :key="lecture.id" class="bg-white dark:bg-slate-700 rounded-lg shadow-sm p-4">
                    <Lecture :lecture="lecture" />
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import Lecture from './Lecture.vue'

const props = defineProps({
    section: {
        type: Object,
        required: true
    },
    lectures: {
        type: Array,
        default: () => []
    }
})

const isCollapsed = ref(false)
const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value
}
</script>
