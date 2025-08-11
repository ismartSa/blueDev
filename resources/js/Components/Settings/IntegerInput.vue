<template>
    <div :class="COMMON_CLASSES.container">
        <input
            :id="setting.key"
            :value="modelValue"
            @input="handleInput"
            type="number"
            :min="getIntegerConfig(setting.key, 'mins')"
            :max="getIntegerConfig(setting.key, 'maxs')"
            :step="getIntegerConfig(setting.key, 'steps')"
            :placeholder="getPlaceholder(setting.key)"
            :class="`${COMMON_CLASSES.input} pr-12`"
        />
        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ getIntegerConfig(setting.key, 'units') }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { COMMON_CLASSES, getPlaceholder, getIntegerConfig } from '@/utils/settingsUtils'

const props = defineProps({
    setting: Object,
    modelValue: [Number, String]
})

const emit = defineEmits(['update:modelValue'])

const handleInput = (event) => {
    const value = parseInt(event.target.value) || 0
    emit('update:modelValue', value)
}
</script>