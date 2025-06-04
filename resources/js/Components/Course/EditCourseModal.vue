<script setup>
import { reactive, watch } from 'vue';
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
    course: { type: Object, required: true }
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

const submit = () => {
    form.put(route('courses.update', props.course.id), {
        onSuccess: () => {
            emit('success', 'Course updated successfully!');
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
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
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                Edit Course: {{ course.title }}
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Title -->
                    <div class="md:col-span-2">
                        <InputLabel for="title" value="Course Title" />
                        <TextInput
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.title" class="mt-2" />
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <InputLabel for="description" value="Description" />
                        <TextArea
                            id="description"
                            v-model="form.description"
                            class="mt-1 block w-full"
                            rows="4"
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <!-- Price -->
                    <div>
                        <InputLabel for="price" value="Price ($)" />
                        <TextInput
                            id="price"
                            v-model="form.price"
                            type="number"
                            class="mt-1 block w-full"
                            min="0"
                            step="0.01"
                        />
                        <InputError :message="form.errors.price" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <InputLabel for="status" value="Status" />
                        <SelectInput
                            id="status"
                            v-model="form.status"
                            class="mt-1 block w-full"
                            :options="statusOptions"
                        />
                        <InputError :message="form.errors.status" class="mt-2" />
                    </div>

                    <!-- Level -->
                    <div>
                        <InputLabel for="level" value="Difficulty Level" />
                        <SelectInput
                            id="level"
                            v-model="form.level"
                            class="mt-1 block w-full"
                            :options="levelOptions"
                        />
                        <InputError :message="form.errors.level" class="mt-2" />
                    </div>

                    <!-- Language -->
                    <div>
                        <InputLabel for="language" value="Language" />
                        <SelectInput
                            id="language"
                            v-model="form.language"
                            class="mt-1 block w-full"
                            :options="languageOptions"
                        />
                        <InputError :message="form.errors.language" class="mt-2" />
                    </div>

                    <!-- Duration -->
                    <div>
                        <InputLabel for="duration" value="Duration (hours)" />
                        <TextInput
                            id="duration"
                            v-model="form.duration"
                            type="number"
                            class="mt-1 block w-full"
                            min="0"
                            step="0.5"
                        />
                        <InputError :message="form.errors.duration" class="mt-2" />
                    </div>

                    <!-- Category -->
                    <div>
                        <InputLabel for="category_id" value="Category ID" />
                        <TextInput
                            id="category_id"
                            v-model="form.category_id"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.category_id" class="mt-2" />
                    </div>

                    <!-- Thumbnail -->
                    <div class="md:col-span-2">
                        <InputLabel for="thumbnail" value="Course Thumbnail" />
                        <input
                            id="thumbnail"
                            type="file"
                            @change="handleFileChange"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        />
                        <p class="mt-1 text-sm text-gray-500">Upload a new thumbnail image (optional)</p>
                        <InputError :message="form.errors.thumbnail" class="mt-2" />
                    </div>
                </div>

                <!-- Current Thumbnail Preview -->
                <div v-if="course.thumbnail" class="mt-4">
                    <InputLabel value="Current Thumbnail" />
                    <div class="mt-2">
                        <img
                            :src="course.thumbnail"
                            :alt="course.title"
                            class="w-32 h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-600"
                        />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <SecondaryButton @click="closeModal" type="button">
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Updating...' : 'Update Course' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
