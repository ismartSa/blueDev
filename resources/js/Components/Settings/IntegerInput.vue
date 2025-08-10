<template>
    <div class="relative">
        <input
            :id="setting.key"
            :value="modelValue"
            @input="handleInput"
            type="number"
            :min="getMin()"
            :max="getMax()"
            :step="getStep()"
            :placeholder="getPlaceholder()"
            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors duration-200 text-sm pr-12"
        />
        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ getUnit() }}
            </span>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    setting: Object,
    modelValue: [Number, String]
})

const emit = defineEmits(['update:modelValue'])

const handleInput = (event) => {
    const value = parseInt(event.target.value) || 0
    emit('update:modelValue', value)
}

const getMin = () => {
    const mins = {
        session_lifetime: 1,
        max_file_size: 1,
        pagination_limit: 5,
        cache_ttl: 60
    }
    return mins[props.setting.key] || 0
}

const getMax = () => {
    const maxs = {
        session_lifetime: 43200, // 30 days in minutes
        max_file_size: 100, // 100MB
        pagination_limit: 100,
        cache_ttl: 86400 // 24 hours in seconds
    }
    return maxs[props.setting.key] || 999999
}

const getStep = () => {
    const steps = {
        session_lifetime: 15,
        max_file_size: 1,
        pagination_limit: 5,
        cache_ttl: 60
    }
    return steps[props.setting.key] || 1
}

const getPlaceholder = () => {
    const placeholders = {
        session_lifetime: '120',
        max_file_size: '10',
        pagination_limit: '20',
        cache_ttl: '3600'
    }
    return placeholders[props.setting.key] || '0'
}

const getUnit = () => {
    const units = {
        session_lifetime: 'min',
        max_file_size: 'MB',
        pagination_limit: 'items',
        cache_ttl: 'sec'
    }
    return units[props.setting.key] || ''
}
</script>