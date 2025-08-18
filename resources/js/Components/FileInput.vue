<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: [File, String],
        default: null
    },
    accept: {
        type: String,
        default: 'image/*'
    },
    placeholder: {
        type: String,
        default: 'Choose file...'
    },
    error: {
        type: String,
        default: null
    },
    preview: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const previewUrl = ref(null);
const fileName = ref('');

watch(() => props.modelValue, (newValue) => {
    if (newValue instanceof File) {
        fileName.value = newValue.name;
        if (props.preview && newValue.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewUrl.value = e.target.result;
            };
            reader.readAsDataURL(newValue);
        }
    } else if (typeof newValue === 'string' && newValue) {
        fileName.value = newValue.split('/').pop();
        if (props.preview) {
            previewUrl.value = newValue;
        }
    } else {
        fileName.value = '';
        previewUrl.value = null;
    }
}, { immediate: true });

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        emit('update:modelValue', file);
    }
};

const clearFile = () => {
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    emit('update:modelValue', null);
};

const triggerFileInput = () => {
    fileInput.value?.click();
};
</script>

<template>
    <div class="file-input-container">
        <!-- Hidden file input -->
        <input
            ref="fileInput"
            type="file"
            :accept="accept"
            @change="handleFileChange"
            class="hidden"
        />

        <!-- Custom file input display -->
        <div class="file-input-wrapper">
            <div
                @click="triggerFileInput"
                class="file-input-button"
                :class="{ 'border-red-300': error }"
            >
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 truncate">
                        {{ fileName || placeholder }}
                    </span>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span class="text-sm text-blue-600 hover:text-blue-800">Browse</span>
                    </div>
                </div>
            </div>

            <!-- Clear button -->
            <button
                v-if="fileName"
                @click="clearFile"
                type="button"
                class="clear-button"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Error message -->
        <div v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </div>

        <!-- Image preview -->
        <div v-if="preview && previewUrl" class="mt-3">
            <img
                :src="previewUrl"
                alt="Preview"
                class="w-32 h-32 object-cover rounded-lg border border-gray-200"
            />
        </div>
    </div>
</template>

<style scoped>
.file-input-container {
    @apply w-full;
}

.file-input-wrapper {
    @apply relative flex items-center;
}

.file-input-button {
    @apply flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm cursor-pointer hover:border-gray-400 focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500 transition-colors;
}

.clear-button {
    @apply ml-2 p-1 text-gray-400 hover:text-gray-600 transition-colors;
}
</style>
