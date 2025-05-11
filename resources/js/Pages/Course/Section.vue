
<template>
    <div class="section mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">{{ section.title }}</h3>
            <button @click="toggleCollapse" class="text-gray-500">
                {{ isCollapsed ? 'إظهار' : 'إخفاء' }}
            </button>
        </div>

        <div v-if="!isCollapsed">
            <p class="text-gray-600 mb-4">{{ section.description }}</p>
            <ul class="space-y-2">
                <li v-for="lecture in lectures" :key="lecture.id">
                    <Lecture :lecture="lecture" />
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

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

const isExpanded = ref(false)
</script>

<template>
  <div class="section-container">
    const isCollapsed = ref(false)
    const toggleCollapse = () => {
        isCollapsed.value = !isCollapsed.value
    }
  </div>
</template>
