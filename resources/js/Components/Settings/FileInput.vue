<template>
    <div class="space-y-3">
        <!-- Current File Preview -->
        <div v-if="modelValue" class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="flex-shrink-0">
                <img 
                    v-if="isImage(modelValue)"
                    :src="modelValue" 
                    :alt="setting.key"
                    class="w-12 h-12 object-cover rounded-lg border border-gray-200 dark:border-gray-600"
                />
                <div v-else class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                    <DocumentIcon class="w-6 h-6 text-gray-400" />
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ getFileName() }}
                </p>
                <p :class="COMMON_CLASSES.description">
                    Current {{ formatLabel(setting.key) }}
                </p>
            </div>
            <button
                type="button"
                @click="removeFile"
                class="flex-shrink-0 p-1 text-red-400 hover:text-red-600 transition-colors duration-200"
            >
                <XMarkIcon class="w-4 h-4" />
            </button>
        </div>

        <!-- File Upload Area -->
        <div
            @drop="handleDrop"
            @dragover.prevent
            @dragenter.prevent
            :class="[dropzoneBaseClasses, dropzoneClasses]"
            @click="$refs.fileInput.click()"
        >
            <input
                ref="fileInput"
                :id="setting.key"
                @change="handleFileChange"
                type="file"
                :accept="getFileConfig(setting.key, 'acceptedTypes')"
                class="sr-only"
            />
            
            <div class="space-y-2">
                <div class="mx-auto w-12 h-12 text-gray-400">
                    <CloudArrowUpIcon class="w-full h-full" />
                </div>
                <div class="text-sm">
                    <span class="font-medium text-indigo-600 dark:text-indigo-400">
                        Click to upload
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">
                        or drag and drop
                    </span>
                </div>
                <p :class="COMMON_CLASSES.description">
                    {{ getFileConfig(setting.key, 'descriptions') }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { CloudArrowUpIcon, DocumentIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { COMMON_CLASSES, formatLabel, getFileConfig, isImage, validateFile } from '@/utils/settingsUtils'

const props = defineProps({
    setting: Object,
    modelValue: [String, File]
})

const emit = defineEmits(['update:modelValue', 'file-upload'])

const isDragging = ref(false)

// Base classes
const dropzoneBaseClasses = 'relative border-2 border-dashed rounded-lg p-6 text-center hover:border-indigo-400 transition-colors duration-200 cursor-pointer'

const dropzoneClasses = computed(() => 
    isDragging.value 
        ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20'
        : 'border-gray-300 dark:border-gray-600'
)

const handleFileChange = (event) => {
    const file = event.target.files?.[0]
    if (file) processFile(file)
}

const handleDrop = (event) => {
    event.preventDefault()
    isDragging.value = false
    
    const files = event.dataTransfer.files
    if (files.length > 0) processFile(files[0])
}

const processFile = (file) => {
    const validation = validateFile(file, props.setting.key)
    
    if (!validation.valid) {
        alert(validation.error)
        return
    }
    
    emit('file-upload', { target: { files: [file] } }, props.setting.key)
}

const removeFile = () => emit('update:modelValue', null)

const getFileName = () => {
    if (typeof props.modelValue === 'string') {
        return props.modelValue.split('/').pop() || 'Current file'
    }
    return props.modelValue?.name || 'Current file'
}
</script>