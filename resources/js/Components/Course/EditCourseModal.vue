<script setup>
import { reactive, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import FileInput from '@/Components/FileInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    course: { type: Object, required: true },
    categories: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    title: '',
    description: '',
    price: '',
    status: 'draft',
    category_id: '',
    thumbnail: null,
    level: 'beginner',
    duration: '',
    language: 'en'
});

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' }
];

const levelOptions = [
    { value: 'beginner', label: 'Beginner' },
    { value: 'intermediate', label: 'Intermediate' },
    { value: 'advanced', label: 'Advanced' }
];

const languageOptions = [
    { value: 'en', label: 'English' },
    { value: 'ar', label: 'Arabic' },
    { value: 'fr', label: 'French' },
    { value: 'es', label: 'Spanish' }
];

// Watch for course prop changes to populate form
watch(() => props.course, (newCourse) => {
    if (newCourse) {
        form.title = newCourse.title || '';
        form.description = newCourse.description || '';
        form.price = newCourse.price || '';
        form.status = newCourse.status || 'draft';
        form.category_id = newCourse.category_id || '';
        form.level = newCourse.level || 'beginner';
        form.duration = newCourse.duration || '';
        form.language = newCourse.language || 'en';
        // Don't set thumbnail as it's a file input
        form.thumbnail = null;
    }
}, { immediate: true });

// Watch for show prop to reset errors when modal closes
watch(() => props.show, (show) => {
    if (!show) {
        form.clearErrors();
    }
});

// Add validation rules
const validationRules = reactive({
    title: { required: true, minLength: 3 },
    description: { required: true, minLength: 10 },
    price: { required: true, min: 0 },
    category_id: { required: true },
    level: { required: true },
    duration: { required: true },
    language: { required: true }
});

// Add validation state
const validationState = reactive({
    title: { valid: true, message: '' },
    description: { valid: true, message: '' },
    price: { valid: true, message: '' },
    category_id: { valid: true, message: '' },
    level: { valid: true, message: '' },
    duration: { valid: true, message: '' },
    language: { valid: true, message: '' }
});

// Add validation methods
const validateField = (field, value) => {
    const rules = validationRules[field];
    const state = validationState[field];

    if (rules.required && (!value || value.trim() === '')) {
        state.valid = false;
        state.message = `${field.charAt(0).toUpperCase() + field.slice(1)} is required`;
        return false;
    }

    if (rules.minLength && value.length < rules.minLength) {
        state.valid = false;
        state.message = `${field.charAt(0).toUpperCase() + field.slice(1)} must be at least ${rules.minLength} characters`;
        return false;
    }

    if (field === 'price' && rules.min !== undefined && Number(value) < rules.min) {
        state.valid = false;
        state.message = `${field.charAt(0).toUpperCase() + field.slice(1)} must be at least ${rules.min}`;
        return false;
    }

    state.valid = true;
    state.message = '';
    return true;
};

// Add computed property for form validity
const isFormValid = computed(() => {
    return Object.keys(validationRules).every(field => {
        return validateField(field, form[field]);
    });
});

// Modify the submit method to include validation
const submit = () => {
    // Validate all fields
    const isValid = Object.keys(validationRules).every(field => {
        return validateField(field, form[field]);
    });

    if (!isValid) {
        return;
    }

    form.put(route('courses.update', props.course.id), {
        onSuccess: () => {
            emit('success', 'Course updated successfully!');
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
            // Map backend errors to validation state
            Object.keys(errors).forEach(field => {
                if (validationState[field]) {
                    validationState[field].valid = false;
                    validationState[field].message = errors[field];
                }
            });
        }
    });
};

const closeModal = () => {
    emit('close');
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.thumbnail = file;
    }
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="3xl">
        <div class="p-6">
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Title Input -->
                <div>
                    <InputLabel for="title" value="Title" />
                    <TextInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full"
                        @blur="validateField('title', form.title)"
                        :class="{ 'border-red-500': !validationState.title.valid }"
                    />
                    <InputError :message="validationState.title.message" />
                </div>

                <!-- Description Input -->
                <div>
                    <InputLabel for="description" value="Description" />
                    <TextArea
                        id="description"
                        v-model="form.description"
                        class="mt-1 block w-full"
                        @blur="validateField('description', form.description)"
                        :class="{ 'border-red-500': !validationState.description.valid }"
                    />
                    <InputError :message="validationState.description.message" />
                </div>

                <!-- Price Input -->
                <div>
                    <InputLabel for="price" value="Price" />
                    <TextInput
                        id="price"
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 block w-full"
                        @blur="validateField('price', form.price)"
                        :class="{ 'border-red-500': !validationState.price.valid }"
                    />
                    <InputError :message="validationState.price.message" />
                </div>

                <!-- Category Input -->
                <div>
                    <InputLabel for="category_id" value="Category" />
                    <SelectInput
                        id="category_id"
                        v-model="form.category_id"
                        class="mt-1 block w-full"
                        @blur="validateField('category_id', form.category_id)"
                        :class="{ 'border-red-500': !validationState.category_id.valid }"
                    >
                        <option value="">Select a category</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </SelectInput>
                    <InputError :message="validationState.category_id.message" />
                </div>

                <!-- Status Input -->
                <div>
                    <InputLabel for="status" value="Status" />
                    <SelectInput
                        id="status"
                        v-model="form.status"
                        class="mt-1 block w-full"
                        @blur="validateField('status', form.status)"
                        :class="{ 'border-red-500': !validationState.status.valid }"
                    >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </SelectInput>
                    <InputError :message="validationState.status.message" />
                </div>

                <!-- Level Input -->
                <div>
                    <InputLabel for="level" value="Level" />
                    <SelectInput
                        id="level"
                        v-model="form.level"
                        class="mt-1 block w-full"
                        @blur="validateField('level', form.level)"
                        :class="{ 'border-red-500': !validationState.level.valid }"
                    >
                        <option v-for="option in levelOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </SelectInput>
                    <InputError :message="validationState.level.message" />
                </div>

                <!-- Duration Input -->
                <div>
                    <InputLabel for="duration" value="Duration (hours)" />
                    <TextInput
                        id="duration"
                        v-model="form.duration"
                        type="number"
                        min="1"
                        class="mt-1 block w-full"
                        @blur="validateField('duration', form.duration)"
                        :class="{ 'border-red-500': !validationState.duration.valid }"
                    />
                    <InputError :message="validationState.duration.message" />
                </div>

                <!-- Language Input -->
                <div>
                    <InputLabel for="language" value="Language" />
                    <SelectInput
                        id="language"
                        v-model="form.language"
                        class="mt-1 block w-full"
                        @blur="validateField('language', form.language)"
                        :class="{ 'border-red-500': !validationState.language.valid }"
                    >
                        <option v-for="option in languageOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </SelectInput>
                    <InputError :message="validationState.language.message" />
                </div>

                <!-- Thumbnail Input -->
                <div>
                    <InputLabel for="thumbnail" value="Thumbnail (optional)" />
                    <FileInput
                        id="thumbnail"
                        @change="handleFileChange"
                        accept="image/*"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.thumbnail" />
                </div>

                <div class="flex justify-end mt-6 gap-x-4">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        type="submit"
                        :disabled="!isFormValid || form.processing"
                    >
                        {{ form.processing ? 'Updating...' : 'Update Course' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
