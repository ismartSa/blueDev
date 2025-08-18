<script setup>
import { reactive, watch, computed, ref } from 'vue';
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
import QuickSectionModal from '@/Components/Course/QuickSectionModal.vue';
const props = defineProps({
    show: { type: Boolean, default: false },
    lesson: { type: Object, default: null },
    course: { type: Object, required: true }
});
// show course details consol log


const emit = defineEmits(['close', 'success']);

const form = useForm({
    name: '',
    title: '',
    description: '',
    type: 'video',
    video_url: '',
    duration: 0,
    order: '1',
    is_free: false,
    section_id: null,
});

const lessonTypes = [
    { value: 'video', label: 'Video' },
    { value: 'text', label: 'Text' },
    { value: 'quiz', label: 'Quiz' }
];

const sectionOptions = computed(() => {
    const options = props.course?.sections?.map(section => ({
        value: section.id,
        label: section.title
    })) || [];

    options.push({ value: 'new', label: '+ Create New Section' });
    return options;
});

// Watch for lesson prop changes to populate form
watch(() => props.lesson, (newLesson) => {
    if (newLesson) {
        form.name = newLesson.name || '';
        form.title = newLesson.title || '';
        form.description = newLesson.description || '';
        form.type = newLesson.type || 'video';
        form.video_url = newLesson.video_url || '';
        form.duration = newLesson.duration || 0;
        form.order = newLesson.order || 1;
        form.is_free = newLesson.is_free || false;
        form.section_id = newLesson.section_id || null; // Add this
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
        ? route('courses.lecture.update', { course: props.course.id, lesson: props.lesson.id })
        : route('courses.lecture.store', { course: props.course.id });

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

// Auto-select first section if only one exists
watch(() => props.course?.sections, (sections) => {
    if (sections?.length === 1 && !form.section_id) {
        form.section_id = sections[0].id;
    }
}, { immediate: true });

const showQuickSection = ref(false);

const handleSectionCreated = () => {
    // Refresh sections or handle success
    showQuickSection.value = false;
};
// Add this watch to handle section selection
watch(() => form.section_id, (newValue) => {
    if (newValue === 'new') {
        showQuickSection.value = true;
        form.section_id = null; // Reset selection
    }
});
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                {{ lesson ? 'Edit Lesson' : 'Create New Lesson' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Lesson Name" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>

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

                <!-- Add this after the "Type" field -->
                <div>
                    <InputLabel for="section_id" value="Section" />
                    <SelectInput
                        id="section_id"
                        v-model="form.section_id"
                        class="mt-1 block w-full"
                        :dataSet="sectionOptions"
                        placeholder="Select a section..."
                        required
                    />
                    <InputError :message="form.errors.section_id" class="mt-2" />
                </div>
                                <QuickSectionModal
                    :show="showQuickSection"
                    :course-id="course.id"
                    @close="showQuickSection = false"
                    @success="handleSectionCreated"
                />
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

    <div v-if="!sectionOptions.length" class="text-yellow-600">
        ⚠️ No sections available. Create a section first.
    </div>
</template>
