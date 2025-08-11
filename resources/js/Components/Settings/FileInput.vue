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
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Current {{ setting.key.replace(/_/g, ' ') }}
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
            :class="dropzoneClasses"
            class="relative border-2 border-dashed rounded-lg p-6 text-center hover:border-indigo-400 transition-colors duration-200 cursor-pointer"
            @click="$refs.fileInput.click()"
        >
            <input
                ref="fileInput"
                :id="setting.key"
                @change="handleFileChange"
                type="file"
                :accept="getAcceptedTypes()"
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
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ getFileTypeDescription() }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { CloudArrowUpIcon, DocumentIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    setting: Object,
    modelValue: [String, File]
})

const emit = defineEmits(['update:modelValue', 'file-upload'])

const isDragging = ref(false)

const dropzoneClasses = computed(() => ({
    'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20': isDragging.value,
    'border-gray-300 dark:border-gray-600': !isDragging.value
}))

const handleFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        processFile(file)
    }
}

const handleDrop = (event) => {
    event.preventDefault()
    isDragging.value = false
    
    const files = event.dataTransfer.files
    if (files.length > 0) {
        processFile(files[0])
    }
}

const processFile = (file) => {
    // Validate file type
    const acceptedTypes = getAcceptedTypes().split(',')
    const isValidType = acceptedTypes.some(type => {
        if (type.includes('*')) {
            return file.type.startsWith(type.replace('*', ''))
        }
        return file.type === type || file.name.toLowerCase().endsWith(type.replace('.', ''))
    })
    
    if (!isValidType) {
        alert(`Please select a valid file type: ${getFileTypeDescription()}`)
        return
    }
    
    // Validate file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        alert('File size must be less than 10MB')
        return
    }
    
    emit('file-upload', { target: { files: [file] } }, props.setting.key)
}

const removeFile = () => {
    emit('update:modelValue', null)
}

const getAcceptedTypes = () => {
    const types = {
        logo: 'image/*,.svg',
        favicon: 'image/*,.ico',
        banner: 'image/*',
        avatar: 'image/*'
    }
    return types[props.setting.key] || 'image/*,.ico'
}

const getFileTypeDescription = () => {
    const descriptions = {
        logo: 'PNG, JPG, SVG up to 10MB',
        favicon: 'ICO, PNG up to 10MB',
        banner: 'PNG, JPG up to 10MB',
        avatar: 'PNG, JPG up to 10MB'
    }
    return descriptions[props.setting.key] || 'Images up to 10MB'
}

const getFileName = () => {
    if (typeof props.modelValue === 'string') {
        return props.modelValue.split('/').pop() || 'Current file'
    }
    return props.modelValue?.name || 'Current file'
}

const isImage = (url) => {
    return url && /\.(jpg|jpeg|png|gif|svg|webp)$/i.test(url)
}
</script>