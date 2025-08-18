<template>
    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <label :for="setting.key" :class="COMMON_CLASSES.label">
            {{ formatLabel(setting.key) }}
        </label>
        
        <!-- Toggle Switch -->
        <button
            type="button"
            :id="setting.key"
            @click="toggle"
            :class="[toggleBaseClasses, toggleClasses]"
            role="switch"
            :aria-checked="modelValue"
        >
            <span :class="[switchBaseClasses, switchClasses]"></span>
        </button>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { COMMON_CLASSES, formatLabel } from '@/utils/settingsUtils'

const props = defineProps({
    setting: Object,
    modelValue: Boolean
})

const emit = defineEmits(['update:modelValue'])

const toggle = () => emit('update:modelValue', !props.modelValue)

// Base classes
const toggleBaseClasses = 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
const switchBaseClasses = 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'

// Dynamic classes
const toggleClasses = computed(() => 
    props.modelValue ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600'
)

const switchClasses = computed(() => 
    props.modelValue ? 'translate-x-5' : 'translate-x-0'
)
</script>