<script setup>
import { reactive, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    lesson: { type: Object, default: null },
    course: { type: Object, required: true }
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    title: '',
    description: '',
    video_url: '',
    duration: '',
    type: 'video',
    is_free: false,
    order: 1
});

const lessonTypes = [
    { value: 'video', label: 'Video' },
    { value: 'text', label: 'Text' },
    { value: 'quiz', label: 'Quiz' }
];

// Watch for lesson prop changes to populate form
watch(() => props.lesson, (newLesson) => {
    if (newLesson) {
        form.title = newLesson.title || '';
        form.description = newLesson.description || '';
        form.video_url = newLesson.video_url || '';
        form.duration = newLesson.duration || '';
        form.type = newLesson.type || 'video';
        form.is_free = newLesson.is_free || false;
        form.order = newLesson.order || 1;
    } else {
        form.reset();
    }
}, { immediate: true });

// Watch for show prop to reset form when modal closes
watch(() => props.show, (show) => {
    if (!show) {
        form.reset();
        form.clearErrors();
    }
});

const submit = () => {
    const url = props.lesson
        ? route('courses.lessons.update', { course: props.course.id, lesson: props.lesson.id })
        : route('courses.lessons.store', { course: props.course.id });

    const method = props.lesson ? 'put' : 'post';

    form[method](url, {
        onSuccess: (response) => {
            emit('success', props.lesson ? 'Lesson updated successfully!' : 'Lesson created successfully!');
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        }
    });
};

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                {{ lesson ? 'Edit Lesson' : 'Create New Lesson' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Title -->
                <div>
                    <InputLabel for="title" value="Lesson Title" />
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
                <div>
                    <InputLabel for="description" value="Description" />
                    <TextArea
                        id="description"
                        v-model="form.description"
                        class="mt-1 block w-full"
                        rows="3"
                    />
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <!-- Type -->
                <div>
                    <InputLabel for="type" value="Lesson Type" />
                    <SelectInput
                        id="type"
                        v-model="form.type"
                        class="mt-1 block w-full"
                        :options="lessonTypes"
                    />
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>

                <!-- Video URL (only for video type) -->
                <div v-if="form.type === 'video'">
                    <InputLabel for="video_url" value="Video URL" />
                    <TextInput
                        id="video_url"
                        v-model="form.video_url"
                        type="url"
                        class="mt-1 block w-full"
                        placeholder="https://example.com/video.mp4"
                    />
                    <InputError :message="form.errors.video_url" class="mt-2" />
                </div>

                <!-- Duration -->
                <div>
                    <InputLabel for="duration" value="Duration (in seconds)" />
                    <TextInput
                        id="duration"
                        v-model="form.duration"
                        type="number"
                        class="mt-1 block w-full"
                        min="0"
                    />
                    <InputError :message="form.errors.duration" class="mt-2" />
                </div>

                <!-- Order -->
                <div>
                    <InputLabel for="order" value="Lesson Order" />
                    <TextInput
                        id="order"
                        v-model="form.order"
                        type="number"
                        class="mt-1 block w-full"
                        min="1"
                    />
                    <InputError :message="form.errors.order" class="mt-2" />
                </div>

                <!-- Is Free -->
                <div class="flex items-center">
                    <Checkbox
                        id="is_free"
                        v-model:checked="form.is_free"
                    />
                    <InputLabel for="is_free" value="Free Lesson" class="ml-2" />
                    <InputError :message="form.errors.is_free" class="mt-2" />
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <SecondaryButton @click="closeModal" type="button">
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : (lesson ? 'Update Lesson' : 'Create Lesson') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
