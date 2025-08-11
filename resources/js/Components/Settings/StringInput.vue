<template>
    <input
        :id="setting.key"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        type="text"
        :placeholder="getPlaceholder(setting.key)"
        :class="COMMON_CLASSES.input"
    />
</template>

<script setup>
import { COMMON_CLASSES, getPlaceholder } from '@/utils/settingsUtils'

defineProps({
    setting: Object,
    modelValue: [String, Number]
})

defineEmits(['update:modelValue'])
</script>